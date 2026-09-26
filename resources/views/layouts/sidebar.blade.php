<aside class="sidebar" id="appSidebar" aria-label="Main navigation">
    <a class="brand" href="{{ route('dashboard') }}">
        <span class="brand-mark"><i class="bi bi-grid-1x2-fill"></i></span>
        <span class="brand-copy"><strong>{{ config('app.name') }}</strong><small>ADMIN CONSOLE</small></span>
    </a>
    <div class="nav-caption">MENU</div>
    <nav class="side-nav">
        <a class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
        @can('manage-users')
            <a class="side-link {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-people"></i><span>Users</span></a>
            <a class="side-link {{ request()->routeIs('users.create') ? 'active' : '' }}" href="{{ route('users.create') }}"><i class="bi bi-person-plus"></i><span>Add user</span></a>
        @endcan
        <div class="nav-caption nav-caption-spaced">ACCOUNT</div>
        <a class="side-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.index') }}"><i class="bi bi-person-circle"></i><span>My profile</span></a>
        <a class="side-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i class="bi bi-gear"></i><span>Settings</span></a>
    </nav>
    <div class="sidebar-bottom">
        <div class="sidebar-help-icon"><i class="bi bi-shield-check"></i></div>
        <div><strong>Signed in</strong><small>{{ ucfirst(auth()->user()->role) }} account</small></div>
        <span class="status-dot"></span>
    </div>
</aside>
