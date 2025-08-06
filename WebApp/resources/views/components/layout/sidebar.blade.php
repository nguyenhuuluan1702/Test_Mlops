{{-- Component: Complete Layout Sidebar --}}
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="{{ asset('images/ml.ico') }}" alt="ML Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">ML Prediction</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @yield('sidebar')
                
                <!-- Logout option at bottom of sidebar -->
                <li class="nav-item mt-3" style="padding: 0 10px;">
                    <button type="button" 
                            class="btn btn-danger w-100 d-flex align-items-center text-left" 
                            style="background-color: #dc3545 !important; color: white !important; border: none !important; padding: 10px 15px !important; border-radius: 5px !important; text-align: left !important; justify-content: flex-start !important;" 
                            data-bs-toggle="modal" 
                            data-bs-target="#logoutModal"
                            onmouseover="this.style.backgroundColor='#c82333 !important'"
                            onmouseout="this.style.backgroundColor='#dc3545 !important'">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        <span>Logout</span>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</aside>
