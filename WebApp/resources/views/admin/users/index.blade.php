@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('sidebar')
    <x-navigation.admin-sidebar />
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Users</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                                            <table id="users" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>User Code</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Gender</th>
                                <th>Address</th>
                                <th>Predictions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    <tbody>
                        @foreach($users as $user)
                        @php
                            $predictionCount = $user->predictions()->count();
                        @endphp
                        <tr>
                            <td>{{ $user->UserCode }}</td>
                            <td>{{ $user->FullName }}</td>
                            <td>{{ $user->Username }}</td>
                            <td>{{ $user->Gender }}</td>
                            <td>{{ $user->Address }}</td>
                            <td>
                                <span class="badge {{ $predictionCount > 0 ? 'bg-warning' : 'bg-light' }}">
                                    {{ $predictionCount }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <x-ui.action-buttons :actions="[
                                        [
                                            'type' => 'link',
                                            'url' => route('admin.users.edit', $user),
                                            'color' => 'info',
                                            'icon' => 'fas fa-edit',
                                            'text' => 'Edit',
                                            'tooltip' => 'Edit user'
                                        ],
                                        [
                                            'type' => 'form',
                                            'method' => 'POST',
                                            'url' => route('admin.users.reset-password', $user),
                                            'color' => 'warning',
                                            'icon' => 'fas fa-key',
                                            'text' => 'Reset',
                                            'confirm' => 'Reset password for this user?',
                                            'tooltip' => 'Reset password'
                                        ]
                                    ]" />
                                    
                                    <button type="button" class="btn btn-sm {{ $predictionCount > 0 ? 'btn-warning' : 'btn-danger' }}" 
                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                        <i class="fas fa-trash"></i> 
                                        Delete{{ $predictionCount > 0 ? " ({$predictionCount})" : '' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modals -->
@foreach($users as $user)
    @php
        $predictionCount = $user->predictions()->count();
    @endphp
    
    <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" 
         aria-labelledby="deleteModalLabel{{ $user->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header {{ $predictionCount > 0 ? 'bg-warning' : 'bg-danger' }} text-white">
                    <h5 class="modal-title" id="deleteModalLabel{{ $user->id }}">
                        <i class="fas fa-exclamation-triangle"></i>
                        Delete User: <span class="text-wrap">{{ $user->FullName }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($predictionCount > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning!</strong> This user has <strong>{{ $predictionCount }}</strong> associated prediction(s).
                        </div>
                        
                        <p>Choose how you want to proceed:</p>
                        
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <div class="card border-secondary h-100">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h6 class="card-title text-secondary">
                                            <i class="fas fa-shield-alt"></i> Safe Option
                                        </h6>
                                        <p class="card-text small flex-grow-1">Cancel deletion and keep user with all prediction history.</p>
                                        <button type="button" class="btn btn-secondary btn-sm mt-auto" data-bs-dismiss="modal">
                                            <i class="fas fa-arrow-left"></i> Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border-warning h-100">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h6 class="card-title text-warning">
                                            <i class="fas fa-eye-slash"></i> Anonymize
                                        </h6>
                                        <p class="card-text small flex-grow-1">Remove personal data but keep predictions for data integrity.</p>
                                        <form method="POST" action="{{ route('admin.users.anonymize', $user) }}" class="d-inline mt-auto">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm" 
                                                    onclick="return confirm('This will anonymize user data but preserve {{ $predictionCount }} predictions. Continue?')">
                                                <i class="fas fa-eye-slash"></i> Anonymize
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border-danger h-100">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h6 class="card-title text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Force Delete
                                        </h6>
                                        <p class="card-text small flex-grow-1">Delete user AND all {{ $predictionCount }} prediction(s).</p>
                                        <form method="POST" action="{{ route('admin.users.force-delete', $user) }}" class="d-inline mt-auto">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('⚠️ FINAL WARNING: This will permanently delete the user and ALL {{ $predictionCount }} predictions. This cannot be undone! Are you absolutely sure?')">
                                                <i class="fas fa-trash"></i> Force Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                <strong>Recommendation:</strong> For data privacy compliance, consider anonymizing users instead of deleting their prediction history.
                            </small>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            This user has no associated predictions. It's safe to delete.
                        </div>
                        
                        <p>Are you sure you want to delete the user <strong>"{{ $user->FullName }}"</strong>?</p>
                        <p class="text-muted small">This action will permanently remove the user account and profile information.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    @if($predictionCount == 0)
                        <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete User
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-user-management.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/admin-panel.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize admin panel
    if (typeof AdminPanel !== 'undefined') {
        window.adminPanel = new AdminPanel('users');
    }
});
</script>
@endpush
