@extends('layouts.guest', ['title' => 'Sign in'])

@section('content')
    <main class="auth-shell">
        <section class="auth-brand-panel" aria-labelledby="welcome-title">
            <a class="auth-brand" href="{{ route('login') }}" aria-label="{{ config('app.name') }} sign in">
                <span class="brand-mark"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i></span>
                <span class="brand-copy"><strong>{{ config('app.name') }}</strong><small>PEOPLE &amp; WORKSPACE</small></span>
            </a>

            <div class="auth-brand-message">
                <span class="welcome-kicker"><i class="bi bi-sparkle me-2" aria-hidden="true"></i>YOUR TEAM, IN SYNC</span>
                <h1 id="welcome-title">Good work<br>starts <span>together.</span></h1>
                <p>One clear space to manage your people, keep details up to date, and move work forward.</p>
            </div>

            <div class="auth-brand-footer"><span class="auth-footer-dot" aria-hidden="true"></span> A calmer way to keep your team connected</div>
            <div class="auth-brand-orbit auth-brand-orbit-one" aria-hidden="true"></div>
            <div class="auth-brand-orbit auth-brand-orbit-two" aria-hidden="true"></div>
            <div class="auth-brand-card" aria-hidden="true"><i class="bi bi-check2-circle"></i><span><strong>People in one place</strong><small>Ready when you are</small></span></div>
        </section>

        <section class="auth-form-panel" aria-labelledby="login-title">
            <div class="auth-form-wrap">
                <div class="auth-mobile-brand"><span class="brand-mark"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i></span><strong>{{ config('app.name') }}</strong></div>
                <div class="auth-heading">
                    <p class="eyebrow">WELCOME BACK</p>
                    <h2 id="login-title">Sign in to your account</h2>
                    <p>Use your work email and password to continue.</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success auth-alert" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger auth-alert" role="alert"><i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="login-form">
                    @csrf
                    <div class="auth-field">
                        <label class="form-label" for="email">Email address</label>
                        <div class="input-with-icon">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="username" inputmode="email" required autofocus>
                        </div>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="auth-field">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-with-icon password-field">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter your password" autocomplete="current-password" required>
                            <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false" data-password-toggle><i class="bi bi-eye" aria-hidden="true"></i></button>
                        </div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="login-options">
                        <label class="remember-option" for="remember">
                            <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                            <span>Keep me signed in</span>
                        </label>
                    </div>
                    <button class="btn btn-primary login-submit" type="submit"><span>Sign in</span><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </form>

                <p class="auth-footnote"><i class="bi bi-shield-check me-2" aria-hidden="true"></i>Your account is protected with secure sign-in.</p>
            </div>
            <div class="auth-copyright">&copy; {{ date('Y') }} {{ config('app.name') }} <span>•</span> Team workspace</div>
        </section>
    </main>
    <script>
        document.querySelector('[data-password-toggle]')?.addEventListener('click', function () {
            const password = document.getElementById('password');
            const showing = password.type === 'password';
            password.type = showing ? 'text' : 'password';
            this.setAttribute('aria-pressed', String(showing));
            this.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
            this.querySelector('i').className = showing ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
@endsection
