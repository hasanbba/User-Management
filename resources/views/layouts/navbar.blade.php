<header class="topbar">
    <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-controls="appSidebar" aria-expanded="false" data-sidebar-toggle><i class="bi bi-list"></i></button>
    <div class="topbar-context"><span class="context-dot"></span><span>Management workspace</span></div>
    <div class="topbar-actions">
        <button class="icon-button" type="button" aria-label="Notifications"><i class="bi bi-bell"></i></button>
        <span class="topbar-divider"></span>
        <a class="user-summary topbar-profile-link" href="{{ route('profile.index') }}" aria-label="View your profile">
            @if (auth()->user()->profile_image)
                <img class="avatar topbar-avatar-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile_image) }}" alt="">
            @else
                <span class="avatar"><i class="bi bi-person-fill"></i></span>
            @endif
            <span class="user-summary-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->role) }}</small></span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button class="logout-button" type="submit"><i class="bi bi-box-arrow-right"></i><span>Sign out</span></button>
        </form>
    </div>
</header>
