@extends('layouts.app')

@section('title', 'Security')
@section('page-title', 'Security Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Security</li>
@endsection

@section('sidebar')
    <x-navigation.user-sidebar />
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Change Password</h3>
            </div>
            <form method="POST" action="{{ route('user.security.change-password') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                               id="new_password" name="new_password" minlength="6" required>
                        <small class="form-text text-muted">Password must be at least 6 characters long.</small>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password_confirmation">Confirm New Password *</label>
                        <input type="password" class="form-control" 
                               id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                    <a href="{{ route('user.profile') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Security Tips</h3>
            </div>
            <div class="card-body">
                <h5><i class="bi bi-shield-check text-success"></i> Password Security</h5>
                <ul class="list-unstyled">
                    <li><i class="bi bi-check text-success"></i> Use at least 6 characters</li>
                    <li><i class="bi bi-check text-success"></i> Include numbers and symbols</li>
                    <li><i class="bi bi-check text-success"></i> Avoid personal information</li>
                    <li><i class="bi bi-check text-success"></i> Don't reuse old passwords</li>
                </ul>
                
                <hr>
                
                <h5><i class="bi bi-info-circle text-info"></i> Account Recovery</h5>
                <p class="text-muted">If you forget your password, please contact the system administrator for a password reset.</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Account Activity</h3>
            </div>
            <div class="card-body">
                <p><strong>Last Login:</strong><br>
                <small class="text-muted">{{ Auth::user()->updated_at->format('F d, Y H:i:s') }}</small></p>
                
                <p><strong>Account Created:</strong><br>
                <small class="text-muted">{{ Auth::user()->created_at->format('F d, Y') }}</small></p>
                
                <hr>
                
                <div class="text-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to logout?')">
                            <i class="bi bi-box-arrow-right"></i> Logout from All Sessions
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/user-forms.js') }}"></script>
@endsection
