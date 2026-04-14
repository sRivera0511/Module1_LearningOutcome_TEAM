@extends('layouts.app')

@section('content')
    <div class="page-shell page-shell--narrow">
        <div id="estado-title" class="page-title page-title--compact">
            <h1 id="estado-t1">ACCESO DE</h1>
            <h1 id="estado-t2">PERSONAL</h1>
        </div>

        <p class="page-description">
            Inicia sesion con tu usuario interno para acceder al panel administrativo.
        </p>

        <section class="form-card">
            @if($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="stack-form">
                @csrf

                <div class="field floating-field floating-field--full">
                    <input
                        type="text"
                        id="login-username"
                        name="username"
                        value="{{ old('username') }}"
                        placeholder=" "
                        required
                        autocomplete="username"
                    >
                    <label for="login-username" class="floating-label">Usuario</label>
                </div>

                <div class="field floating-field floating-field--full">
                    <input
                        type="password"
                        id="login-password"
                        name="password"
                        placeholder=" "
                        required
                        autocomplete="current-password"
                    >
                    <label for="login-password" class="floating-label">Contrasena</label>
                </div>

                <button type="submit" class="button button-primary button-block">Iniciar sesion</button>
            </form>
        </section>
    </div>
@endsection
