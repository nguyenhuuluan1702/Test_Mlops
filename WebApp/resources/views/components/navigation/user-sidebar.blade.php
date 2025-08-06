{{-- Component: User Sidebar Menu --}}
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
