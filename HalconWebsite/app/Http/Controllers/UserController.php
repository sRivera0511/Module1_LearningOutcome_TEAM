<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:Admin,Sales,Purchasing,Warehouse,Route',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'email' => 'El campo :attribute debe ser un correo valido.',
            'unique' => 'El :attribute ya esta registrado.',
            'min' => 'La :attribute debe tener al menos :min caracteres.',
            'in' => 'Selecciona un rol valido.',
        ], [
            'name' => 'nombre',
            'username' => 'nombre de usuario',
            'email' => 'correo electronico',
            'password' => 'contrasena',
            'role' => 'rol',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['active'] = true;

        User::create($validated);

        return back()->with('success', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:Admin,Sales,Purchasing,Warehouse,Route',
            'active' => 'required|boolean',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'boolean' => 'El campo :attribute debe ser valido.',
            'in' => 'Selecciona un rol valido.',
        ], [
            'name' => 'nombre',
            'role' => 'rol',
            'active' => 'estado',
        ]);

        $user->update($validated);

        return back()->with('success', 'Usuario actualizado.');
    }
}
