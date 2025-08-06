<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Schwann Cell Viability Prediction')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/ml.ico') }}">
    
    {{-- External CSS --}}
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}">
    
    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app-layout.css') }}">
    
    @yield('styles')
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        {{-- Header Component --}}
        <x-layout.header />

        {{-- Sidebar Component --}}
        <x-layout.sidebar />

        {{-- Content Wrapper Component --}}
        <x-layout.content />

        {{-- Footer Component --}}
        <x-layout.footer />
    </div>

    {{-- Logout Modal Component --}}
    <x-auth.logout-modal />

    {{-- JavaScript --}}
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/js/adminlte.min.js') }}"></script>
    
    {{-- Custom JavaScript with global variables --}}
    <script>
        // Global variables for JavaScript
        window.logoutRoute = '{{ route("logout") }}';
        window.csrfToken = '{{ csrf_token() }}';
    </script>
    <script src="{{ asset('js/app-layout.js') }}"></script>
    
    @yield('scripts')
</body>
</html>
