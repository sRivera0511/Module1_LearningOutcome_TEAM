@extends('layouts.app')

@section('content')
    @php
        $role = auth()->user()->role;
        $canCreate = in_array($role, ['Admin', 'Sales'], true);
        $canUpdate = in_array($role, ['Admin', 'Purchasing', 'Warehouse', 'Route'], true);
        $canArchive = in_array($role, ['Admin', 'Sales'], true);
        $allowPhotoUpload = $role === 'Route';
    @endphp

    <div class="page-shell">
        <div class="page-header">
            <div>
                <div id="estado-title" class="page-title page-title--compact">
                    <h1 id="estado-t1">GESTION DE</h1>
                    <h1 id="estado-t2">PEDIDOS</h1>
                </div>
                <p class="page-description">
                    Consulta los pedidos activos y actualiza el flujo de trabajo segun tu rol.
                </p>
            </div>

            @if($canArchive)
                <a href="{{ route('orders.archived') }}" class="button button-secondary">Ver archivados</a>
            @endif
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
                    <p class="record-kicker">Busqueda</p>
                    <h2>Filtrar pedidos</h2>
                </div>
            </div>

            <form action="{{ route('orders.index') }}" method="GET" class="stack-form">
                <div class="filter-grid">
                    <div class="field">
                        <label for="search" class="field-label">Factura, cliente o nombre</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Ej. 1001, Juan...">
                    </div>

                    <div class="field">
                        <label for="filter-status" class="field-label">Estado</label>
                        <select id="filter-status" name="status" class="form-select">
                            <option value="">Todos</option>
                            @foreach(\App\Models\Order::STATUSES as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="filter-date" class="field-label">Fecha de creacion</label>
                        <input type="date" id="filter-date" name="date" value="{{ request('date') }}">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="button button-primary">Buscar</button>
                    @if(request()->hasAny(['search', 'status', 'date']))
                        <a href="{{ route('orders.index') }}" class="button button-secondary">Limpiar filtros</a>
                    @endif
                </div>
            </form>
        </section>

        @if($canCreate)
            <section class="form-card">
                <div class="section-heading">
                    <div>
                        <p class="record-kicker">Registro</p>
                        <h2>Nuevo pedido</h2>
                    </div>
                    <span class="status-badge badge-ordered">{{ \App\Models\Order::STATUS_PEDIDO_RECIBIDO }}</span>
                </div>

                <form action="{{ route('orders.store') }}" method="POST" class="stack-form">
                    @csrf

                    <div class="form-grid">
                        <div class="field">
                            <label for="invoice_number" class="field-label">Numero de factura</label>
                            <input type="number" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" required>
                        </div>

                        <div class="field">
                            <label for="customer_number" class="field-label">Numero de cliente</label>
                            <input type="number" id="customer_number" name="customer_number" value="{{ old('customer_number') }}" required>
                        </div>

                        <div class="field">
                            <label for="customer_name" class="field-label">Nombre del cliente</label>
                            <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
                        </div>

                        <div class="field field--full">
                            <label for="fiscal_data" class="field-label">Datos fiscales</label>
                            <textarea id="fiscal_data" name="fiscal_data" rows="3" required>{{ old('fiscal_data') }}</textarea>
                        </div>

                        <div class="field field--full">
                            <label for="delivery_address" class="field-label">Direccion de entrega</label>
                            <textarea id="delivery_address" name="delivery_address" rows="3" required>{{ old('delivery_address') }}</textarea>
                        </div>

                        <div class="field field--full">
                            <label for="notes" class="field-label">Notas</label>
                            <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="button button-primary">Registrar pedido</button>
                </form>
            </section>
        @endif

        <section class="collection-card">
            <div class="section-heading">
                <div>
                    <p class="record-kicker">Operacion diaria</p>
                    <h2>Pedidos activos</h2>
                </div>
                <span class="section-count">{{ $orders->count() }} registros</span>
            </div>

            @if($orders->isEmpty())
                <div class="empty-state">
                    No hay pedidos activos por mostrar.
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
                                    <dt>Direccion</dt>
                                    <dd>{{ $order->delivery_address }}</dd>
                                </div>
                                <div>
                                    <dt>Notas</dt>
                                    <dd>{{ $order->notes ?: 'Sin notas' }}</dd>
                                </div>
                            </dl>

                            @if(in_array($order->status, \App\Models\Order::evidenceStatuses()))
                                <div class="evidence-gallery">
                                    @if($order->route_photo && $order->status === \App\Models\Order::STATUS_EN_RUTA)
                                        <div class="evidence-item">
                                            <p class="evidence-label">Evidencia de ruta</p>
                                            <img
                                                src="{{ asset('storage/' . $order->route_photo) }}"
                                                alt="Evidencia de ruta"
                                                class="evidence-thumbnail"
                                                onclick="window.open(this.src, '_blank')"
                                                style="cursor: pointer;"
                                            >
                                        </div>
                                    @elseif($order->delivery_photo && $order->status === \App\Models\Order::STATUS_ENTREGADO)
                                        <div class="evidence-item">
                                            <p class="evidence-label">Evidencia de entrega</p>
                                            <img
                                                src="{{ asset('storage/' . $order->delivery_photo) }}"
                                                alt="Evidencia de entrega"
                                                class="evidence-thumbnail"
                                                onclick="window.open(this.src, '_blank')"
                                                style="cursor: pointer;"
                                            >
                                        </div>
                                    @endif

                                    @if($order->route_photo && $order->delivery_photo)
                                        <div class="evidence-item">
                                            <p class="evidence-label">Evidencia de entrega</p>
                                            <img
                                                src="{{ asset('storage/' . $order->delivery_photo) }}"
                                                alt="Evidencia de entrega"
                                                class="evidence-thumbnail"
                                                onclick="window.open(this.src, '_blank')"
                                                style="cursor: pointer;"
                                            >
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="record-actions">
                                @if($canUpdate)
                                    @include('admin.orders.edit', ['order' => $order, 'allowPhotoUpload' => $allowPhotoUpload])
                                @endif

                                @if($canArchive)
                                    <form action="{{ route('orders.destroy', $order) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button button-danger">Archivar pedido</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    @if($allowPhotoUpload)
        <script>
            document.querySelectorAll('[data-photo-control]').forEach(function (select) {
                var form = select.closest('form');
                var photoField = form ? form.querySelector('[data-photo-target]') : null;

                if (!photoField) {
                    return;
                }

                var togglePhotoField = function () {
                    var shouldShow = ['En ruta', 'Entregado'].includes(select.value);
                    photoField.style.display = shouldShow ? 'block' : 'none';
                };

                togglePhotoField();
                select.addEventListener('change', togglePhotoField);
            });
        </script>
    @endif
@endsection
