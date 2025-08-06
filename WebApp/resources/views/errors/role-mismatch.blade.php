<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Access Denied - Role Mismatch</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/ml.ico') }}">
    
    <!-- Bootstrap CSS -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    
    <!-- Role Mismatch Error CSS -->
    <link rel="stylesheet" href="{{ asset('css/error-role-mismatch.css') }}">
</head>
<body>
    <div class="role-mismatch-card">
        <div class="role-icon warning-animation">
            <i class="bi bi-shield-exclamation"></i>
        </div>
        
        <h2 class="text-danger mb-3">Access Denied</h2>
        <h5 class="text-muted mb-4">Role Permission Mismatch</h5>
        
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Hello {{ $user_name }}!</strong><br>
            You are currently logged in but don't have the required permissions to access this page.
        </div>
        
        <div class="mb-4">
            <p class="mb-3"><strong>Permission Details:</strong></p>
            <div>
                <span class="role-badge current">
                    <i class="bi bi-person-circle me-1"></i>
                    Your Role: {{ strtoupper($current_role) }}
                </span>
                <br>
                <span class="role-badge required">
                    <i class="bi bi-shield-lock me-1"></i>
                    Required: {{ strtoupper($required_role) }}
                </span>
            </div>
        </div>
        
        <div class="mb-4">
            <p class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                To access this page, you need to logout and login with an account that has 
                <strong>{{ strtoupper($required_role) }}</strong> privileges.
            </p>
        </div>
        
        <div class="row g-2">
            <div class="col-md-6">
                <button type="button" class="btn btn-secondary btn-back w-100" onclick="goBack()">
                    <i class="bi bi-arrow-left me-2"></i>
                    Go Back
                </button>
            </div>
            <div class="col-md-6">
                <button type="button" class="btn btn-danger btn-logout w-100" onclick="showLogoutConfirm()">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </button>
            </div>
        </div>
        
        <div class="mt-4 pt-3 border-top">
            <small class="text-muted">
                <i class="bi bi-clock me-1"></i>
                If you believe this is an error, please contact your administrator.
            </small>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <x-auth.role-mismatch-modal :required-role="$required_role" />

    <!-- Bootstrap JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Role Mismatch Error JS -->
    <script src="{{ asset('js/error-role-mismatch.js') }}"></script>
    <script>
        // Set role-based redirect URL for JavaScript
        @if($current_role === 'admin')
            window.roleBasedRedirect = '{{ route("admin.dashboard") }}';
        @elseif($current_role === 'user')
            window.roleBasedRedirect = '{{ route("user.dashboard") }}';
        @else
            window.roleBasedRedirect = '{{ route("login") }}';
        @endif
    </script>
</body>
</html>
