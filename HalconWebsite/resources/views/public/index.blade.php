@extends('layouts.app')

@section('content')
    <div class="page-shell page-shell--narrow">
        <div id="estado-title" class="page-title">
            <h1 id="estado-t1">ESTADO DE</h1>
            <h1 id="estado-t2">TU PEDIDO</h1>
        </div>

        <p id="estado-descripcion" class="page-description">
            Ingresa tu numero de cliente y tu numero de factura para consultar el avance de tu pedido.
        </p>

        <section class="form-card">
            @if($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('track') }}" method="POST" class="stack-form">
                @csrf

                <div class="inputs-row">
                    <div class="field floating-field">
                        <input
                            type="number"
                            id="customer-number"
                            name="customer_number"
                            value="{{ old('customer_number') }}"
                            placeholder=" "
                            required
                        >
                        <label for="customer-number" class="floating-label">Numero de cliente</label>
                    </div>

                    <div class="field floating-field">
                        <input
                            type="number"
                            id="invoice-number"
                            name="invoice_number"
                            value="{{ old('invoice_number') }}"
                            placeholder=" "
                            required
                        >
                        <label for="invoice-number" class="floating-label">Numero de factura</label>
                    </div>
                </div>

                <button type="submit" class="button button-primary button-block">Consultar estado</button>
            </form>
        </section>

        @isset($order)
            <div id="result" class="result-area">
                <section class="status-card">
                    <div class="record-header">
                        <div>
                            <p class="record-kicker">Seguimiento publico</p>
                            <h2 class="status-title">Pedido #{{ $order->invoice_number }}</h2>
                        </div>

                        <span class="status-badge {{ $order->status_badge_class }}">{{ $order->status }}</span>
                    </div>

                    <dl class="meta-list">
                        <div>
                            <dt>Cliente</dt>
                            <dd>{{ $order->customer_name }}</dd>
                        </div>
                        <div>
                            <dt>Numero de cliente</dt>
                            <dd>{{ $order->customer_number }}</dd>
                        </div>
                        <div>
                            <dt>Direccion de entrega</dt>
                            <dd>{{ $order->delivery_address }}</dd>
                        </div>
                    </dl>

                    @if($order->status === \App\Models\Order::STATUS_ENTREGADO && $order->delivery_photo)
                        <div class="photo-block">
                            <p class="photo-block__title">Evidencia de entrega</p>
                            <img src="{{ asset('storage/' . $order->delivery_photo) }}" alt="Evidencia de entrega" class="status-photo">
                        </div>
                    @endif
                </section>
            </div>
        @endisset
    </div>
@endsection
