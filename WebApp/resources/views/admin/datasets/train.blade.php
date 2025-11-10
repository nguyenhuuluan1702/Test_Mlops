@extends('layouts.app')

@section('sidebar')
<x-navigation.admin-sidebar />
@endsection

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">
                <i class="bi bi-cpu"></i> Train Machine Learning Model
            </h2>

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.datasets.index') }}">Datasets</a></li>
                    <li class="breadcrumb-item active">Train Model</li>
                </ol>
            </nav>

            <!-- Dataset Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-database"></i> Dataset Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Dataset Name:</strong> {{ $dataset->DatasetName }}</p>
                            <p><strong>Description:</strong> {{ $dataset->Description ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Uploaded By:</strong> {{ $dataset->user->FullName ?? 'Unknown' }}</p>
                            <p><strong>Upload Date:</strong> {{ $dataset->UploadDate }}</p>
                            <p><strong>File Path:</strong> <code>{{ $dataset->FilePath }}</code></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Training Configuration Form -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Training Configuration</h5>
                </div>
                <div class="card-body">
                    <form id="trainingForm" action="{{ route('admin.datasets.train', $dataset->DatasetId) }}" method="POST">
                        @csrf

                        <!-- Training Method -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Training Method</label>
                                <select name="training_method" id="training_method" class="form-select">
                                    <option value="process">Direct Process (Python Script)</option>
                                    <option value="api" selected>API Call (Flask Service)</option>
                                </select>
                                <small class="text-muted">API call is recommended for better monitoring</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Model Name (Optional)</label>
                                <input type="text" name="model_name" class="form-control" 
                                       placeholder="Auto-generated if empty"
                                       value="RF_Model_{{ $dataset->DatasetName }}_{{ date('Ymd') }}">
                            </div>
                        </div>

                        <!-- Hyperparameters -->
                        <h6 class="border-bottom pb-2 mb-3">Hyperparameters</h6>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="n_estimators" class="form-label">Number of Trees (n_estimators)</label>
                                <input type="number" name="n_estimators" id="n_estimators" 
                                       class="form-control" value="100" min="10" max="1000">
                                <small class="text-muted">Default: 100</small>
                            </div>

                            <div class="col-md-4">
                                <label for="max_depth" class="form-label">Max Depth</label>
                                <input type="number" name="max_depth" id="max_depth" 
                                       class="form-control" placeholder="None (unlimited)" min="1" max="50">
                                <small class="text-muted">Leave empty for unlimited</small>
                            </div>

                            <div class="col-md-4">
                                <label for="test_size" class="form-label">Test Size (%)</label>
                                <input type="number" name="test_size" id="test_size" 
                                       class="form-control" value="20" min="10" max="50" step="5">
                                <small class="text-muted">Default: 20%</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="random_state" class="form-label">Random State</label>
                                <input type="number" name="random_state" id="random_state" 
                                       class="form-control" value="42" min="0">
                                <small class="text-muted">For reproducibility</small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('admin.datasets.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Datasets
                            </a>
                            
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" id="validateBtn">
                                    <i class="bi bi-check-circle"></i> Validate Settings
                                </button>
                                <button type="submit" class="btn btn-success" id="trainBtn">
                                    <i class="bi bi-play-circle"></i> Start Training
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Training Progress (Hidden initially) -->
            <div class="card mb-4 d-none" id="progressCard">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-hourglass-split"></i> Training Progress</h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="progressBar">
                            0%
                        </div>
                    </div>
                    <div id="progressMessage" class="text-center">
                        <p class="mb-0">Initializing training...</p>
                    </div>
                    <div id="trainingLog" class="mt-3 p-3 bg-light rounded" style="max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.9em;">
                        <div class="text-muted">Training logs will appear here...</div>
                    </div>
                </div>
            </div>

            <!-- Information Panel -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Training Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">About Random Forest</h6>
                            <ul class="small">
                                <li>Ensemble learning method using multiple decision trees</li>
                                <li>Good for both regression and classification</li>
                                <li>Reduces overfitting compared to single decision trees</li>
                                <li>Can handle non-linear relationships</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Parameter Recommendations</h6>
                            <ul class="small">
                                <li><strong>n_estimators:</strong> Start with 100, increase for better performance</li>
                                <li><strong>max_depth:</strong> Leave unlimited or set 10-30 to prevent overfitting</li>
                                <li><strong>test_size:</strong> 20% is standard, use 30% for small datasets</li>
                                <li><strong>Training time:</strong> Typically 1-10 minutes depending on dataset size</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('trainingForm');
    const trainBtn = document.getElementById('trainBtn');
    const validateBtn = document.getElementById('validateBtn');
    const progressCard = document.getElementById('progressCard');
    const progressBar = document.getElementById('progressBar');
    const progressMessage = document.getElementById('progressMessage');
    const trainingLog = document.getElementById('trainingLog');

    // Validate settings
    validateBtn.addEventListener('click', function() {
        const nEstimators = document.getElementById('n_estimators').value;
        const maxDepth = document.getElementById('max_depth').value;
        const testSize = document.getElementById('test_size').value;

        let messages = [];
        if (nEstimators < 50) messages.push('⚠️ Low n_estimators may result in underfitting');
        if (nEstimators > 500) messages.push('⚠️ High n_estimators will increase training time');
        if (maxDepth && maxDepth < 3) messages.push('⚠️ Very shallow trees may not capture patterns');
        if (testSize < 15) messages.push('⚠️ Small test size may not be representative');

        if (messages.length === 0) {
            alert('✅ Settings look good! You can start training.');
        } else {
            alert('Validation Warnings:\n\n' + messages.join('\n'));
        }
    });

    // Form submission with progress simulation
    form.addEventListener('submit', function(e) {
        if (!confirm('Start training with current settings? This may take several minutes.')) {
            e.preventDefault();
            return;
        }

        // Show progress card
        progressCard.classList.remove('d-none');
        trainBtn.disabled = true;
        trainBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Training...';

        // Simulate progress (in real implementation, use WebSocket or polling)
        let progress = 0;
        const interval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            
            progressBar.style.width = progress + '%';
            progressBar.textContent = Math.round(progress) + '%';

            // Update message based on progress
            if (progress < 20) {
                progressMessage.innerHTML = '<p class="mb-0">📂 Loading dataset...</p>';
            } else if (progress < 40) {
                progressMessage.innerHTML = '<p class="mb-0">🔢 Preprocessing data...</p>';
            } else if (progress < 70) {
                progressMessage.innerHTML = '<p class="mb-0">🚀 Training model...</p>';
            } else {
                progressMessage.innerHTML = '<p class="mb-0">📊 Evaluating performance...</p>';
            }
        }, 1000);

        // Note: In real implementation, clear interval when training completes
    });
});
</script>
@endpush
@endsection
