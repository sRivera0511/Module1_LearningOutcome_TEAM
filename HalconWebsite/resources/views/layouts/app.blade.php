<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halcon Order Hub</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Asap+Condensed:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <div class="site-header__inner">
            <h1 class="site-title">
                <a href="{{ route('home') }}">Halcon Order Hub</a>
            </h1>

            @guest
                <button type="button" class="button button-secondary" onclick="window.location.href='{{ route('login') }}'">
                    Acceso de personal
                </button>
            @else
                <div class="site-header__actions">
                    <nav class="site-nav">
                        <a href="{{ route('dashboard') }}">Panel</a>
                        <a href="{{ route('orders.index') }}">Pedidos</a>
                        @if(in_array(auth()->user()->role, ['Admin', 'Sales'], true))
                            <a href="{{ route('orders.archived') }}">Archivados</a>
                        @endif
                        @if(auth()->user()->role === 'Admin')
                            <a href="{{ route('users.index') }}">Usuarios</a>
                        @endif
                    </nav>

                    <div class="site-user">
                        <span>{{ auth()->user()->name }}</span>
                        <span class="site-user__role">{{ auth()->user()->role }}</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="button button-secondary">Cerrar sesion</button>
                    </form>
                </div>
            @endguest
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
