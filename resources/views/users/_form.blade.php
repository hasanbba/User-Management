@php($editing = $user !== null)

<form method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}" enctype="multipart/form-data" class="user-form">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-4">
            <x-form.input name="name" label="Full name" :value="$user?->name" required autocomplete="name" />
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <x-form.input name="email" label="Email address" type="email" :value="$user?->email" required autocomplete="email" />
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <x-form.input name="phone" label="Phone number" type="tel" :value="$user?->phone" autocomplete="tel" />
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <label class="form-label" for="profile_image">Profile image</label>
            <input id="profile_image" name="profile_image" type="file" accept=".jpg,.jpeg,.png,.webp" @class(['form-control', 'is-invalid' => $errors->has('profile_image')])>
            <div class="form-text">JPG, PNG, or WebP. Maximum 2 MB.</div>
            @error('profile_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @if ($user?->profile_image)
                <div class="current-image"><img class="user-avatar" src="{{ asset('storage/'.$user->profile_image) }}" alt=""><span>Current profile image</span></div>
            @endif
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <x-form.select name="role" label="Role" :selected="$user?->role ?? 'user'" :options="['admin' => 'Admin', 'user' => 'User']" required />
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <x-form.select name="status" label="Account status" :selected="$user?->status ?? 'active'" :options="['active' => 'Active', 'inactive' => 'Inactive']" required />
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <label class="form-label" for="password">{{ $editing ? 'New password' : 'Password' }}</label>
            <input id="password" name="password" type="password" autocomplete="new-password" @class(['form-control', 'is-invalid' => $errors->has('password')]) @required(! $editing)>
            @if ($editing)
                <div class="form-text">Leave blank to keep the current password.</div>
            @else
                <div class="form-text">At least 8 characters, with letters and numbers.</div>
            @endif
            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <label class="form-label" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @class(['form-control', 'is-invalid' => $errors->has('password_confirmation')]) @required(! $editing)>
            @error('password_confirmation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn-light" href="{{ $editing ? route('users.show', $user) : route('users.index') }}">Cancel</a>
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i> {{ $editing ? 'Save changes' : 'Create user' }}</button>
    </div>
</form>
