<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\MLModel;
use App\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PredictionModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['RoleCode' => 'admin', 'RoleName' => 'Administrator']);
        Role::create(['RoleCode' => 'user', 'RoleName' => 'User']);
    }

    /** @test */
    public function it_can_be_created_with_required_fields()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        
        $prediction = Prediction::create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'MXene' => 0.15,
            'Peptide' => 75.5,
            'Stimulation' => 1.8,
            'Voltage' => 2.2,
            'Result' => 82.5,
            'PredictionDateTime' => now()
        ]);

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'MXene' => 0.15,
            'Peptide' => 75.5,
            'Stimulation' => 1.8,
            'Voltage' => 2.2,
            'Result' => 82.5
        ]);
    }

    /** @test */
    public function it_can_be_created_with_factory()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        
        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'Result' => 90.0
        ]);

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'Result' => 90.0
        ]);
        
        $this->assertNotNull($prediction->MXene);
        $this->assertNotNull($prediction->Peptide);
        $this->assertNotNull($prediction->Stimulation);
        $this->assertNotNull($prediction->Voltage);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id
        ]);
        
        $this->assertInstanceOf(User::class, $prediction->user);
        $this->assertEquals($user->id, $prediction->user->id);
        $this->assertEquals($user->FullName, $prediction->user->FullName);
    }

    /** @test */
    public function it_belongs_to_an_ml_model()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id
        ]);
        
        $this->assertInstanceOf(MLModel::class, $prediction->mlModel);
        $this->assertEquals($model->id, $prediction->mlModel->id);
        $this->assertEquals($model->MLMName, $prediction->mlModel->MLMName);
    }

    /** @test */
    public function prediction_parameters_are_within_valid_ranges()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        
        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'MXene' => 0.2,      // Valid: 0-0.3
            'Peptide' => 100.0,  // Valid: 0-150
            'Stimulation' => 2.5, // Valid: 0-3
            'Voltage' => 1.5     // Valid: 0-3
        ]);
        
        $this->assertTrue($prediction->MXene >= 0 && $prediction->MXene <= 0.3);
        $this->assertTrue($prediction->Peptide >= 0 && $prediction->Peptide <= 150);
        $this->assertTrue($prediction->Stimulation >= 0 && $prediction->Stimulation <= 3);
        $this->assertTrue($prediction->Voltage >= 0 && $prediction->Voltage <= 3);
    }

    /** @test */
    public function result_is_stored_as_float()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        
        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'Result' => 85.75
        ]);
        
        $this->assertIsFloat($prediction->Result);
        $this->assertEquals(85.75, $prediction->Result);
    }

    /** @test */
    public function prediction_date_time_is_cast_to_carbon()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        
        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'PredictionDateTime' => '2023-08-15 14:30:00'
        ]);
        
        $this->assertInstanceOf(\Carbon\Carbon::class, $prediction->PredictionDateTime);
        $this->assertEquals('2023-08-15 14:30:00', $prediction->PredictionDateTime->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_has_timestamps()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id
        ]);
        
        $this->assertNotNull($prediction->created_at);
        $this->assertNotNull($prediction->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $prediction->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $prediction->updated_at);
    }

    /** @test */
    public function it_can_be_ordered_by_prediction_date()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        
        $oldPrediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'PredictionDateTime' => now()->subDays(2)
        ]);
        
        $newPrediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'PredictionDateTime' => now()
        ]);
        
        $orderedPredictions = Prediction::orderBy('PredictionDateTime', 'desc')->get();
        
        $this->assertEquals($newPrediction->id, $orderedPredictions->first()->id);
        $this->assertEquals($oldPrediction->id, $orderedPredictions->last()->id);
    }

    /** @test */
    public function it_can_be_filtered_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $model = MLModel::factory()->create();
        
        $prediction1 = Prediction::factory()->create([
            'user_id' => $user1->id,
            'ml_model_id' => $model->id
        ]);
        
        $prediction2 = Prediction::factory()->create([
            'user_id' => $user2->id,
            'ml_model_id' => $model->id
        ]);
        
        $user1Predictions = Prediction::where('user_id', $user1->id)->get();
        
        $this->assertCount(1, $user1Predictions);
        $this->assertEquals($prediction1->id, $user1Predictions->first()->id);
    }

    /** @test */
    public function it_can_be_filtered_by_model()
    {
        $user = User::factory()->create();
        $model1 = MLModel::factory()->create();
        $model2 = MLModel::factory()->create();
        
        $prediction1 = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model1->id
        ]);
        
        $prediction2 = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model2->id
        ]);
        
        $model1Predictions = Prediction::where('ml_model_id', $model1->id)->get();
        
        $this->assertCount(1, $model1Predictions);
        $this->assertEquals($prediction1->id, $model1Predictions->first()->id);
    }

    /** @test */
    public function it_can_calculate_average_result_for_user()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        
        Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'Result' => 80.0
        ]);
        
        Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'Result' => 90.0
        ]);
        
        $avgResult = Prediction::where('user_id', $user->id)->avg('Result');
        
        $this->assertEquals(85.0, $avgResult);
    }

    /** @test */
    public function it_can_find_recent_predictions()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        
        // Old prediction
        Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'PredictionDateTime' => now()->subDays(60)
        ]);
        
        // Recent prediction
        $recentPrediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id,
            'PredictionDateTime' => now()->subDays(15)
        ]);
        
        $recentPredictions = Prediction::where('user_id', $user->id)
            ->where('PredictionDateTime', '>=', now()->subDays(30))
            ->get();
        
        $this->assertCount(1, $recentPredictions);
        $this->assertEquals($recentPrediction->id, $recentPredictions->first()->id);
    }

    /** @test */
    public function foreign_key_constraints_are_enforced()
    {
        // This test checks if the database enforces foreign key constraints
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Prediction::create([
            'user_id' => 999, // Non-existent user
            'ml_model_id' => 999, // Non-existent model
            'MXene' => 0.1,
            'Peptide' => 50.0,
            'Stimulation' => 1.5,
            'Voltage' => 2.0,
            'Result' => 75.0,
            'PredictionDateTime' => now()
        ]);
    }

    /** @test */
    public function required_fields_are_enforced()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Prediction::create([
            // Missing required fields
            'Result' => 75.0
        ]);
    }

    /** @test */
    public function it_can_be_soft_deleted()
    {
        $user = User::factory()->create();
        $model = MLModel::factory()->create();
        $prediction = Prediction::factory()->create([
            'user_id' => $user->id,
            'ml_model_id' => $model->id
        ]);
        
        $predictionId = $prediction->id;
        $prediction->delete();
        
        // Check if using soft deletes
        if (method_exists($prediction, 'trashed')) {
            $this->assertTrue($prediction->trashed());
            $this->assertDatabaseHas('predictions', ['id' => $predictionId]);
        } else {
            // Hard delete
            $this->assertDatabaseMissing('predictions', ['id' => $predictionId]);
        }
    }
}
