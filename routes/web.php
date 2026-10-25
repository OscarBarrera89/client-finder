<?php

use App\Http\Controllers\Admin\ImportacionController;
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

        // Importación de clientes y pólizas desde Excel
        Route::get('importacion', [ImportacionController::class, 'index'])->name('importacion.index');
        Route::post('importacion', [ImportacionController::class, 'simular'])->name('importacion.simular');
        Route::post('importacion/confirmar', [ImportacionController::class, 'confirmar'])->name('importacion.confirmar');
        Route::delete('importacion', [ImportacionController::class, 'cancelar'])->name('importacion.cancelar');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
