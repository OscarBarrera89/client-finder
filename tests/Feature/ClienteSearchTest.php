<?php

use App\Models\Cliente;
use App\Models\Compania;
use App\Models\Poliza;
use App\Models\Ramo;
use App\Models\TipoVencimiento;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('the dashboard can search clients by policy plate', function () {
    $user = User::factory()->create();

    $cliente = Cliente::create([
        'nombre' => 'Ana Garcia',
        'identificacion' => '12345678Z',
        'telefono' => '600000000',
        'email' => 'ana@example.com',
        'direccion' => 'Calle Principal 1',
        'activo' => true,
    ]);

    $compania = Compania::create([
        'codigo' => 'MAP',
        'nombre' => 'Mapfre',
        'activo' => true,
    ]);

    $ramo = Ramo::create([
        'codigo' => 'AUTO',
        'nombre' => 'Automóvil',
        'activo' => true,
    ]);

    $tipoVencimiento = TipoVencimiento::create([
        'codigo' => 'ANU',
        'nombre' => 'Anual',
        'activo' => true,
    ]);

    Poliza::create([
        'cliente_id' => $cliente->id,
        'compania_id' => $compania->id,
        'ramo_id' => $ramo->id,
        'tipo_vencimiento_id' => $tipoVencimiento->id,
        'numero_poliza' => 'POL-001',
        'matricula' => 'ABC123',
        'prima_anual' => 1200.50,
        'fecha_contratacion' => '2026-01-01',
        'activo' => true,
    ]);

    $response = $this->actingAs($user)->get('/dashboard?q=ABC123');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('dashboard')
        ->where('query', 'ABC123')
        ->where('clientes.0.nombre', 'Ana Garcia')
        ->where('clientes.0.polizas.0.matricula', 'ABC123')
    );
});
