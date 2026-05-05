<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', $search)
                  ->orWhere('customer_number', $search)
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        $orders = $query->latest()->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|numeric|unique:orders',
            'customer_name' => 'required|string',
            'customer_number' => 'required|numeric',
            'fiscal_data' => 'required|string',
            'delivery_address' => 'required|string',
            'notes' => 'nullable|string',
        ], $this->messages(), $this->attributes());

        $validated['user_id'] = auth()->id();
        $validated['status'] = Order::STATUS_PEDIDO_RECIBIDO;

        Order::create($validated);

        return back()->with('success', 'Pedido registrado con exito.');
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'photo' => 'nullable|image|max:2048',
        ], [
            ...$this->messages(),
            'photo.image' => 'La evidencia debe ser una imagen valida.',
            'photo.max' => 'La evidencia no puede exceder 2 MB.',
        ], $this->attributes());

        $order->status = $validated['status'];

        if ($request->hasFile('photo')) {
            if ($request->user()->role !== 'Route') {
                abort(403);
            }

            if (! in_array($validated['status'], Order::evidenceStatuses(), true)) {
                throw ValidationException::withMessages([
                    'photo' => 'Solo puedes subir evidencia al marcar un pedido como En ruta o Entregado.',
                ]);
            }

            $path = $request->file('photo')->store('evidences', 'public');

            if ($validated['status'] === Order::STATUS_EN_RUTA) {
                $order->route_photo = $path;
            } elseif ($validated['status'] === Order::STATUS_ENTREGADO) {
                $order->delivery_photo = $path;
            }
        }

        $order->save();

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return back()->with('success', 'Pedido archivado correctamente.');
    }

    public function archived()
    {
        $orders = Order::onlyTrashed()
            ->with('user')
            ->latest('deleted_at')
            ->get();

        return view('admin.orders.archived', compact('orders'));
    }

    public function restore($id)
    {
        $order = Order::withTrashed()->findOrFail($id);
        $order->restore();

        return back()->with('success', 'Pedido restaurado.');
    }

    private function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'numeric' => 'El campo :attribute debe ser numerico.',
            'string' => 'El campo :attribute debe ser texto.',
            'unique' => 'El :attribute ya esta registrado.',
        ];
    }

    private function attributes(): array
    {
        return [
            'invoice_number' => 'numero de factura',
            'customer_name' => 'nombre del cliente',
            'customer_number' => 'numero de cliente',
            'fiscal_data' => 'datos fiscales',
            'delivery_address' => 'direccion de entrega',
            'notes' => 'notas',
            'status' => 'estado',
            'photo' => 'evidencia',
        ];
    }
}
