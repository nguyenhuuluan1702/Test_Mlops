<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use App\Models\User;
use App\Models\Role;
use App\Models\MLModel;
use App\Models\Prediction;

class AdminController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->userService = $userService;
    }

    public function dashboard()
    {
        $totalUsers = User::where('role_id', 2)->count();
        $totalModels = MLModel::count();
        $activeModels = MLModel::where('IsActive', true)->count();
        $adminPredictions = Prediction::where('user_id', Auth::id())->count();
        
        return view('admin.dashboard', compact('totalUsers', 'totalModels', 'activeModels', 'adminPredictions'));
    }

    // User Management
    public function users()
    {
        $users = User::with('role')->where('role_id', 2)->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'FullName' => 'required|string|max:255',
            'Gender' => 'required|in:Male,Female',
            'BirthDate' => 'required|date',
            'Address' => 'required|string|max:255',
            'Username' => 'required|string|max:255|unique:users',
            'Password' => 'required|string|min:6',
        ]);

        try {
            $this->userService->createUser([
                'FullName' => $request->FullName,
                'Gender' => $request->Gender,
                'BirthDate' => $request->BirthDate,
                'Address' => $request->Address,
                'Username' => $request->Username,
                'Password' => $request->Password,
            ]);

            return redirect()->route('admin.users')->with('success', 'User created successfully with auto-generated UserCode.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function editUser(User $user)
    {
        if ($user->role_id === 1) {
            return redirect()->route('admin.users')->with('error', 'Cannot edit admin user.');
        }
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'FullName' => 'required|string|max:255',
            'Gender' => 'required|in:Male,Female',
            'BirthDate' => 'required|date',
            'Address' => 'required|string|max:255',
            'Username' => 'required|string|max:255|unique:users,Username,' . $user->id,
        ]);

        try {
            $updated = $this->userService->updateUser($user, [
                'FullName' => $request->FullName,
                'Gender' => $request->Gender,
                'BirthDate' => $request->BirthDate,
                'Address' => $request->Address,
                'Username' => $request->Username,
            ]);

            if (!$updated) {
                return redirect()->route('admin.users')->with('error', 'Cannot edit admin user.');
            }

            return redirect()->route('admin.users')->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function resetPassword(User $user)
    {
        try {
            $success = $this->userService->resetPassword($user);
            
            if (!$success) {
                return redirect()->route('admin.users')->with('error', 'Cannot reset admin password.');
            }

            return redirect()->route('admin.users')->with('success', 'Password has been reset to a new random password.');
        } catch (\Exception $e) {
            return redirect()->route('admin.users')->with('error', 'Failed to reset password: ' . $e->getMessage());
        }
    }

    public function deleteUser(User $user)
    {
        try {
            $success = $this->userService->deleteUser($user);
            
            if (!$success) {
                $predictionCount = $user->predictions()->count();
                if ($predictionCount > 0) {
                    return redirect()->route('admin.users')
                        ->with('error', "Cannot delete user '{$user->FullName}' because they have {$predictionCount} associated prediction(s). Use anonymize or force delete instead.");
                } else {
                    return redirect()->route('admin.users')->with('error', 'Cannot delete admin user.');
                }
            }

            return redirect()->route('admin.users')->with('success', "User '{$user->FullName}' deleted successfully.");
        } catch (\Exception $e) {
            return redirect()->route('admin.users')->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    public function forceDeleteUser(User $user)
    {
        try {
            $predictionCount = $user->predictions()->count();
            $userName = $user->FullName;
            
            $success = $this->userService->deleteUser($user, true);
            
            if (!$success) {
                return redirect()->route('admin.users')->with('error', 'Cannot delete admin user.');
            }
            
            return redirect()->route('admin.users')
                ->with('success', "User '{$userName}' and {$predictionCount} associated prediction(s) deleted successfully.");
        } catch (\Exception $e) {
            return redirect()->route('admin.users')->with('error', 'Failed to force delete user: ' . $e->getMessage());
        }
    }

    public function anonymizeUser(User $user)
    {
        try {
            $originalName = $user->FullName;
            $predictionCount = $user->predictions()->count();
            
            $success = $this->userService->anonymizeUser($user);
            
            if (!$success) {
                return redirect()->route('admin.users')->with('error', 'Cannot anonymize admin user.');
            }
            
            return redirect()->route('admin.users')
                ->with('success', "User '{$originalName}' has been anonymized. {$predictionCount} prediction(s) have been preserved for data integrity.");
        } catch (\Exception $e) {
            return redirect()->route('admin.users')->with('error', 'Failed to anonymize user: ' . $e->getMessage());
        }
    }

    // ML Model Management
    public function models()
    {
        $models = MLModel::paginate(10);
        return view('admin.models.index', compact('models'));
    }

    public function createModel()
    {
        return view('admin.models.create');
    }

    public function storeModel(Request $request)
    {
        // Debug information
        \Log::info('File upload attempt:', [
            'has_file' => $request->hasFile('model_file'),
            'file_info' => $request->hasFile('model_file') ? [
                'name' => $request->file('model_file')->getClientOriginalName(),
                'extension' => $request->file('model_file')->getClientOriginalExtension(),
                'mime' => $request->file('model_file')->getMimeType(),
                'size' => $request->file('model_file')->getSize(),
            ] : null,
            'libtype' => $request->LibType
        ]);

        $request->validate([
            'MLMName' => 'required|string|max:255',
            'model_file' => [
                'required',
                'file',
                'max:102400', // 100MB
                function ($attribute, $value, $fail) {
                    $allowedExtensions = ['h5', 'pkl', 'keras', 'json', 'pt', 'pth', 'joblib', 'xgb'];
                    $extension = strtolower($value->getClientOriginalExtension());
                    
                    \Log::info('File validation:', [
                        'extension' => $extension,
                        'allowed' => $allowedExtensions,
                        'in_array' => in_array($extension, $allowedExtensions)
                    ]);
                    
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('The ' . $attribute . ' must be a file of type: ' . implode(', ', $allowedExtensions) . '. Got: ' . $extension);
                    }
                }
            ],
            'LibType' => 'required|string|in:keras,pytorch,sklearn,xgboost,pickle,joblib',
        ]);

        $file = $request->file('model_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        
        // Store in public/models directory directly (not storage/app/public)
        $destinationPath = public_path('models');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        
        $file->move($destinationPath, $filename);
        
        MLModel::create([
            'MLMName' => $request->MLMName,
            'FilePath' => 'models/' . $filename, // Store relative path from public directory
            'LibType' => $request->LibType,
            'IsActive' => $request->has('IsActive'),
        ]);

        return redirect()->route('admin.models')->with('success', 'Model uploaded successfully.');
    }

    public function editModel(MLModel $model)
    {
        return view('admin.models.edit', compact('model'));
    }

    public function updateModel(Request $request, MLModel $model)
    {
        $request->validate([
            'MLMName' => 'required|string|max:255',
            'model_file' => [
                'nullable',
                'file',
                'max:102400', // 100MB
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $allowedExtensions = ['h5', 'pkl', 'keras', 'json', 'pt', 'pth', 'joblib', 'xgb'];
                        $extension = strtolower($value->getClientOriginalExtension());
                        
                        if (!in_array($extension, $allowedExtensions)) {
                            $fail('The ' . $attribute . ' must be a file of type: ' . implode(', ', $allowedExtensions) . '.');
                        }
                    }
                }
            ],
            'LibType' => 'required|string|in:keras,pytorch,sklearn,xgboost,pickle,joblib',
        ]);

        $data = [
            'MLMName' => $request->MLMName,
            'LibType' => $request->LibType,
            'IsActive' => $request->has('IsActive'),
        ];

        if ($request->hasFile('model_file')) {
            // Delete old file from public/models
            if ($model->FilePath) {
                $oldFilePath = public_path($model->FilePath);
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // Upload new file to public/models
            $file = $request->file('model_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            $destinationPath = public_path('models');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $file->move($destinationPath, $filename);
            $data['FilePath'] = 'models/' . $filename;
        }

        $model->update($data);

        return redirect()->route('admin.models')->with('success', 'Model updated successfully.');
    }

    public function deleteModel(MLModel $model)
    {
        // Check if this is the default model (protect it)
        if ($this->isDefaultModel($model)) {
            return redirect()->route('admin.models')
                ->with('error', "Cannot delete '{$model->MLMName}' because it is the default system model. The system needs at least one default model to function properly.");
        }
        
        // Check if model has associated predictions
        $predictionCount = $model->predictions()->count();
        
        if ($predictionCount > 0) {
            return redirect()->route('admin.models')
                ->with('error', "Cannot delete model '{$model->MLMName}' because it has {$predictionCount} associated prediction(s). Please delete the predictions first or consider deactivating the model instead.");
        }
        
        // Delete file from public/models
        if ($model->FilePath) {
            $filePath = public_path($model->FilePath);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $modelName = $model->MLMName;
        $model->delete();
        
        return redirect()->route('admin.models')->with('success', "Model '{$modelName}' deleted successfully.");
    }

    public function forceDeleteModel(MLModel $model)
    {
        // Check if this is the default model (protect it)
        if ($this->isDefaultModel($model)) {
            return redirect()->route('admin.models')
                ->with('error', "Cannot delete '{$model->MLMName}' because it is the default system model. The system needs at least one default model to function properly.");
        }
        
        // Force delete: delete all associated predictions first
        $predictionCount = $model->predictions()->count();
        
        if ($predictionCount > 0) {
            $model->predictions()->delete();
        }
        
        // Delete file from public/models
        if ($model->FilePath) {
            $filePath = public_path($model->FilePath);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $modelName = $model->MLMName;
        $model->delete();
        
        return redirect()->route('admin.models')
            ->with('success', "Model '{$modelName}' and {$predictionCount} associated prediction(s) deleted successfully.");
    }

    /**
     * Check if a model is the default system model
     */
    private function isDefaultModel(MLModel $model)
    {
        // Consider default if it's the "Default ANN Model" or has specific file name
        return $model->MLMName === 'Default ANN Model' || 
               str_contains($model->FilePath, 'ann_model.keras') ||
               $model->id === 1; // First model is usually default
    }

    public function testModel(Request $request, MLModel $model)
    {
        // Test the model with sample data
        $request->validate([
            'pc_mxene_loading' => 'required|numeric|min:0|max:0.03',
            'laminin_peptide_loading' => 'required|numeric|min:0|max:5.9',
            'stimulation_frequency' => 'required|numeric|min:0|max:3',
            'applied_voltage' => 'required|numeric|min:0|max:3',
        ]);

        try {
            // Check if model file exists
            if (!$model->fileExists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Model file not found on server.'
                ], 404);
            }

            // Test API connection
            $apiUrl = env('PREDICT_SERVICE_URL', 'http://localhost:5000');
            try {
                $healthResponse = Http::timeout(5)->get($apiUrl . '/predict/health');
                if (!$healthResponse->successful()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Prediction service is not available.'
                    ], 503);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot connect to prediction service: ' . $e->getMessage()
                ], 503);
            }

            // Generate test token (similar to user token but for admin)
            $payload = [
                'user_id' => auth()->id(),
                'username' => auth()->user()->Username,
                'iat' => time(),
                'exp' => time() + (60 * 60), // 1 hour expiration
            ];
            $secretKey = env('JWT_SECRET', 'jwt_secret');
            $token = \Firebase\JWT\JWT::encode($payload, $secretKey, 'HS256');

            // Prepare test payload
            $testPayload = [
                'pc_mxene_loading' => (float)$request->pc_mxene_loading,
                'laminin_peptide_loading' => (float)$request->laminin_peptide_loading,
                'stimulation_frequency' => (float)$request->stimulation_frequency,
                'applied_voltage' => (float)$request->applied_voltage,
                'model_path' => $model->absolute_path,
                'model_type' => strtolower($model->LibType),
            ];

            // Call Flask API using the new universal endpoint
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($apiUrl . '/predict/model', $testPayload);

            if ($response->successful()) {
                $responseData = $response->json();
                return response()->json([
                    'success' => true,
                    'prediction' => round($responseData['prediction'], 2),
                    'model_used' => $responseData['model_used'] ?? $model->MLMName,
                    'message' => 'Model test successful!'
                ]);
            } else {
                $errorMessage = 'Model test failed';
                $responseBody = $response->json();
                
                if ($responseBody && isset($responseBody['error'])) {
                    $errorMessage = $responseBody['error'];
                }
                
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                    'debug_info' => env('APP_DEBUG') ? [
                        'api_status' => $response->status(),
                        'api_response' => $response->body()
                    ] : null
                ], $response->status());
            }

        } catch (\Exception $e) {
            \Log::error('Exception in testModel', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error testing model: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Prediction methods for admin
    public function predict()
    {
        // Get available active models for admin selection
        $models = MLModel::where('IsActive', true)->get();
        return view('admin.predict', compact('models'));
    }

    private function testApiConnection()
    {
        $apiUrl = env('PREDICT_SERVICE_URL', 'http://localhost:5000');
        try {
            $response = Http::timeout(5)->get($apiUrl . '/predict/health');
            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('API health check failed: ' . $e->getMessage());
            return false;
        }
    }

    public function makePrediction(Request $request)
    {
        $request->validate([
            'pc_mxene_loading' => 'required|numeric|min:0|max:0.03',
            'laminin_peptide_loading' => 'required|numeric|min:0|max:5.9',
            'stimulation_frequency' => 'required|numeric|min:0|max:3',
            'applied_voltage' => 'required|numeric|min:0|max:3',
            'ml_model_id' => 'required|exists:ml_models,id',
        ]);

        try {
            // Get selected model
            $selectedModel = MLModel::findOrFail($request->ml_model_id);
            
            // Check if model is active
            if (!$selectedModel->IsActive) {
                return response()->json([
                    'success' => false,
                    'error' => 'Selected model is not active.'
                ], 400);
            }

            // Check if API service is available first
            if (!$this->testApiConnection()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Prediction service is not available. Please try again later.'
                ], 503);
            }

            // Prepare model file path (convert relative path to absolute)
            $modelPath = public_path($selectedModel->FilePath);
            
            // Verify model file exists
            if (!file_exists($modelPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Model file not found on server.'
                ], 404);
            }

            // Call Flask API with new format
            $apiUrl = env('PREDICT_SERVICE_URL', 'http://localhost:5000');
            $token = $this->generateApiToken();
            
            // Prepare payload with model path and type
            $payload = [
                'pc_mxene_loading' => (float)$request->pc_mxene_loading,
                'laminin_peptide_loading' => (float)$request->laminin_peptide_loading,
                'stimulation_frequency' => (float)$request->stimulation_frequency,
                'applied_voltage' => (float)$request->applied_voltage,
                'model_path' => $modelPath,
                'model_type' => strtolower($selectedModel->LibType), // Convert to lowercase for API
            ];
            
            // Debug logging (remove in production)
            \Log::info('Making prediction API call (Admin)', [
                'url' => $apiUrl . '/predict/model',
                'token_preview' => substr($token, 0, 50) . '...',
                'payload' => $payload
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($apiUrl . '/predict/model', $payload);

            // Debug logging
            \Log::info('API Response (Admin)', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $prediction = $responseData['prediction'];
                
                // Save prediction to database with admin user
                Prediction::create([
                    'user_id' => Auth::id(),
                    'ml_model_id' => $selectedModel->id,
                    'MXene' => $request->pc_mxene_loading,
                    'Peptide' => $request->laminin_peptide_loading,
                    'Stimulation' => $request->stimulation_frequency,
                    'Voltage' => $request->applied_voltage,
                    'Result' => $prediction,
                    'PredictionDateTime' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'prediction' => round($prediction, 2),
                    'model_used' => $selectedModel->MLMName,
                    'message' => 'Prediction successful and saved to database!'
                ]);
            } else {
                $errorMessage = 'Failed to get prediction from API';
                $responseBody = $response->json();
                
                if ($response->status() === 401) {
                    $errorMessage = 'Authentication failed with prediction service';
                } elseif ($responseBody && isset($responseBody['error'])) {
                    $errorMessage = $responseBody['error'];
                }
                
                // Enhanced error logging
                \Log::error('API call failed (Admin)', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'error_message' => $errorMessage
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                    'debug_info' => env('APP_DEBUG') ? [
                        'api_status' => $response->status(),
                        'api_response' => $response->body()
                    ] : null
                ], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Exception in makePrediction (Admin)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error connecting to prediction service: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function history()
    {
        $predictions = Prediction::with('mlModel')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.history', compact('predictions'));
    }

    private function generateApiToken()
    {
        // Generate proper JWT token compatible with Flask API
        $payload = [
            'user_id' => Auth::id(),
            'username' => Auth::user()->Username,
            'iat' => time(),
            'exp' => time() + (60 * 60), // 1 hour expiration
        ];
        
        // Use the same secret key as Flask API (default: 'jwt_secret')
        $secretKey = env('JWT_SECRET', 'jwt_secret');
        
        // Create proper JWT token using Firebase JWT library
        return JWT::encode($payload, $secretKey, 'HS256');
    }
}
