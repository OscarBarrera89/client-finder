<?php

use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('BuscadorPage');
    })->name('home');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión de usuarios: solo el administrador
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::put('usuarios/{usuario}/password', [UsuarioController::class, 'actualizarPassword'])->name('usuarios.password');
        Route::patch('usuarios/{usuario}/activo', [UsuarioController::class, 'cambiarActivo'])->name('usuarios.activo');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
