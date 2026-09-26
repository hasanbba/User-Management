@extends('layouts.app', ['section' => 'My Profile'])

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="status">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <section class="surface-card profile-card">
        <div class="profile-heading">
            @if ($user->profile_image)
                <img class="profile-avatar" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_image) }}" alt="Profile photo for {{ $user->name }}">
            @else
                <span class="profile-avatar profile-avatar-fallback" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
            @endif
            <div class="profile-heading-copy">
                <span class="eyebrow">ACCOUNT PROFILE</span>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>
                <div class="profile-badges">
                    <span class="badge {{ $user->isAdmin() ? 'text-bg-primary' : 'text-bg-light text-dark' }}">{{ ucfirst($user->role) }}</span>
                    <span class="badge {{ $user->isActive() ? 'text-bg-success' : 'text-bg-secondary' }}">{{ ucfirst($user->status) }}</span>
                </div>
            </div>
            <div class="profile-actions">
                <a class="btn btn-primary" href="{{ route('profile.edit') }}"><i class="bi bi-pencil me-1"></i> Edit profile</a>
                <a class="btn btn-light" href="{{ route('profile.password') }}"><i class="bi bi-key me-1"></i> Change password</a>
            </div>
        </div>

        <div class="profile-details">
            <div class="profile-detail"><span>Phone</span><strong>{{ $user->phone ?: 'Not provided' }}</strong></div>
            <div class="profile-detail"><span>Account created</span><strong>{{ $user->created_at?->format('M j, Y') ?? '—' }}</strong></div>
            <div class="profile-detail"><span>Last login</span><strong>{{ $user->last_login_at?->format('M j, Y g:i A') ?? 'Never' }}</strong></div>
            <div class="profile-detail"><span>Email verification</span><strong>{{ $user->email_verified_at ? 'Verified' : 'Not verified' }}</strong></div>
        </div>
    </section>
@endsection
