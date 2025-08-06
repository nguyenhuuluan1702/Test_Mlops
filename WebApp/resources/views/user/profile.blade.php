@extends('layouts.app')

@section('title', 'Profile')
@section('page-title', 'Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('sidebar')
    <x-navigation.user-sidebar />
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile Information</h3>
            </div>
            <form method="POST" action="{{ route('user.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="UserCode">User Code</label>
                        <input type="text" class="form-control" id="UserCode" value="{{ $user->UserCode }}" readonly>
                        <small class="form-text text-muted">User code cannot be changed.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="FullName">Full Name *</label>
                        <input type="text" class="form-control @error('FullName') is-invalid @enderror" 
                               id="FullName" name="FullName" value="{{ old('FullName', $user->FullName) }}" required>
                        @error('FullName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="Username">Username *</label>
                        <input type="text" class="form-control @error('Username') is-invalid @enderror" 
                               id="Username" name="Username" value="{{ old('Username', $user->Username) }}" required>
                        @error('Username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="Gender">Gender *</label>
                        <select class="form-control @error('Gender') is-invalid @enderror" id="Gender" name="Gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('Gender', $user->Gender) == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('Gender', $user->Gender) == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('Gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="BirthDate">Birth Date *</label>
                        <input type="date" class="form-control @error('BirthDate') is-invalid @enderror" 
                               id="BirthDate" name="BirthDate" value="{{ old('BirthDate', $user->BirthDate) }}" required>
                        @error('BirthDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="Address">Address *</label>
                        <textarea class="form-control @error('Address') is-invalid @enderror" 
                                  id="Address" name="Address" rows="3" required>{{ old('Address', $user->Address) }}</textarea>
                        @error('Address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Account Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>User Code:</strong></td>
                        <td>{{ $user->UserCode }}</td>
                    </tr>
                    <tr>
                        <td><strong>Role:</strong></td>
                        <td><span class="badge badge-info">{{ $user->role->RoleName }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Member Since:</strong></td>
                        <td>{{ $user->created_at->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Last Updated:</strong></td>
                        <td>{{ $user->updated_at->format('F d, Y H:i') }}</td>
                    </tr>
                </table>
                
                <hr>
                
                <h5>Account Actions</h5>
                <div class="btn-group-vertical w-100">
                    <a href="{{ route('user.security') }}" class="btn btn-outline-primary">
                        <i class="bi bi-shield-lock"></i> Change Password
                    </a>
                    <a href="{{ route('user.history') }}" class="btn btn-outline-info">
                        <i class="bi bi-clock-history"></i> View Prediction History
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Need Help?</h3>
            </div>
            <div class="card-body">
                <p>If you need to reset your password or have any account issues, please contact the administrator.</p>
                <p class="text-muted">For technical support regarding predictions or the ML models, please reach out to the system administrator.</p>
            </div>
        </div>
    </div>
</div>
@endsection
