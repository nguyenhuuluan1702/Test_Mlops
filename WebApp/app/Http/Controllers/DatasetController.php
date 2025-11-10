<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Services\TrainingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DatasetController extends Controller
{
    /**
     * Hiển thị danh sách dataset.
     */
    public function index()
    {
        $datasets = Dataset::with('user')->orderByDesc('UploadDate')->get();
        return view('admin.datasets.index', compact('datasets'));
    }

    /**
     * Hiển thị form upload dataset mới.
     */
    public function create()
    {
        return view('admin.datasets.create');
    }

    /**
     * Lưu dataset mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        $request->validate([
            'DatasetName' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'dataset_file' => 'required|file|mimes:csv,txt,xlsx|max:10240', // Tối đa 10MB
        ]);

        // Lưu file vào storage/app/datasets
        $path = $request->file('dataset_file')->store('datasets', 'public');

        // Lưu thông tin vào DB
        Dataset::create([
            'DatasetName' => $request->DatasetName,
            'FilePath' => $path,
            'Description' => $request->Description,
            'UploadedBy' => Auth::id(), // user đang đăng nhập
        ]);

        return redirect()->route('admin.datasets.index')->with('success', 'Dataset uploaded successfully!');
    }

    /**
     * Hiển thị chi tiết dataset.
     */
    public function show($id)
    {
        $dataset = Dataset::findOrFail($id);
        return view('admin.datasets.show', compact('dataset'));
    }

    /**
     * Xóa dataset.
     */
    public function destroy($id)
    {
        $dataset = Dataset::findOrFail($id);

        // Xóa file vật lý nếu có
        if (Storage::exists($dataset->FilePath)) {
            Storage::delete($dataset->FilePath);
        }

        $dataset->delete();

        return redirect()->route('admin.datasets.index')->with('success', 'Dataset deleted successfully.');
    }

    /**
     * Hiển thị form training configuration
     */
    public function showTrainForm($id)
    {
        $dataset = Dataset::with('user')->findOrFail($id);
        return view('admin.datasets.train', compact('dataset'));
    }

    /**
     * Train model với dataset được chọn
     */
    public function train(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);
        $user = Auth::user();

        // Validate training parameters
        $request->validate([
            'training_method' => 'nullable|in:process,api',
            'n_estimators' => 'nullable|integer|min:10|max:1000',
            'max_depth' => 'nullable|integer|min:1|max:50',
            'test_size' => 'nullable|integer|min:10|max:50',
            'random_state' => 'nullable|integer|min:0',
            'model_name' => 'nullable|string|max:255',
        ]);

        // Prepare training options
        $options = [
            'n_estimators' => $request->input('n_estimators', 100),
            'max_depth' => $request->input('max_depth'),
            'test_size' => $request->input('test_size', 20) / 100, // Convert percentage to decimal
            'random_state' => $request->input('random_state', 42),
            'model_name' => $request->input('model_name'),
        ];

        // Sử dụng TrainingService để xử lý training
        $trainingService = app(TrainingService::class);
        
        // Choose training method
        $trainingMethod = $request->input('training_method', 'api');
        
        if ($trainingMethod === 'api') {
            $result = $trainingService->trainModelViaAPI($dataset, $user, $options);
        } else {
            $result = $trainingService->trainModel($dataset, $user, $options);
        }

        // Trả về kết quả
        if ($result['success']) {
            $message = 'Model trained successfully with dataset: ' . $dataset->DatasetName;
            if (isset($result['metrics'])) {
                $message .= sprintf(
                    ' | R²: %.4f | RMSE: %.4f | MAE: %.4f',
                    $result['metrics']['r2_score'] ?? 0,
                    $result['metrics']['rmse'] ?? 0,
                    $result['metrics']['mae'] ?? 0
                );
            }
            
            return redirect()->route('admin.datasets.index')
                ->with('success', $message);
        } else {
            return redirect()->route('admin.datasets.index')
                ->with('error', 'Training failed: ' . ($result['error'] ?? 'Unknown error'));
        }
    }
}
