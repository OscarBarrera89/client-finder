<?php

use App\Models\Cliente;
use App\Models\User;

test('guests can not use the search api', function () {
    $this->getJson('/api/search?q=prueba')->assertUnauthorized();
});

test('authenticated users can use the search api', function () {
    Cliente::create([
        'nombre' => 'Cliente Prueba',
        'identificacion' => '00000000T',
        'activo' => true,
    ]);

    $this->actingAs(User::factory()->create())
        ->getJson('/api/search?q=Prueba')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.nombre', 'Cliente Prueba');
});

test('the search api is rate limited', function () {
    $this->actingAs(User::factory()->create());

    for ($i = 0; $i < 60; $i++) {
        $this->getJson('/api/search?q=x')->assertOk();
    }

    $this->getJson('/api/search?q=x')->assertTooManyRequests();
});
