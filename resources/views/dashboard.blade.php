@extends('layouts.app')

@section('content')
    <section class="welcome-panel">
        <div class="welcome-copy">
            <span class="welcome-kicker"><i class="bi bi-stars me-1"></i> YOUR WORKSPACE</span>
            <h2>A clearer view of your team.</h2>
            <p>{{ auth()->user()->isAdmin() ? 'Your user management workspace is ready. Review your team and account activity here.' : 'Your workspace is ready. Review your account and personal settings here.' }}</p>
            @can('manage-users')
                <a class="btn btn-light welcome-button" href="{{ route('users.index') }}">Explore users <i class="bi bi-arrow-right ms-2"></i></a>
            @endcan
        </div>
        <div class="welcome-art" aria-hidden="true"><div class="art-orbit orbit-one"></div><div class="art-orbit orbit-two"></div><div class="art-card"><i class="bi bi-people-fill"></i><span class="art-line line-wide"></span><span class="art-line"></span><span class="art-line line-short"></span><div class="art-badge"><i class="bi bi-check-lg"></i></div></div></div>
    </section>

    @can('manage-users')
        <section class="stats-grid dashboard-stats-grid" aria-label="User statistics">
            <article class="stat-card"><div class="stat-top"><span>Total users</span><span class="stat-icon icon-violet"><i class="bi bi-people"></i></span></div><div class="stat-value">{{ number_format($stats->total_users) }}</div><div class="stat-note">All registered accounts</div></article>
            <article class="stat-card"><div class="stat-top"><span>Active users</span><span class="stat-icon icon-green"><i class="bi bi-person-check"></i></span></div><div class="stat-value">{{ number_format($stats->active_users) }}</div><div class="stat-note">Accounts that can sign in</div></article>
            <article class="stat-card"><div class="stat-top"><span>Inactive users</span><span class="stat-icon icon-amber"><i class="bi bi-person-slash"></i></span></div><div class="stat-value">{{ number_format($stats->inactive_users) }}</div><div class="stat-note">Accounts currently disabled</div></article>
            <article class="stat-card"><div class="stat-top"><span>Administrators</span><span class="stat-icon icon-blue"><i class="bi bi-shield-check"></i></span></div><div class="stat-value">{{ number_format($stats->admin_users) }}</div><div class="stat-note">Admin accounts</div></article>
            <article class="stat-card"><div class="stat-top"><span>Regular users</span><span class="stat-icon icon-violet"><i class="bi bi-person"></i></span></div><div class="stat-value">{{ number_format($stats->regular_users) }}</div><div class="stat-note">Standard accounts</div></article>
        </section>
    @endcan

    <section class="dashboard-lower {{ auth()->user()->isAdmin() ? '' : 'dashboard-lower-single' }}">
        @can('manage-users')
            <article class="surface-card activity-card dashboard-recent-card">
                <div class="card-heading"><div><h3>Recent users</h3><p>The latest accounts added to your team.</p></div><a class="text-link" href="{{ route('users.index') }}">View directory <i class="bi bi-arrow-up-right ms-1"></i></a></div>
                @if ($recentUsers->isEmpty())
                    <div class="empty-state"><span class="empty-icon"><i class="bi bi-person-lines-fill"></i></span><strong>No users found.</strong><p>New accounts will appear here.</p></div>
                @else
                    <div class="table-responsive dashboard-recent-wrap">
                        <table class="table align-middle dashboard-recent-table">
                            <thead><tr><th>Person</th><th>Role</th><th>Status</th><th>Created</th></tr></thead>
                            <tbody>
                                @foreach ($recentUsers as $recentUser)
                                    <tr>
                                        <td><a class="dashboard-user-link" href="{{ route('users.show', $recentUser) }}">
                                            @if ($recentUser->profile_image)
                                                <img class="user-avatar" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($recentUser->profile_image) }}" alt="">
                                            @else
                                                <span class="user-avatar user-avatar-fallback">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($recentUser->name, 0, 1)) }}</span>
                                            @endif
                                            <span class="user-cell-copy"><strong>{{ $recentUser->name }}</strong><small>{{ $recentUser->email }}</small></span>
                                        </a></td>
                                        <td><span @class(['badge role-badge', 'text-bg-primary' => $recentUser->isAdmin(), 'text-bg-light text-dark' => $recentUser->isUser()])>{{ ucfirst($recentUser->role) }}</span></td>
                                        <td><span @class(['badge', 'text-bg-success' => $recentUser->isActive(), 'text-bg-secondary' => $recentUser->isInactive()])>{{ ucfirst($recentUser->status) }}</span></td>
                                        <td>{{ $recentUser->created_at?->format('M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        @endcan

        <article class="surface-card quick-card">
            <div class="card-heading"><div><h3>Quick access</h3><p>Jump to a workspace area.</p></div></div>
            @can('manage-users')
                <a class="quick-link" href="{{ route('users.create') }}"><span class="quick-link-icon"><i class="bi bi-person-plus"></i></span><span><strong>Add a user</strong><small>Create a team account</small></span><i class="bi bi-arrow-up-right ms-auto"></i></a>
            @endcan
            <a class="quick-link" href="{{ route('profile.index') }}"><span class="quick-link-icon quick-link-icon-blue"><i class="bi bi-person-circle"></i></span><span><strong>My profile</strong><small>View account details</small></span><i class="bi bi-arrow-up-right ms-auto"></i></a>
            <a class="quick-link" href="{{ route('settings.index') }}"><span class="quick-link-icon quick-link-icon-amber"><i class="bi bi-sliders"></i></span><span><strong>Settings</strong><small>Workspace preferences</small></span><i class="bi bi-arrow-up-right ms-auto"></i></a>
        </article>
    </section>
@endsection
