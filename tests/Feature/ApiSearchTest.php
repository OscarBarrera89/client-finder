<?php

use App\Models\Cliente;
use App\Models\Compania;
use App\Models\Poliza;
use App\Models\Ramo;
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

test('the search api finds identificacion and matricula written with spaces or hyphens', function () {
    $cliente = Cliente::create(['nombre' => 'Cliente Prueba', 'identificacion' => '00000001R', 'activo' => true]);
    Poliza::create([
        'cliente_id' => $cliente->id,
        'compania_id' => Compania::create(['codigo' => 'ASEG', 'nombre' => 'Aseguradora Prueba', 'activo' => true])->id,
        'ramo_id' => Ramo::create(['codigo' => 'AUTO', 'nombre' => 'Automóvil', 'activo' => true])->id,
        'numero_poliza' => 'POL-1',
        'matricula' => '1234ABC',
        'activo' => true,
    ]);

    $this->actingAs(User::factory()->create());

    $this->getJson('/api/search?q=00000001-r')->assertJsonPath('data.0.identificacion', '00000001R');
    $this->getJson('/api/search?type=poliza&q=1234-abc')->assertJsonPath('data.0.matricula', '1234ABC');
});
