<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::post('/track', [PublicController::class, 'track'])->name('track');

Route::get('/login', function () {
    return view('api.auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required'],
        'password' => ['required'],
    ], [
        'username.required' => 'El usuario es obligatorio.',
        'password.required' => 'La contrasena es obligatoria.',
    ]);

    if (Auth::attempt([
        'username' => $credentials['username'],
        'password' => $credentials['password'],
        'active' => 1,
    ])) {
        $request->session()->regenerate();

        return redirect()->intended('dashboard');
    }

    return back()
        ->withInput($request->only('username'))
        ->withErrors(['username' => 'Credenciales incorrectas o usuario inactivo.']);
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

    Route::middleware('role:Admin,Sales')->group(function () {
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        Route::get('/orders/archived', [OrderController::class, 'archived'])->name('orders.archived');
        Route::post('/orders/{id}/restore', [OrderController::class, 'restore'])->name('orders.restore');
    });

    Route::middleware('role:Admin,Purchasing,Warehouse,Route')->group(function () {
        Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    });

    Route::middleware('role:Admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});
