<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $section ?? 'Dashboard' }} · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
</head>
<body>
    <div class="app-shell">
        @include('layouts.sidebar')
        <div class="app-main">
            @include('layouts.navbar')
            <main class="content-area">
                <div class="content-heading">
                    <div>
                        <p class="eyebrow mb-1">WORKSPACE</p>
                        <h1>{{ $section ?? 'Dashboard' }}</h1>
                    </div>
                    <span class="date-chip"><i class="bi bi-calendar3 me-2"></i>{{ now()->format('D, M j, Y') }}</span>
                </div>
                @yield('content')
            </main>
            @include('layouts.footer')
        </div>
    </div>
    <div class="sidebar-backdrop" data-sidebar-close></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
