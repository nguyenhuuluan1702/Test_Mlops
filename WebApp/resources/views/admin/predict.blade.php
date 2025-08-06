@extends('layouts.app')

@section('title', 'Make Prediction (Admin)')
@section('page-title', 'Make Prediction')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Predict</li>
@endsection

@section('sidebar')
    <x-navigation.admin-sidebar />
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/prediction-form.css') }}">
@endsection

@section('content')
<div class="prediction-form-container">
<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<div class="row">
    <!-- Form Section (Left Side - Wider) -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calculator me-2"></i>
                    Schwann Cell Viability Prediction
                    <span class="admin-badge">ADMIN</span>
                </h3>
            </div>
            <div class="card-body">
                <form id="predictionForm">
                    @csrf
                    
                    <!-- AI Model Selection -->
                    <div class="form-group">
                        <label for="ml_model_id" class="form-label">
                            <i class="bi bi-cpu me-1"></i>
                            AI Model Selection
                        </label>
                        <select class="form-control" id="ml_model_id" name="ml_model_id" required>
                            <option value="">Select a model...</option>
                            @foreach($models as $model)
                                <option value="{{ $model->id }}" 
                                        data-lib-type="{{ $model->LibType }}"
                                        data-file-size="{{ $model->file_size }}"
                                        {{ $loop->first ? 'selected' : '' }}>
                                    {{ $model->MLMName }} ({{ ucfirst($model->LibType) }})
                                    @if($model->file_size > 0)
                                        - {{ $model->file_size }}MB
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted" id="model-info">
                            @if($models->count() > 0)
                                Select an AI model to use for prediction. Default: {{ $models->first()->MLMName }}
                            @else
                                No active models available. Please add models in Model Management.
                            @endif
                        </small>
                        
                        <!-- Model Info Card -->
                        <div class="model-info-card" id="selectedModelInfo">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="bi bi-robot me-1"></i>
                                        <span id="selectedModelName">-</span>
                                    </h6>
                                    <small class="text-muted">
                                        Library: <span class="model-badge" id="selectedModelBadge">-</span>
                                        | Size: <strong id="selectedModelSize">-</strong>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <i class="bi bi-check-circle-fill text-success icon-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($models->count() === 0)
                        <!-- No Models Available Alert -->
                        <div class="no-models-alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No AI Models Available</strong>
                            <p class="mb-0 mt-2">There are currently no active AI models in the system. Please add models in <a href="{{ route('admin.models') }}">Model Management</a>.</p>
                        </div>
                    @endif

                    <!-- pc-MXene loading -->
                    <div class="form-group">
                        <label for="pc_mxene_loading" class="form-label">
                            <i class="bi bi-droplet me-1"></i>
                            pc-MXene loading
                        </label>
                        <input type="number" step="0.001" class="form-control" id="pc_mxene_loading" 
                               name="pc_mxene_loading" min="0" max="0.03" placeholder="Enter pc-MXene loading (0 to 0.03)" required>
                        <small class="form-text text-muted">Concentration in mg/mL</small>
                    </div>

                    <!-- Laminin peptide loading -->
                    <div class="form-group">
                        <label for="laminin_peptide_loading" class="form-label">
                            <i class="bi bi-capsule me-1"></i>
                            Laminin peptide loading
                        </label>
                        <input type="number" step="0.1" class="form-control" id="laminin_peptide_loading" 
                               name="laminin_peptide_loading" min="0" max="5.9" placeholder="Enter Laminin peptide loading (0 to 5.9)" required>
                        <small class="form-text text-muted">Concentration in μg/mL</small>
                    </div>

                    <!-- Stimulation frequency -->
                    <div class="form-group">
                        <label for="stimulation_frequency" class="form-label">
                            <i class="bi bi-broadcast me-1"></i>
                            Stimulation frequency
                        </label>
                        <input type="number" step="0.1" class="form-control" id="stimulation_frequency" 
                               name="stimulation_frequency" min="0" max="3" placeholder="Enter stimulation frequency (0 to 3)" required>
                        <small class="form-text text-muted">Frequency in Hz</small>
                    </div>

                    <!-- Applied voltage -->
                    <div class="form-group">
                        <label for="applied_voltage" class="form-label">
                            <i class="bi bi-lightning me-1"></i>
                            Applied voltage
                        </label>
                        <input type="number" step="0.1" class="form-control" id="applied_voltage" 
                               name="applied_voltage" min="0" max="3" placeholder="Enter applied voltage (0 to 3)" required>
                        <small class="form-text text-muted">Voltage in V</small>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-predict w-100" 
                                id="predictButton" {{ $models->count() === 0 ? 'disabled' : '' }}>
                            <i class="bi bi-calculator me-2"></i>
                            @if($models->count() > 0)
                                Predict Cell Viability (Admin)
                            @else
                                No Models Available
                            @endif
                        </button>
                        @if($models->count() === 0)
                            <small class="form-text text-danger mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Prediction is disabled until models are available. <a href="{{ route('admin.models') }}">Add models here</a>.
                            </small>
                        @endif
                    </div>
                </form>
                
                <!-- Result Display -->
                <div id="predictionResult" class="mt-4 prediction-result-hidden">
                    <!-- Results will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Guidelines Section (Right Side - Narrower) -->
    <div class="col-lg-4">
        <div class="card guidelines-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Parameter Guidelines
                </h3>
            </div>
            <div class="card-body">
                @if($models->count() > 0)
                    <div class="parameter-item mb-3">
                        <h6 class="mb-2">
                            <i class="bi bi-cpu text-info me-1"></i>
                            AI Model Selection
                        </h6>
                        <p class="mb-2 small">
                            <strong>Available Models:</strong> {{ $models->count() }}<br>
                            <strong>Current Selection:</strong> Dynamic based on your choice
                        </p>
                        <div class="small">
                            @foreach($models as $model)
                                <span class="model-badge {{ strtolower($model->LibType) }} me-1 mb-1">
                                    {{ $model->MLMName }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="parameter-item">
                    <h6 class="mb-2">
                        <i class="bi bi-droplet text-primary me-1"></i>
                        pc-MXene loading
                    </h6>
                    <p class="mb-0 small">
                        <strong>Range:</strong> 0 to 0.03 mg/mL<br>
                        <strong>Description:</strong> Concentration of pc-MXene nanosheets
                    </p>
                </div>

                <div class="parameter-item">
                    <h6 class="mb-2">
                        <i class="bi bi-capsule text-success me-1"></i>
                        Laminin peptide loading
                    </h6>
                    <p class="mb-0 small">
                        <strong>Range:</strong> 0 to 5.9 μg/mL<br>
                        <strong>Description:</strong> Concentration of laminin peptide
                    </p>
                </div>

                <div class="parameter-item">
                    <h6 class="mb-2">
                        <i class="bi bi-broadcast text-warning me-1"></i>
                        Stimulation frequency
                    </h6>
                    <p class="mb-0 small">
                        <strong>Range:</strong> 0 to 3 Hz<br>
                        <strong>Description:</strong> Electric stimulation frequency
                    </p>
                </div>

                <div class="parameter-item">
                    <h6 class="mb-2">
                        <i class="bi bi-lightning text-danger me-1"></i>
                        Applied voltage
                    </h6>
                    <p class="mb-0 small">
                        <strong>Range:</strong> 0 to 3 V<br>
                        <strong>Description:</strong> Applied electric voltage
                    </p>
                </div>

                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="text-primary">
                        <i class="bi bi-lightbulb me-1"></i>
                        Admin Tips
                    </h6>
                    <ul class="small mb-0">
                        @if($models->count() > 0)
                            <li>Test different AI models for comparison</li>
                            <li>Admin predictions are logged for auditing</li>
                        @endif
                        <li>Use this tool to verify model performance</li>
                        <li>Results can help in model evaluation</li>
                        <li>Consider parameter ranges when training models</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/prediction-form-config.js') }}"></script>
<script src="{{ asset('js/prediction-form.js') }}"></script>
<script src="{{ asset('js/admin-prediction.js') }}"></script>
<script>
// Register admin prediction form configuration
window.PredictionFormConfig.register('admin-predict', {
    submitUrl: '{{ route('admin.predict.make') }}',
    csrfToken: '{{ csrf_token() }}',
    userType: 'admin'
});
</script>
@endsection
