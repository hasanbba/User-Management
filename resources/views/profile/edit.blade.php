@extends('layouts.app', ['section' => 'Edit Profile'])

@section('content')
    <section class="surface-card user-form-card profile-form-card">
        <div class="form-card-heading"><h3>Edit profile</h3><p>Update your personal details and profile photo.</p></div>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="user-form">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6"><x-form.input name="name" label="Full name" :value="$user->name" required autocomplete="name" /></div>
                <div class="col-md-6"><x-form.input name="email" label="Email address" type="email" :value="$user->email" required autocomplete="email" /></div>
                <div class="col-md-6"><x-form.input name="phone" label="Phone number" type="tel" :value="$user->phone" autocomplete="tel" /></div>
                <div class="col-md-6">
                    <label class="form-label" for="profile_image">Profile image</label>
                    <input id="profile_image" name="profile_image" type="file" accept=".jpg,.jpeg,.png,.webp" @class(['form-control', 'is-invalid' => $errors->has('profile_image')])>
                    <div class="form-text">JPG, PNG, or WebP. Maximum 2 MB.</div>
                    @error('profile_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @if ($user->profile_image)
                        <div class="current-image"><img class="user-avatar" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_image) }}" alt=""><span>Current image</span></div>
                    @endif
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-light" href="{{ route('profile.index') }}">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i> Save profile</button>
            </div>
        </form>
    </section>
@endsection
