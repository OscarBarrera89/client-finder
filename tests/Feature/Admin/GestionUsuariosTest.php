<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

test('employees can not access user management', function () {
    $empleado = User::factory()->create();

    $this->actingAs($empleado)->get('/admin/usuarios')->assertForbidden();
    $this->actingAs($empleado)->post('/admin/usuarios', [])->assertForbidden();
});

test('guests are redirected to login from user management', function () {
    $this->get('/admin/usuarios')->assertRedirect('/login');
});

test('admin can list users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Empleado Prueba']);

    $this->actingAs($admin)
        ->get('/admin/usuarios')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/UsuariosPage')
            ->has('usuarios', 2)
            ->where('usuarios.0.rol', User::ROL_ADMIN)
            ->missing('usuarios.0.password')
        );
});

test('admin can create an employee', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/usuarios', [
            'name' => 'Empleado Nuevo',
            'email' => 'empleado@example.com',
            'password' => 'contrasena-segura',
            'password_confirmation' => 'contrasena-segura',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $nuevo = User::where('email', 'empleado@example.com')->first();

    expect($nuevo->rol)->toBe(User::ROL_EMPLEADO)
        ->and($nuevo->activo)->toBeTrue();
});

test('admin can not create another admin through the form', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/usuarios', [
        'name' => 'Intento Admin',
        'email' => 'otro@example.com',
        'password' => 'contrasena-segura',
        'password_confirmation' => 'contrasena-segura',
        'rol' => User::ROL_ADMIN,
    ]);

    expect(User::where('email', 'otro@example.com')->first()->rol)->toBe(User::ROL_EMPLEADO);
});

test('passwords must be at least 10 characters', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/usuarios', [
            'name' => 'Empleado',
            'email' => 'corta@example.com',
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ])
        ->assertSessionHasErrors('password');
});

test('admin can change an employee password', function () {
    $admin = User::factory()->admin()->create();
    $empleado = User::factory()->create();

    $this->actingAs($admin)
        ->put("/admin/usuarios/{$empleado->id}/password", [
            'password' => 'nueva-contrasena',
            'password_confirmation' => 'nueva-contrasena',
        ])
        ->assertSessionHasNoErrors();

    expect(Hash::check('nueva-contrasena', $empleado->fresh()->password))->toBeTrue();
});

test('admin can deactivate and reactivate an employee', function () {
    $admin = User::factory()->admin()->create();
    $empleado = User::factory()->create();

    $this->actingAs($admin)->patch("/admin/usuarios/{$empleado->id}/activo");
    expect($empleado->fresh()->activo)->toBeFalse();

    $this->actingAs($admin)->patch("/admin/usuarios/{$empleado->id}/activo");
    expect($empleado->fresh()->activo)->toBeTrue();
});

test('admin can not deactivate or change the password of an admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch("/admin/usuarios/{$admin->id}/activo")->assertForbidden();
    $this->actingAs($admin)->put("/admin/usuarios/{$admin->id}/password", [
        'password' => 'nueva-contrasena',
        'password_confirmation' => 'nueva-contrasena',
    ])->assertForbidden();

    expect($admin->fresh()->activo)->toBeTrue();
});
