@extends('layouts.app', ['section' => 'Edit User'])

@section('content')
    <section class="surface-card user-form-card">
        <div class="card-heading form-card-heading"><div><h3>Edit {{ $user->name }}</h3><p>Update account details, role, or status.</p></div><a class="text-link" href="{{ route('users.show', $user) }}"><i class="bi bi-arrow-left me-1"></i> Back to details</a></div>
        @include('users._form', ['user' => $user])
    </section>
@endsection
