@extends('layouts.app')

@section('content')
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div id="estado-title" class="page-title page-title--compact">
                    <h1 id="estado-t1">CONTROL DE</h1>
                    <h1 id="estado-t2">USUARIOS</h1>
                </div>
                <p class="page-description">
                    Administra al personal interno, sus credenciales y el estado de acceso a la plataforma.
                </p>
            </div>

            <a href="{{ route('dashboard') }}" class="button button-secondary">Volver al panel</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="form-card">
            <div class="section-heading">
                <div>
                    <p class="record-kicker">Alta de personal</p>
                    <h2>Registrar usuario</h2>
                </div>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="stack-form">
                @csrf

                <div class="form-grid">
                    <div class="field">
                        <label for="name" class="field-label">Nombre completo</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="field">
                        <label for="username" class="field-label">Nombre de usuario</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required>
                    </div>

                    <div class="field">
                        <label for="email" class="field-label">Correo electronico</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="field">
                        <label for="password" class="field-label">Contrasena</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="field">
                        <label for="role" class="field-label">Rol</label>
                        <select id="role" name="role" class="form-select" required>
                            @foreach(['Admin', 'Sales', 'Purchasing', 'Warehouse', 'Route'] as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="button button-primary">Crear usuario</button>
            </form>
        </section>

        <section class="collection-card">
            <div class="section-heading">
                <div>
                    <p class="record-kicker">Listado interno</p>
                    <h2>Usuarios registrados</h2>
                </div>
                <span class="section-count">{{ $users->count() }} registros</span>
            </div>

            @if($users->isEmpty())
                <div class="empty-state">
                    No hay usuarios registrados.
                </div>
            @else
                <div class="record-grid">
                    @foreach($users as $user)
                        <article class="record-card">
                            <div class="record-header">
                                <div>
                                    <p class="record-kicker">{{ $user->username }}</p>
                                    <h3>{{ $user->name }}</h3>
                                </div>

                                <span class="status-badge {{ $user->active ? 'badge-delivered' : 'badge-default' }}">
                                    {{ $user->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <dl class="meta-list">
                                <div>
                                    <dt>Correo</dt>
                                    <dd>{{ $user->email }}</dd>
                                </div>
                                <div>
                                    <dt>Rol actual</dt>
                                    <dd>{{ $user->role }}</dd>
                                </div>
                            </dl>

                            <form action="{{ route('users.update', $user) }}" method="POST" class="stack-form stack-form--compact">
                                @csrf
                                @method('PUT')

                                <div class="field">
                                    <label for="edit-name-{{ $user->id }}" class="field-label">Nombre</label>
                                    <input type="text" id="edit-name-{{ $user->id }}" name="name" value="{{ $user->name }}" required>
                                </div>

                                <div class="field">
                                    <label for="edit-role-{{ $user->id }}" class="field-label">Rol</label>
                                    <select id="edit-role-{{ $user->id }}" name="role" class="form-select" required>
                                        @foreach(['Admin', 'Sales', 'Purchasing', 'Warehouse', 'Route'] as $role)
                                            <option value="{{ $role }}" @selected($user->role === $role)>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="edit-active-{{ $user->id }}" class="field-label">Estado</label>
                                    <select id="edit-active-{{ $user->id }}" name="active" class="form-select" required>
                                        <option value="1" @selected($user->active)>Activo</option>
                                        <option value="0" @selected(! $user->active)>Inactivo</option>
                                    </select>
                                </div>

                                <button type="submit" class="button button-primary">Guardar usuario</button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
