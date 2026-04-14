@extends('layouts.app')

@section('content')
    <div class="page-shell">
        <div class="page-header">
            <div>
                <div id="estado-title" class="page-title page-title--compact">
                    <h1 id="estado-t1">PEDIDOS</h1>
                    <h1 id="estado-t2">ARCHIVADOS</h1>
                </div>
                <p class="page-description">
                    Consulta pedidos eliminados logicamente y restauralos cuando vuelvan a requerirse.
                </p>
            </div>

            <a href="{{ route('orders.index') }}" class="button button-secondary">Volver a pedidos</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <section class="collection-card">
            <div class="section-heading">
                <div>
                    <p class="record-kicker">Historial</p>
                    <h2>Archivo de pedidos</h2>
                </div>
                <span class="section-count">{{ $orders->count() }} registros</span>
            </div>

            @if($orders->isEmpty())
                <div class="empty-state">
                    No hay pedidos archivados en este momento.
                </div>
            @else
                <div class="record-grid">
                    @foreach($orders as $order)
                        <article class="record-card">
                            <div class="record-header">
                                <div>
                                    <p class="record-kicker">Factura #{{ $order->invoice_number }}</p>
                                    <h3>{{ $order->customer_name }}</h3>
                                </div>

                                <span class="status-badge {{ $order->status_badge_class }}">{{ $order->status }}</span>
                            </div>

                            <dl class="meta-list">
                                <div>
                                    <dt>Numero de cliente</dt>
                                    <dd>{{ $order->customer_number }}</dd>
                                </div>
                                <div>
                                    <dt>Creado por</dt>
                                    <dd>{{ $order->user?->name ?? 'Sin asignar' }}</dd>
                                </div>
                                <div>
                                    <dt>Archivado el</dt>
                                    <dd>{{ optional($order->deleted_at)->format('d/m/Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt>Direccion</dt>
                                    <dd>{{ $order->delivery_address }}</dd>
                                </div>
                            </dl>

                            <form action="{{ route('orders.restore', $order->id) }}" method="POST" class="inline-form">
                                @csrf
                                <button type="submit" class="button button-primary">Restaurar pedido</button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
