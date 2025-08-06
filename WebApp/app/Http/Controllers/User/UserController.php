<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use App\Models\User;
use App\Models\MLModel;
use App\Models\Prediction;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('user');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $totalPredictions = Prediction::where('user_id', $user->id)->count();
        $recentPredictions = Prediction::with('mlModel')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('user.dashboard', compact('totalPredictions', 'recentPredictions'));
    }

    public function predict()
    {
        // Get available active models for user selection
        $models = MLModel::where('IsActive', true)->get();
        return view('user.predict', compact('models'));
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
            \Log::info('Making prediction API call with new format', [
                'url' => $apiUrl . '/predict/model',
                'token_preview' => substr($token, 0, 50) . '...',
                'payload' => $payload
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($apiUrl . '/predict/model', $payload);

            // Debug logging
            \Log::info('API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $prediction = $responseData['prediction'];
                
                // Save prediction to database
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
                \Log::error('API call failed', [
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
            \Log::error('Exception in makePrediction', [
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
        
        return view('user.history', compact('predictions'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'FullName' => 'required|string|max:255',
            'Gender' => 'required|in:Male,Female',
            'BirthDate' => 'required|date',
            'Address' => 'required|string|max:255',
            'Username' => 'required|string|max:255|unique:users,Username,' . $user->id,
        ]);

        $user->update([
            'FullName' => $request->FullName,
            'Gender' => $request->Gender,
            'BirthDate' => $request->BirthDate,
            'Address' => $request->Address,
            'Username' => $request->Username,
        ]);

        return redirect()->route('user.profile')->with('success', 'Profile updated successfully.');
    }

    public function security()
    {
        return view('user.security');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->Password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'Password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('user.security')->with('success', 'Password changed successfully.');
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
