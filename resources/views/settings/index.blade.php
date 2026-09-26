@extends('layouts.app', ['section' => 'Settings'])

@section('content')
    <section class="settings-intro">
        <span class="settings-intro-icon"><i class="bi bi-sliders2" aria-hidden="true"></i></span>
        <div>
            <p class="eyebrow">ACCOUNT &amp; WORKSPACE</p>
            <h2>Account and workspace access</h2>
            <p>Manage your account details, sign-in security, and team access.</p>
        </div>
    </section>

    <div class="settings-grid">
        <section class="surface-card settings-card">
            <div class="settings-card-top">
                <span class="settings-card-icon settings-icon-purple"><i class="bi bi-person-circle" aria-hidden="true"></i></span>
                <span class="settings-card-label">YOUR ACCOUNT</span>
            </div>
            <h3>Profile information</h3>
            <p>Keep your name, email address, phone number, and profile photo up to date.</p>
            <div class="settings-account-preview">
                <span class="settings-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
                <span><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></span>
            </div>
            <a class="btn btn-primary settings-action" href="{{ route('profile.edit') }}">Edit profile <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </section>

        <section class="surface-card settings-card">
            <div class="settings-card-top">
                <span class="settings-card-icon settings-icon-green"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                <span class="settings-card-label">SIGN-IN &amp; SECURITY</span>
            </div>
            <h3>Password and access</h3>
            <p>Change your password to keep your account secure. You will need your current password.</p>
            <div class="settings-security-note"><i class="bi bi-check-circle" aria-hidden="true"></i><span><strong>Account status</strong><small>{{ ucfirst($user->status) }} · {{ ucfirst($user->role) }} account</small></span></div>
            <a class="btn btn-light settings-action" href="{{ route('profile.password') }}">Change password <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </section>

        @if ($user->isAdmin())
            <section class="surface-card settings-card settings-team-card">
                <div class="settings-card-top">
                    <span class="settings-card-icon settings-icon-blue"><i class="bi bi-people" aria-hidden="true"></i></span>
                    <span class="settings-card-label">ADMINISTRATOR</span>
                </div>
                <h3>Team access</h3>
                <p>Manage team accounts, roles, and who can sign in to this workspace.</p>
                <div class="settings-team-stats" aria-label="Team account totals">
                    <div><strong>{{ number_format($teamStats->total_users) }}</strong><span>Total accounts</span></div>
                    <div><strong>{{ number_format($teamStats->active_users) }}</strong><span>Active</span></div>
                    <div><strong>{{ number_format($teamStats->inactive_users) }}</strong><span>Inactive</span></div>
                </div>
                <div class="settings-team-actions">
                    <a class="btn btn-primary settings-action" href="{{ route('users.index') }}">Manage users <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    <a class="btn btn-light settings-action" href="{{ route('users.create') }}">Add a user <i class="bi bi-person-plus" aria-hidden="true"></i></a>
                </div>
            </section>
        @endif
    </div>
@endsection
