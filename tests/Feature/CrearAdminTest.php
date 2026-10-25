<?php

use App\Models\User;

test('the command creates an admin user', function () {
    $this->artisan('usuario:crear-admin')
        ->expectsQuestion('Nombre', 'Jefe Prueba')
        ->expectsQuestion('Email', 'jefe@example.com')
        ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'contrasena-del-jefe')
        ->expectsQuestion('Repite la contraseña', 'contrasena-del-jefe')
        ->assertSuccessful();

    $admin = User::where('email', 'jefe@example.com')->first();

    expect($admin->esAdmin())->toBeTrue()
        ->and($admin->activo)->toBeTrue();
});

test('the command rejects short passwords', function () {
    $this->artisan('usuario:crear-admin')
        ->expectsQuestion('Nombre', 'Jefe Prueba')
        ->expectsQuestion('Email', 'jefe@example.com')
        ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'corta')
        ->expectsQuestion('Repite la contraseña', 'corta')
        ->assertFailed();

    expect(User::count())->toBe(0);
});
