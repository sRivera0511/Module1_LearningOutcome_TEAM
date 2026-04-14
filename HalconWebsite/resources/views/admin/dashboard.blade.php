@extends('layouts.app')

@section('content')
    <div class="page-shell">
        <div id="estado-title" class="page-title page-title--compact">
            <h1 id="estado-t1">PANEL DE</h1>
            <h1 id="estado-t2">CONTROL</h1>
        </div>

        <p class="page-description">
            Bienvenido, <strong>{{ auth()->user()->name }}</strong>. Estas en el departamento de
            <strong>{{ auth()->user()->role }}</strong>.
        </p>

        <div class="feature-grid">
            <a href="{{ route('orders.index') }}" class="feature-card feature-card--gold">
                <span class="feature-card__kicker">Modulo principal</span>
                <h2>Gestion de pedidos</h2>
                <p>Consulta pedidos activos, revisa estados y ejecuta las acciones que permite tu rol.</p>
                <span class="feature-card__cta">Entrar</span>
            </a>

            @if(in_array(auth()->user()->role, ['Admin', 'Sales'], true))
                <a href="{{ route('orders.archived') }}" class="feature-card feature-card--orange">
                    <span class="feature-card__kicker">Historial</span>
                    <h2>Pedidos archivados</h2>
                    <p>Consulta pedidos eliminados logicamente y restauralos cuando sea necesario.</p>
                    <span class="feature-card__cta">Ver archivo</span>
                </a>
            @endif

            @if(auth()->user()->role === 'Admin')
                <a href="{{ route('users.index') }}" class="feature-card feature-card--blue">
                    <span class="feature-card__kicker">Administracion</span>
                    <h2>Control de personal</h2>
                    <p>Registra usuarios, ajusta roles y activa o desactiva accesos internos.</p>
                    <span class="feature-card__cta">Gestionar</span>
                </a>
            @endif
        </div>

        <section class="status-card status-card--notice">
            <p class="result-message">
                <strong>Aviso del sistema:</strong>
                @if(auth()->user()->role === 'Warehouse')
                    Hay materiales pendientes por surtir. Revisa los pedidos marcados como En proceso.
                @elseif(auth()->user()->role === 'Route')
                    Recuerda subir evidencia cuando el pedido pase a En ruta o Entregado.
                @elseif(auth()->user()->role === 'Sales')
                    Puedes registrar pedidos nuevos y recuperar pedidos archivados.
                @else
                    El sistema Halcon Order Hub esta operativo.
                @endif
            </p>
        </section>
    </div>
@endsection
