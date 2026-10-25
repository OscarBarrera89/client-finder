<?php

use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Usa el grupo 'web' para compartir la sesión del login: solo usuarios autenticados
Route::middleware(['web', 'auth', 'throttle:busqueda'])->group(function () {
    Route::get('/search', [SearchController::class, 'search'])->name('api.search');
});
