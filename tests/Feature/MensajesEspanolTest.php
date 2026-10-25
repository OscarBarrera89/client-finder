<?php

use App\Models\User;

test('failed login shows the error in spanish', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'contrasena-incorrecta',
    ])->assertSessionHasErrors([
        'email' => 'El email o la contraseña no son correctos.',
    ]);
});

test('validation errors are shown in spanish with spanish field names', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/usuarios', [
            'name' => '',
            'email' => 'no-es-un-email',
            'password' => 'corta',
            'password_confirmation' => 'otra',
        ])
        ->assertSessionHasErrors([
            'name' => 'El campo nombre es obligatorio.',
            'email' => 'El campo email debe ser un email válido.',
        ]);

    $errores = session('errors')->get('password');

    expect($errores)->toContain('La confirmación del campo contraseña no coincide.')
        ->and($errores)->toContain('El campo contraseña debe tener al menos 10 caracteres.');
});

test('wrong current password is shown in spanish', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/settings/password')
        ->put('/settings/password', [
            'current_password' => 'no-es-la-actual',
            'password' => 'nueva-contrasena-larga',
            'password_confirmation' => 'nueva-contrasena-larga',
        ])
        ->assertSessionHasErrors([
            'current_password' => 'La contraseña no es correcta.',
        ]);
});
