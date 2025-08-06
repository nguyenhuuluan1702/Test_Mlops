{{-- Component: Admin Sidebar Menu --}}
<x-navigation.sidebar-menu :items="[
    [
        'title' => 'Dashboard',
        'route' => 'admin.dashboard',
        'icon' => 'fas fa-tachometer-alt'
    ],
    [
        'title' => 'Make Prediction',
        'route' => 'admin.predict',
        'icon' => 'fas fa-calculator'
    ],
    [
        'title' => 'Prediction History',
        'route' => 'admin.history',
        'icon' => 'fas fa-history'
    ],
    [
        'title' => 'User Management',
        'route' => 'admin.users',
        'icon' => 'fas fa-users'
    ],
    [
        'title' => 'Model Management',
        'route' => 'admin.models',
        'icon' => 'fas fa-brain'
    ]    
]" />
