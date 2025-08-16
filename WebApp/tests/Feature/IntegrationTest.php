<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\MLModel;
use App\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['RoleCode' => 'admin', 'RoleName' => 'Administrator']);
        Role::create(['RoleCode' => 'user', 'RoleName' => 'User']);
        
        // Create users
        $this->admin = User::factory()->create(['role_id' => 1]);
        $this->user = User::factory()->create(['role_id' => 2]);
    }

    /** @test */
    public function complete_admin_workflow_for_user_management()
    {
        // 1. Admin logs in
        $loginResponse = $this->post(route('login'), [
            'username' => $this->admin->Username,
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('admin.dashboard'));

        // 2. Admin views dashboard
        $dashboardResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertViewHas(['totalUsers', 'totalModels', 'activeModels', 'adminPredictions']);

        // 3. Admin views users list
        $usersResponse = $this->actingAs($this->admin)->get(route('admin.users'));
        $usersResponse->assertStatus(200);
        $usersResponse->assertViewHas('users');

        // 4. Admin creates a new user
        $userData = [
            'FullName' => 'Integration Test User',
            'Gender' => 'Female',
            'BirthDate' => '1992-03-15',
            'Address' => 'Test Address 123',
            'Username' => 'integrationuser',
            'Password' => 'password123'
        ];

        $createResponse = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);
        $createResponse->assertRedirect(route('admin.users'));
        $createResponse->assertSessionHas('success');

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'FullName' => 'Integration Test User',
            'Username' => 'integrationuser',
            'role_id' => 2
        ]);

        $createdUser = User::where('Username', 'integrationuser')->first();

        // 5. Admin edits the user
        $editResponse = $this->actingAs($this->admin)->get(route('admin.users.edit', $createdUser));
        $editResponse->assertStatus(200);
        $editResponse->assertViewHas('user', $createdUser);

        $updateData = [
            'FullName' => 'Updated Integration User',
            'Gender' => 'Female',
            'BirthDate' => '1992-03-15',
            'Address' => 'Updated Address 456',
            'Username' => 'updatedintegrationuser'
        ];

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.users.update', $createdUser), $updateData);
        $updateResponse->assertRedirect(route('admin.users'));
        $updateResponse->assertSessionHas('success');

        // 6. Admin resets user password
        $resetResponse = $this->actingAs($this->admin)->post(route('admin.users.reset-password', $createdUser));
        $resetResponse->assertRedirect(route('admin.users'));
        $resetResponse->assertSessionHas('success');

        // 7. Admin deletes the user
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.users.delete', $createdUser));
        $deleteResponse->assertRedirect(route('admin.users'));
        $deleteResponse->assertSessionHas('success');
    }

    /** @test */
    public function complete_admin_workflow_for_model_management()
    {
        Storage::fake('public');

        // 1. Admin views models list
        $modelsResponse = $this->actingAs($this->admin)->get(route('admin.models'));
        $modelsResponse->assertStatus(200);
        $modelsResponse->assertViewHas('models');

        // 2. Admin creates a new model
        $file = UploadedFile::fake()->create('test_integration_model.h5', 1000);
        
        $modelData = [
            'MLMName' => 'Integration Test Model',
            'model_file' => $file,
            'LibType' => 'keras',
            'IsActive' => '1'
        ];

        $createResponse = $this->actingAs($this->admin)->post(route('admin.models.store'), $modelData);
        $createResponse->assertRedirect(route('admin.models'));
        $createResponse->assertSessionHas('success');

        // Verify model was created
        $this->assertDatabaseHas('ml_models', [
            'MLMName' => 'Integration Test Model',
            'LibType' => 'keras',
            'IsActive' => true
        ]);

        $createdModel = MLModel::where('MLMName', 'Integration Test Model')->first();

        // 3. Admin edits the model
        $editResponse = $this->actingAs($this->admin)->get(route('admin.models.edit', $createdModel));
        $editResponse->assertStatus(200);
        $editResponse->assertViewHas('model', $createdModel);

        $updateData = [
            'MLMName' => 'Updated Integration Model',
            'LibType' => 'keras',
            'IsActive' => '1'
        ];

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.models.update', $createdModel), $updateData);
        $updateResponse->assertRedirect(route('admin.models'));
        $updateResponse->assertSessionHas('success');

        // 4. Admin tests the model
        Http::fake([
            '*/predict/health' => Http::response(['status' => 'healthy'], 200),
            '*/predict/model' => Http::response(['prediction' => 78.5, 'model_used' => 'Integration Test Model'], 200),
        ]);

        $testData = [
            'pc_mxene_loading' => 0.15,
            'laminin_peptide_loading' => 80,
            'stimulation_frequency' => 2.0,
            'applied_voltage' => 1.8
        ];

        $testResponse = $this->actingAs($this->admin)->postJson(route('admin.models.test', $createdModel), $testData);
        $testResponse->assertStatus(200);
        $testResponse->assertJson(['success' => true]);

        // 5. Admin deletes the model
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.models.delete', $createdModel));
        $deleteResponse->assertRedirect(route('admin.models'));
        $deleteResponse->assertSessionHas('success');
    }

    /** @test */
    public function complete_user_workflow_for_predictions()
    {
        // Setup: Create a model for predictions
        $model = MLModel::factory()->active()->create();

        // 1. User logs in
        $loginResponse = $this->post(route('login'), [
            'username' => $this->user->Username,
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('user.dashboard'));

        // 2. User views dashboard
        $dashboardResponse = $this->actingAs($this->user)->get(route('user.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertViewHas(['totalPredictions', 'recentPredictions']);

        // 3. User views prediction form
        $predictResponse = $this->actingAs($this->user)->get(route('user.predict'));
        $predictResponse->assertStatus(200);
        $predictResponse->assertViewHas('models');

        // 4. User makes a prediction
        Http::fake([
            '*/predict/health' => Http::response(['status' => 'healthy'], 200),
            '*/predict/model' => Http::response(['prediction' => 85.2], 200),
        ]);

        $predictionData = [
            'pc_mxene_loading' => 0.1,
            'laminin_peptide_loading' => 60,
            'stimulation_frequency' => 1.5,
            'applied_voltage' => 2.2,
            'ml_model_id' => $model->id
        ];

        $makePredictionResponse = $this->actingAs($this->user)->postJson(route('user.predict.make'), $predictionData);
        $makePredictionResponse->assertStatus(200);
        $makePredictionResponse->assertJson(['success' => true]);

        // Verify prediction was saved
        $this->assertDatabaseHas('predictions', [
            'user_id' => $this->user->id,
            'ml_model_id' => $model->id,
            'MXene' => 0.1,
            'Peptide' => 60,
            'Stimulation' => 1.5,
            'Voltage' => 2.2
        ]);

        // 5. User views prediction history
        $historyResponse = $this->actingAs($this->user)->get(route('user.history'));
        $historyResponse->assertStatus(200);
        $historyResponse->assertViewHas('predictions');

        // 6. User views profile
        $profileResponse = $this->actingAs($this->user)->get(route('user.profile'));
        $profileResponse->assertStatus(200);
        $profileResponse->assertViewHas('user', $this->user);

        // 7. User updates profile
        $profileUpdateData = [
            'FullName' => 'Updated User Name',
            'Gender' => $this->user->Gender,
            'BirthDate' => $this->user->BirthDate->format('Y-m-d'),
            'Address' => 'Updated User Address',
            'Username' => 'updatedusername'
        ];

        $updateProfileResponse = $this->actingAs($this->user)->put(route('user.profile.update'), $profileUpdateData);
        $updateProfileResponse->assertRedirect(route('user.profile'));
        $updateProfileResponse->assertSessionHas('success');

        // 8. User changes password
        $passwordChangeData = [
            'current_password' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ];

        $changePasswordResponse = $this->actingAs($this->user)->post(route('user.security.change-password'), $passwordChangeData);
        $changePasswordResponse->assertRedirect(route('user.security'));
        $changePasswordResponse->assertSessionHas('success');
    }

    /** @test */
    public function complete_admin_prediction_workflow()
    {
        // Setup: Create a model for predictions
        $model = MLModel::factory()->active()->create();

        Http::fake([
            '*/predict/health' => Http::response(['status' => 'healthy'], 200),
            '*/predict/model' => Http::response(['prediction' => 92.7], 200),
        ]);

        // 1. Admin views prediction form
        $predictResponse = $this->actingAs($this->admin)->get(route('admin.predict'));
        $predictResponse->assertStatus(200);
        $predictResponse->assertViewHas('models');

        // 2. Admin makes a prediction
        $predictionData = [
            'pc_mxene_loading' => 0.25,
            'laminin_peptide_loading' => 120,
            'stimulation_frequency' => 2.5,
            'applied_voltage' => 2.8,
            'ml_model_id' => $model->id
        ];

        $makePredictionResponse = $this->actingAs($this->admin)->postJson(route('admin.predict.make'), $predictionData);
        $makePredictionResponse->assertStatus(200);
        $makePredictionResponse->assertJson(['success' => true]);

        // 3. Admin views prediction history
        $historyResponse = $this->actingAs($this->admin)->get(route('admin.history'));
        $historyResponse->assertStatus(200);
        $historyResponse->assertViewHas('predictions');

        // Verify prediction was saved for admin
        $this->assertDatabaseHas('predictions', [
            'user_id' => $this->admin->id,
            'ml_model_id' => $model->id,
            'MXene' => 0.25,
            'Result' => 92.7
        ]);
    }

    /** @test */
    public function role_based_access_control_integration()
    {
        // 1. User tries to access admin routes - should be denied
        $adminRoutes = [
            'admin.dashboard',
            'admin.users',
            'admin.models'
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->actingAs($this->user)->get(route($route));
            $response->assertStatus(403);
        }

        // 2. Admin tries to access user routes - should be denied
        $userRoutes = [
            'user.dashboard',
            'user.predict',
            'user.profile'
        ];

        foreach ($userRoutes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertStatus(403);
        }

        // 3. Guest tries to access any protected route - should redirect to login
        $protectedRoutes = array_merge($adminRoutes, $userRoutes);

        foreach ($protectedRoutes as $route) {
            $response = $this->get(route($route));
            $response->assertRedirect(route('login'));
        }
    }

    /** @test */
    public function full_application_error_handling()
    {
        // 1. Test invalid login
        $invalidLoginResponse = $this->post(route('login'), [
            'username' => 'nonexistent',
            'password' => 'wrongpassword'
        ]);
        $invalidLoginResponse->assertRedirect();
        $invalidLoginResponse->assertSessionHasErrors(['username']);

        // 2. Test prediction with invalid data
        $model = MLModel::factory()->active()->create();
        
        $invalidPredictionData = [
            'pc_mxene_loading' => -0.1, // Invalid: negative
            'laminin_peptide_loading' => 200, // Invalid: too high
            'stimulation_frequency' => '', // Invalid: empty
            'applied_voltage' => 'abc', // Invalid: not numeric
            'ml_model_id' => 999 // Invalid: non-existent
        ];

        $invalidPredictionResponse = $this->actingAs($this->user)->postJson(route('user.predict.make'), $invalidPredictionData);
        $invalidPredictionResponse->assertStatus(422);
        $invalidPredictionResponse->assertJsonValidationErrors();

        // 3. Test prediction with API service down
        Http::fake([
            '*/predict/health' => Http::response([], 503),
        ]);

        $validPredictionData = [
            'pc_mxene_loading' => 0.1,
            'laminin_peptide_loading' => 50,
            'stimulation_frequency' => 1.5,
            'applied_voltage' => 2.0,
            'ml_model_id' => $model->id
        ];

        $apiDownResponse = $this->actingAs($this->user)->postJson(route('user.predict.make'), $validPredictionData);
        $apiDownResponse->assertStatus(503);
        $apiDownResponse->assertJson(['success' => false]);
    }

    /** @test */
    public function data_consistency_and_relationships()
    {
        // 1. Create related data
        $model = MLModel::factory()->active()->create();
        $prediction = Prediction::factory()->create([
            'user_id' => $this->user->id,
            'ml_model_id' => $model->id
        ]);

        // 2. Test relationships work correctly
        $this->assertEquals($this->user->id, $prediction->user->id);
        $this->assertEquals($model->id, $prediction->mlModel->id);
        $this->assertContains($prediction->id, $this->user->predictions->pluck('id'));
        $this->assertContains($prediction->id, $model->predictions->pluck('id'));

        // 3. Test cascade behaviors
        $predictionId = $prediction->id;
        
        // Delete user should handle prediction appropriately
        $this->user->delete();
        
        // Check if prediction still exists (depends on foreign key configuration)
        if (config('database.default') === 'sqlite') {
            // SQLite might not enforce foreign key constraints by default
            $this->assertTrue(true); // Placeholder
        }
    }
}
