@extends('layouts.app', ['section' => 'User Details'])

@section('content')
    <section class="surface-card user-detail-card">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="status">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="user-detail-heading">
            @if ($user->profile_image)
                <img class="user-avatar user-avatar-large" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_image) }}" alt="">
            @else
                <span class="user-avatar user-avatar-fallback user-avatar-large">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
            @endif
            <div class="user-detail-title"><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p><div class="d-flex gap-2"><span @class(['badge role-badge', 'text-bg-primary' => $user->isAdmin(), 'text-bg-light text-dark' => $user->isUser()])>{{ ucfirst($user->role) }}</span><span @class(['badge', 'text-bg-success' => $user->isActive(), 'text-bg-secondary' => $user->isInactive()])>{{ ucfirst($user->status) }}</span></div></div>
            <div class="user-detail-actions"><a class="btn btn-light" href="{{ route('users.index') }}"><i class="bi bi-arrow-left me-1"></i> Back</a><a class="btn btn-primary" href="{{ route('users.edit', $user) }}"><i class="bi bi-pencil me-1"></i> Edit user</a></div>
        </div>
        <div class="detail-grid">
            <div class="detail-item"><span>Phone</span><strong>{{ $user->phone ?: '—' }}</strong></div>
            <div class="detail-item"><span>Last login</span><strong>{{ $user->last_login_at?->format('M j, Y g:i A') ?? 'Never' }}</strong></div>
            <div class="detail-item"><span>Account created</span><strong>{{ $user->created_at?->format('M j, Y g:i A') }}</strong></div>
            <div class="detail-item"><span>Last updated</span><strong>{{ $user->updated_at?->format('M j, Y g:i A') }}</strong></div>
        </div>
    </section>
@endsection
