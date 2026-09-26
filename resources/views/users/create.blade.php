@extends('layouts.app', ['section' => 'Add User'])

@section('content')
    <section class="surface-card user-form-card">
        <div class="card-heading form-card-heading"><div><h3>Create user</h3><p>Enter the account details and initial access settings.</p></div></div>
        @include('users._form', ['user' => null])
    </section>
@endsection
