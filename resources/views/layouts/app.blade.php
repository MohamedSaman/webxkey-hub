<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'WebXKey Server Manager' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
<div class="app-shell">

    {{-- Sidebar --}}
    <nav class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-mark">⬡ WebXKey</div>
            <div class="logo-sub">Server Manager</div>
        </div>

        <div class="nav-section">Main</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 16 16" fill="currentColor">
                <rect x="1" y="1" width="6" height="6" rx="1"/>
                <rect x="9" y="1" width="6" height="6" rx="1"/>
                <rect x="1" y="9" width="6" height="6" rx="1"/>
                <rect x="9" y="9" width="6" height="6" rx="1"/>
            </svg>
            Dashboard
        </a>

        <div class="nav-section">Sites</div>
        <a href="{{ route('applications') }}" class="nav-item {{ request()->routeIs('applications') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 16 16" fill="currentColor">
                <path d="M2 3h12v2H2zm0 4h12v2H2zm0 4h8v2H2z"/>
            </svg>
            Client Systems
        </a>
        <a href="{{ route('deploy') }}" class="nav-item {{ request()->routeIs('deploy') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 16 16" fill="currentColor">
                <path d="M8 1l7 4v6l-7 4L1 11V5z"/>
            </svg>
            Deploy New App
        </a>
        <a href="{{ route('billing') }}" class="nav-item {{ request()->routeIs('billing') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 16 16" fill="currentColor">
                <rect x="1" y="3" width="14" height="10" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.2"/>
                <path d="M1 6h14M5 10h2M9 10h2" stroke="currentColor" stroke-width="1.2" fill="none"/>
            </svg>
            Billing
        </a>

        <div class="nav-section">Clients</div>
        <a href="{{ route('clients') }}" class="nav-item {{ request()->routeIs('clients') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 16 16" fill="currentColor">
                <circle cx="8" cy="5" r="3"/>
                <path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6" fill="none" stroke="currentColor" stroke-width="1.2"/>
            </svg>
            Clients
        </a>
        <a href="{{ route('projects') }}" class="nav-item {{ request()->routeIs('projects*') || request()->routeIs('proposals*') || request()->routeIs('invoices*') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 16 16" fill="currentColor">
                <rect x="2" y="4" width="12" height="9" rx="1"/>
                <path d="M5 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1" fill="none" stroke="currentColor" stroke-width="1.2"/>
            </svg>
            Projects
        </a>

        <div class="nav-section">Server</div>
        <a href="{{ route('applications') }}" class="nav-item {{ request()->routeIs('deployments') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 16 16" fill="currentColor">
                <circle cx="8" cy="8" r="7"/><path d="M8 4v4l3 2" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
            Deployments
        </a>

        <div class="sidebar-footer">
            <div class="sidebar-footer-text">
                57.159.27.225<br>
                PHP 8.3 · Ubuntu 24
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin-top: 10px;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #6b6b68; font-size: 11px; cursor: pointer; padding: 0;">
                    Sign out
                </button>
            </form>
        </div>
    </nav>

    {{-- Main content --}}
    <div class="main-area">
        {{ $slot }}
    </div>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

</div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
        document.getElementById('sidebar-overlay').classList.toggle('open');
    }

    document.getElementById('sidebar-overlay').addEventListener('click', toggleSidebar);

    // Close sidebar on navigation if needed
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', () => {
            if (window.innerWidth <= 900) {
                toggleSidebar();
            }
        });
    });
</script>
@livewireScripts
</body>
</html>
