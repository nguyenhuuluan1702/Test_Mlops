@extends('layouts.app')

@section('title', 'Upload Model')
@section('page-title', 'Upload Model')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.models') }}">Models</a></li>
    <li class="breadcrumb-item active">Upload</li>
@endsection

@section('sidebar')
    <x-navigation.admin-sidebar />
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if ($errors->any())
            <div class="alert alert-danger">
                <h5><i class="icon fas fa-ban"></i> Validation Errors:</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upload New Model</h3>
            </div>
            <form method="POST" action="{{ route('admin.models.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="MLMName">Model Name *</label>
                                <input type="text" class="form-control @error('MLMName') is-invalid @enderror" 
                                       id="MLMName" name="MLMName" value="{{ old('MLMName') }}" required>
                                @error('MLMName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="LibType">Library Type *</label>
                                <select class="form-control @error('LibType') is-invalid @enderror" id="LibType" name="LibType" required>
                                    <option value="">Select Library Type</option>
                                    <option value="keras" {{ old('LibType') == 'keras' ? 'selected' : '' }}>Keras/TensorFlow</option>
                                    <option value="pytorch" {{ old('LibType') == 'pytorch' ? 'selected' : '' }}>PyTorch</option>
                                    <option value="sklearn" {{ old('LibType') == 'sklearn' ? 'selected' : '' }}>Scikit-learn</option>
                                    <option value="xgboost" {{ old('LibType') == 'xgboost' ? 'selected' : '' }}>XGBoost</option>
                                    <option value="pickle" {{ old('LibType') == 'pickle' ? 'selected' : '' }}>Pickle</option>
                                    <option value="joblib" {{ old('LibType') == 'joblib' ? 'selected' : '' }}>Joblib</option>
                                </select>
                                @error('LibType')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="model_file">Model File *</label>
                        <input type="file" class="form-control-file @error('model_file') is-invalid @enderror" 
                               id="model_file" name="model_file" accept=".h5,.pkl,.keras,.json,.pt,.pth,.joblib,.xgb" required>
                        <small class="form-text text-muted">
                            Supported formats: 
                            <br><strong>Keras:</strong> .keras, .h5, .hdf5
                            <br><strong>PyTorch:</strong> .pt, .pth
                            <br><strong>Sklearn:</strong> .pkl, .joblib
                            <br><strong>XGBoost:</strong> .json, .model, .xgb
                            <br><strong>Max size:</strong> 100MB
                        </small>
                        @error('model_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="IsActive" name="IsActive" value="1" {{ old('IsActive') ? 'checked' : '' }}>
                            <label class="form-check-label" for="IsActive">
                                Set as Active Model
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Upload Model</button>
                    <a href="{{ route('admin.models') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
@endsection

@section('scripts')
<script src="{{ asset('js/admin-panel.js') }}"></script>
<script src="{{ asset('js/admin-model-create.js') }}"></script>
@endsection
