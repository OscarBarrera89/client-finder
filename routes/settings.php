<?php

use App\Http\Controllers\Settings\AparienciaController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Sin borrado de cuenta: solo el administrador puede desactivar usuarios

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', [AparienciaController::class, 'edit'])->name('appearance');
    Route::patch('settings/appearance', [AparienciaController::class, 'update'])->name('appearance.update');
});
