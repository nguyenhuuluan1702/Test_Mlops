@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Users</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('sidebar')
    <x-navigation.admin-sidebar />
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Create New User</h3>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> UserCode will be automatically generated with format: USR + 7 random digits (e.g., USR1234567)
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="FullName">Full Name *</label>
                                <input type="text" class="form-control @error('FullName') is-invalid @enderror" 
                                       id="FullName" name="FullName" value="{{ old('FullName') }}" required>
                                @error('FullName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Username">Username *</label>
                                <input type="text" class="form-control @error('Username') is-invalid @enderror" 
                                       id="Username" name="Username" value="{{ old('Username') }}" required>
                                @error('Username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Password">Password *</label>
                                <input type="password" class="form-control @error('Password') is-invalid @enderror" 
                                       id="Password" name="Password" required>
                                @error('Password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Gender">Gender *</label>
                                <select class="form-control @error('Gender') is-invalid @enderror" id="Gender" name="Gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('Gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('Gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('Gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="BirthDate">Birth Date *</label>
                                <input type="date" class="form-control @error('BirthDate') is-invalid @enderror" 
                                       id="BirthDate" name="BirthDate" value="{{ old('BirthDate') }}" required>
                                @error('BirthDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="Address">Address *</label>
                        <textarea class="form-control @error('Address') is-invalid @enderror" 
                                  id="Address" name="Address" rows="3" required>{{ old('Address') }}</textarea>
                        @error('Address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
