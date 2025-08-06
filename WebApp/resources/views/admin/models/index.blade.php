@extends('layouts.app')

@section('title', 'ML Models')
@section('page-title', 'ML Models')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Models</li>
@endsection

@section('sidebar')
    <x-navigation.admin-sidebar />
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Machine Learning Models</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.models.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Add Model
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Note:</strong> The default system model (<span class="badge badge-primary"><i class="bi bi-star"></i> Default</span>) is protected and cannot be deleted to ensure the system always has a working prediction model.
                </div>
                
                <div class="table-responsive">
                    <table id="models-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Model Name</th>
                                <th>Library Type</th>
                                <th>File Path</th>
                                <th>Status</th>
                                <th>Predictions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($models as $model)
                            <tr>
                                <td>
                                    {{ $model->MLMName }}
                                    @php
                                        $isDefault = $model->MLMName === 'Default ANN Model' || 
                                                   str_contains($model->FilePath, 'ann_model.keras') || 
                                                   $model->id === 1;
                                    @endphp
                                    @if($isDefault)
                                        <span class="badge badge-primary ms-1">
                                            <i class="bi bi-star"></i> Default
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $model->LibType }}</span>
                                </td>
                                <td>
                                    <code>{{ $model->FilePath }}</code>
                                </td>
                                <td>
                                    @if($model->IsActive)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $predictionCount = $model->predictions()->count();
                                    @endphp
                                    <span class="badge {{ $predictionCount > 0 ? 'badge-warning' : 'badge-light' }}">
                                        {{ $predictionCount }}
                                    </span>
                                </td>
                                <td>{{ $model->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.models.edit', $model) }}" class="btn btn-sm btn-info me-2">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        
                                        @php
                                            $predictionCount = $model->predictions()->count();
                                            $isDefault = $model->MLMName === 'Default ANN Model' || 
                                                       str_contains($model->FilePath, 'ann_model.keras') || 
                                                       $model->id === 1;
                                        @endphp
                                        
                                        @if($isDefault)
                                            <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete default system model">
                                                <i class="bi bi-shield-lock"></i> Protected
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm {{ $predictionCount > 0 ? 'btn-warning' : 'btn-danger' }}" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal{{ $model->id }}">
                                                <i class="bi bi-trash"></i> 
                                                Delete {{ $predictionCount > 0 ? "({$predictionCount})" : '' }}
                                            </button>
                                        @endif
                                        
                                        <!-- Delete Modal (only for non-default models) -->
                                        @if(!$isDefault)
                                        <div class="modal fade" id="deleteModal{{ $model->id }}" tabindex="-1" 
                                             aria-labelledby="deleteModalLabel{{ $model->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header {{ $predictionCount > 0 ? 'bg-warning' : 'bg-danger' }} text-white">
                                                        <h5 class="modal-title" id="deleteModalLabel{{ $model->id }}">
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                            Delete Model: <span class="text-wrap">{{ $model->MLMName }}</span>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @if($predictionCount > 0)
                                                            <div class="alert alert-warning">
                                                                <i class="bi bi-exclamation-triangle"></i>
                                                                <strong>Warning!</strong> This model has <strong>{{ $predictionCount }}</strong> associated prediction(s).
                                                            </div>
                                                            
                                                            <p>Choose how you want to proceed:</p>
                                                            
                                                            <div class="row g-2">
                                                                <div class="col-12 col-md-4">
                                                                    <div class="card border-secondary h-100">
                                                                        <div class="card-body text-center d-flex flex-column">
                                                                            <h6 class="card-title text-secondary">
                                                                                <i class="bi bi-shield-check"></i> Safe Option
                                                                            </h6>
                                                                            <p class="card-text small flex-grow-1">Cancel deletion.</p>
                                                                            <button type="button" class="btn btn-secondary btn-sm mt-auto" data-bs-dismiss="modal">
                                                                                <i class="bi bi-arrow-left"></i> Cancel
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-4">
                                                                    <div class="card border-warning h-100">
                                                                        <div class="card-body text-center d-flex flex-column">
                                                                            <h6 class="card-title text-warning">
                                                                                <i class="bi bi-pause-circle"></i> Deactivate
                                                                            </h6>
                                                                            <p class="card-text small flex-grow-1">Keep model but make it inactive.</p>
                                                                            <form method="POST" action="{{ route('admin.models.update', $model) }}" class="d-inline mt-auto">
                                                                                @csrf
                                                                                @method('PUT')
                                                                                <input type="hidden" name="MLMName" value="{{ $model->MLMName }}">
                                                                                <input type="hidden" name="LibType" value="{{ $model->LibType }}">
                                                                                <!-- Don't include IsActive checkbox to make it false -->
                                                                                <button type="submit" class="btn btn-warning btn-sm">
                                                                                    <i class="bi bi-pause"></i> Deactivate
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-4">
                                                                    <div class="card border-danger h-100">
                                                                        <div class="card-body text-center d-flex flex-column">
                                                                            <h6 class="card-title text-danger">
                                                                                <i class="bi bi-exclamation-triangle"></i> Force Delete
                                                                            </h6>
                                                                            <p class="card-text small flex-grow-1">Delete model AND all {{ $predictionCount }} prediction(s).</p>
                                                                            <form method="POST" action="{{ route('admin.models.force-delete', $model) }}" class="d-inline mt-auto">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                                                        onclick="return confirm('⚠️ FINAL WARNING: This will permanently delete the model and ALL {{ $predictionCount }} predictions. This cannot be undone! Are you absolutely sure?')">
                                                                                    <i class="bi bi-trash"></i> Force Delete
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mt-3">
                                                                <small class="text-muted">
                                                                    <i class="bi bi-info-circle"></i>
                                                                    <strong>Alternative:</strong> You can also deactivate this model instead of deleting it by editing the model and unchecking "Active" status.
                                                                </small>
                                                            </div>
                                                        @else
                                                            <div class="alert alert-info">
                                                                <i class="bi bi-info-circle"></i>
                                                                This model has no associated predictions. It's safe to delete.
                                                            </div>
                                                            
                                                            <p>Are you sure you want to delete the model <strong>"{{ $model->MLMName }}"</strong>?</p>
                                                            <p class="text-muted small">This action will permanently remove the model file and database entry.</p>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="bi bi-x"></i> Cancel
                                                        </button>
                                                        @if($predictionCount == 0)
                                                            <form method="POST" action="{{ route('admin.models.delete', $model) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">
                                                                    <i class="bi bi-trash"></i> Delete Model
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $models->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/admin-panel.js') }}"></script>
<script>
// Initialize admin panel
$(document).ready(function() {
    if (typeof AdminPanel !== 'undefined') {
        window.adminPanel = new AdminPanel('models');
    }
});
</script>
@endpush
