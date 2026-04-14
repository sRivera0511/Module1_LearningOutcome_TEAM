<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index()
    {
        return view('public.index');
    }

    public function track(Request $request)
    {
        $request->validate([
            'customer_number' => 'required|numeric',
            'invoice_number' => 'required|numeric',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'numeric' => 'El campo :attribute debe ser numerico.',
        ], [
            'customer_number' => 'numero de cliente',
            'invoice_number' => 'numero de factura',
        ]);

        $order = Order::where('customer_number', $request->customer_number)
            ->where('invoice_number', $request->invoice_number)
            ->first();

        if (! $order) {
            return back()
                ->withInput()
                ->with('error', 'No se encontro ningun pedido con esos datos.');
        }

        return view('public.index', compact('order'));
    }
}
