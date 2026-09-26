@extends('layouts.app', ['section' => 'Change Password'])

@section('content')
    <section class="surface-card user-form-card profile-form-card">
        <div class="form-card-heading"><h3>Change password</h3><p>Confirm your current password before choosing a new one.</p></div>
        <form method="POST" action="{{ route('profile.password.update') }}" class="user-form">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" @class(['form-control', 'is-invalid' => $errors->has('current_password')]) required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6"><x-form.input name="password" label="New password" type="password" required autocomplete="new-password" /></div>
                <div class="col-md-6"><x-form.input name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password" /></div>
            </div>
            <p class="form-text">Use at least 8 characters, including letters and numbers.</p>
            <div class="form-actions">
                <a class="btn btn-light" href="{{ route('profile.index') }}">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-key me-1"></i> Update password</button>
            </div>
        </form>
    </section>
@endsection
