{{-- User Sidebar --}}
<!-- Brand Logo -->
<a href="{{ route('user.dashboard') }}" class="brand-link">
    <img src="{{ asset('images/ml.ico') }}" alt="System Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">Schwann Cell Viability<br>Prediction System</span>
</a>

<!-- Sidebar -->
<div class="sidebar">
    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <x-navigation.sidebar-menu :items="[
                [
                    'title' => 'Dashboard',
                    'route' => 'user.dashboard',
                    'icon' => 'fas fa-tachometer-alt'
                ],
                [
                    'title' => 'Make Prediction',
                    'route' => 'user.predict',
                    'icon' => 'fas fa-calculator'
                ],
                [
                    'title' => 'Prediction History',
                    'route' => 'user.history',
                    'icon' => 'fas fa-history'
                ],
                [
                    'title' => 'Profile',
                    'route' => 'user.profile',
                    'icon' => 'fas fa-user'
                ],
                [
                    'title' => 'Security',
                    'route' => 'user.security',
                    'icon' => 'fas fa-shield-alt'
                ]
            ]" />
        </ul>
    </nav>
    <!-- /.sidebar-menu -->
    
    <!-- Logout Section -->
    <div class="logout-section">
        <ul class="nav nav-pills nav-sidebar flex-column">
            <li class="nav-item logout-btn">
                <a href="#" class="nav-link logout-link" data-bs-toggle="modal" data-bs-target="#logoutModal" data-toggle="modal" data-target="#logoutModal">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <p>Logout</p>
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- /.sidebar -->
