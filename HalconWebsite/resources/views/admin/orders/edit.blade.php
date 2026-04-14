@php
    $selectedStatus = old('status', $order->status);
    $allowPhotoUpload = $allowPhotoUpload ?? false;
@endphp

<form action="{{ route('orders.update', $order->id) }}" method="POST" enctype="multipart/form-data" class="stack-form stack-form--compact">
    @csrf
    @method('PUT')

    <div class="field">
        <label for="status-{{ $order->id }}" class="field-label">Actualizar estado</label>
        <select id="status-{{ $order->id }}" name="status" class="form-select" data-photo-control>
            @foreach (\App\Models\Order::STATUSES as $status)
                <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>

    @if($allowPhotoUpload)
        <div
            class="field"
            data-photo-target
            @if(! in_array($selectedStatus, \App\Models\Order::evidenceStatuses(), true)) style="display: none;" @endif
        >
            <label for="photo-{{ $order->id }}" class="field-label">Subir evidencia</label>
            <input type="file" id="photo-{{ $order->id }}" name="photo" accept="image/*">
            <p class="field-help">Disponible solo para pedidos en ruta o entregados.</p>
        </div>
    @endif

    <button type="submit" class="button button-primary">Guardar cambios</button>
</form>
