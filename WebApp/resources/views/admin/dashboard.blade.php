@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('sidebar')
    <x-navigation.admin-sidebar />
@endsection

@section('content')
<div class="row">
    <!-- Statistics using Small Box Components -->
    <x-ui.small-box 
        color="info" 
        :value="$totalUsers" 
        label="Total Users" 
        icon="fas fa-users" 
        :link="route('admin.users')" 
        linkText="More info" />
    
    <x-ui.small-box 
        color="success" 
        :value="$totalModels" 
        label="ML Models" 
        icon="fas fa-brain" 
        :link="route('admin.models')" 
        linkText="More info" />
    
    <x-ui.small-box 
        color="warning" 
        :value="$activeModels" 
        label="Active Models" 
        icon="fas fa-check-circle" 
        :link="route('admin.models')" 
        linkText="More info" />
    
    <x-ui.small-box 
        color="danger" 
        :value="$adminPredictions" 
        label="Admin Predictions" 
        icon="fas fa-calculator" 
        :link="route('admin.history')" 
        linkText="View history" />
</div>

<div class="row">
    <div class="col-12">
        <x-ui.card title="Admin System Overview">
            <p>Welcome to the Schwann Cell Viability Prediction Admin Panel. You now have access to all system features including prediction capabilities.</p>
            
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-cogs text-primary"></i> Management Features</h5>
                    <ul>
                        <li><strong>User Management:</strong> Create, edit, delete users and reset their passwords</li>
                        <li><strong>ML Model Management:</strong> Upload, manage and configure machine learning models</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5><i class="fas fa-calculator text-success"></i> Prediction Features</h5>
                    <ul>
                        <li><strong>Make Predictions:</strong> Use AI models to predict Schwann cell viability</li>
                        <li><strong>View History:</strong> Track and analyze your prediction results</li>
                    </ul>
                </div>
            </div>
            
            <!-- <div class="alert alert-info mt-3">
                <h6><i class="fas fa-info-circle"></i> New Admin Features Added</h6>
                <p class="mb-0">As an admin, you can now access prediction functionality to test models and verify system performance. All admin predictions are logged separately for auditing purposes.</p>
            </div> -->
            
            <div class="mt-3">
                <a href="{{ route('admin.predict') }}" class="btn btn-success me-2">
                    <i class="fas fa-calculator"></i> Make Prediction
                </a>
                <a href="{{ route('admin.history') }}" class="btn btn-info">
                    <i class="fas fa-history"></i> View History
                </a>
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
