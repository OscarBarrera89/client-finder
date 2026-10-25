<?php

use App\Importacion\ArchivoNoValido;
use App\Importacion\EjecutorImportacion;
use App\Importacion\LectorExcel;
use App\Importacion\SimulacionCaducada;
use App\Importacion\SimuladorImportacion;
use App\Models\Cliente;
use App\Models\Compania;
use App\Models\Poliza;
use App\Models\Ramo;
use App\Models\TipoVencimiento;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Log;

/** Simula el Excel y devuelve [ruta, huella, simulación], como lo vería el admin. */
function prepararImportacion(array $filas): array
{
    $ruta = crearExcel($filas);
    $simulacion = (new SimuladorImportacion)->simular((new LectorExcel)->leer($ruta));

    return [$ruta, SimuladorImportacion::huella($simulacion), $simulacion];
}

function ejecutar(string $ruta, array $aceptados, string $huella): array
{
    return app(EjecutorImportacion::class)->ejecutar($ruta, $aceptados, $huella, 1);
}

test('accepting all creates the clients, their policies and the catalogs', function () {
    [$ruta, $huella] = prepararImportacion([
        filaExcel(['DNI / NIE / CIF' => '00000001R', 'Nº de póliza' => 'POL-1', 'Matrícula' => '1111-bbb']),
        filaExcel(['DNI / NIE / CIF' => '00000001R', 'Nº de póliza' => 'POL-2']),
        filaExcel(['DNI / NIE / CIF' => '00000002W', 'Nombre' => 'Otro Cliente', 'Nº de póliza' => 'POL-3']),
    ]);

    $totales = ejecutar($ruta, ['00000001R', '00000002W'], $huella);

    expect($totales)->toMatchArray(['clientes_creados' => 2, 'clientes_actualizados' => 0, 'polizas_creadas' => 3])
        ->and(Cliente::count())->toBe(2)
        ->and(Poliza::count())->toBe(3)
        ->and(Cliente::where('identificacion', '00000001R')->first()->polizas)->toHaveCount(2);

    $poliza = Poliza::where('numero_poliza', 'POL-1')->first();
    expect($poliza->matricula)->toBe('1111BBB')
        ->and((float) $poliza->prima_anual)->toBe(300.5)
        ->and($poliza->fecha_contratacion->format('Y-m-d'))->toBe('2026-01-15')
        ->and($poliza->compania->nombre)->toBe('Aseguradora Prueba')
        ->and($poliza->ramo->nombre)->toBe('Automóvil')
        ->and($poliza->tipoVencimiento->nombre)->toBe('Anual')
        ->and($poliza->activo)->toBeTrue();
});

test('a discarded client is not saved', function () {
    [$ruta, $huella] = prepararImportacion([
        filaExcel(['DNI / NIE / CIF' => '00000001R', 'Nº de póliza' => 'POL-1']),
        filaExcel(['DNI / NIE / CIF' => '00000002W', 'Nombre' => 'Descartado', 'Nº de póliza' => 'POL-2', 'Compañía' => 'Solo Del Descartado']),
    ]);

    ejecutar($ruta, ['00000001R'], $huella);

    expect(Cliente::pluck('identificacion')->all())->toBe(['00000001R'])
        ->and(Poliza::pluck('numero_poliza')->all())->toBe(['POL-1'])
        ->and(Compania::where('nombre', 'Solo Del Descartado')->exists())->toBeFalse();
});

test('an accepted existing client is updated and gets only its new policies', function () {
    $cliente = Cliente::create(['nombre' => 'Cliente Prueba', 'identificacion' => '00000003A', 'telefono' => '600000000', 'email' => 'antiguo@example.com', 'activo' => true]);
    $compania = Compania::create(['codigo' => 'ASEG', 'nombre' => 'Aseguradora Prueba', 'activo' => true]);
    $ramo = Ramo::create(['codigo' => 'AUTO', 'nombre' => 'Automóvil', 'activo' => true]);
    $existente = Poliza::create(['cliente_id' => $cliente->id, 'compania_id' => $compania->id, 'ramo_id' => $ramo->id, 'numero_poliza' => 'POL-EXISTE', 'prima_anual' => 100, 'activo' => true]);

    [$ruta, $huella] = prepararImportacion([
        filaExcel(['DNI / NIE / CIF' => '00000003A', 'Nº de póliza' => 'POL-EXISTE', 'Teléfono' => '611111111', 'Email' => null, 'Prima anual' => 999]),
        filaExcel(['DNI / NIE / CIF' => '00000003A', 'Nº de póliza' => 'POL-NUEVA', 'Email' => null]),
    ]);

    $totales = ejecutar($ruta, ['00000003A'], $huella);

    $cliente->refresh();
    expect($totales)->toMatchArray(['clientes_creados' => 0, 'clientes_actualizados' => 1, 'polizas_creadas' => 1])
        ->and($cliente->telefono)->toBe('611111111')
        ->and($cliente->email)->toBe('antiguo@example.com')
        ->and($cliente->polizas()->count())->toBe(2)
        ->and((float) $existente->fresh()->prima_anual)->toBe(100.0)
        ->and(Compania::count())->toBe(1)
        ->and(Ramo::count())->toBe(1);
});

test('existing catalogs are reused ignoring case and accents', function () {
    Compania::create(['codigo' => 'MAPFRE', 'nombre' => 'Mapfre', 'activo' => true]);
    TipoVencimiento::create(['codigo' => 'ANUAL', 'nombre' => 'Anual', 'activo' => true]);

    [$ruta, $huella] = prepararImportacion([filaExcel(['Compañía' => 'mapfre', 'Vencimiento' => 'ANUAL'])]);
    ejecutar($ruta, ['00000000T'], $huella);

    expect(Compania::count())->toBe(1)
        ->and(TipoVencimiento::count())->toBe(1)
        ->and(Poliza::first()->compania->codigo)->toBe('MAPFRE');
});

test('a new catalog gets a unique code even if the generated one is taken', function () {
    Ramo::create(['codigo' => 'HOGAR', 'nombre' => 'Hogar antiguo', 'activo' => true]);

    [$ruta, $huella] = prepararImportacion([filaExcel(['Ramo' => 'Hogar'])]);
    ejecutar($ruta, ['00000000T'], $huella);

    expect(Ramo::where('nombre', 'Hogar')->value('codigo'))->toBe('HOGAR_2');
});

test('identificacion is saved normalized', function () {
    [$ruta, $huella] = prepararImportacion([filaExcel(['DNI / NIE / CIF' => '12345678-z'])]);
    ejecutar($ruta, ['12345678Z'], $huella);

    expect(Cliente::first()->identificacion)->toBe('12345678Z');
});

test('a failure in the middle leaves the database exactly as before', function () {
    [$ruta, $huella] = prepararImportacion([
        filaExcel(['DNI / NIE / CIF' => '00000001R', 'Nº de póliza' => 'POL-1']),
        filaExcel(['DNI / NIE / CIF' => '00000002W', 'Nombre' => 'Otro Cliente', 'Nº de póliza' => 'POL-2']),
    ]);

    Poliza::creating(function (Poliza $poliza) {
        if ($poliza->numero_poliza === 'POL-2') {
            throw new RuntimeException('Fallo simulado');
        }
    });

    expect(fn () => ejecutar($ruta, ['00000001R', '00000002W'], $huella))->toThrow(RuntimeException::class);

    expect(Cliente::count())->toBe(0)
        ->and(Poliza::count())->toBe(0)
        ->and(Compania::count())->toBe(0)
        ->and(Ramo::count())->toBe(0)
        ->and(TipoVencimiento::count())->toBe(0);
});

test('nothing is saved if the data changed since the simulation', function () {
    [$ruta, $huella] = prepararImportacion([filaExcel()]);

    Cliente::create(['nombre' => 'Cliente Prueba', 'identificacion' => '00000000T', 'activo' => true]);

    expect(fn () => ejecutar($ruta, ['00000000T'], $huella))->toThrow(SimulacionCaducada::class);
    expect(Poliza::count())->toBe(0);
});

test('nothing is saved if the simulation has errors', function () {
    [$ruta, $huella] = prepararImportacion([filaExcel(['Nº de póliza' => null])]);

    expect(fn () => ejecutar($ruta, ['00000000T'], $huella))->toThrow(ArchivoNoValido::class);
    expect(Cliente::count())->toBe(0);
});

test('logs never contain personal data', function () {
    $mensajes = [];
    Log::listen(function (MessageLogged $evento) use (&$mensajes) {
        $mensajes[] = $evento->message.' '.json_encode($evento->context);
    });

    [$ruta, $huella] = prepararImportacion([filaExcel(['DNI / NIE / CIF' => '00000001R', 'Nombre' => 'Nombre Inventado', 'Nº de póliza' => 'POL-SECRETA'])]);
    ejecutar($ruta, ['00000001R'], $huella);

    $todo = implode("\n", $mensajes);
    expect($mensajes)->not->toBeEmpty()
        ->and($todo)->not->toContain('00000001R')
        ->and($todo)->not->toContain('Nombre Inventado')
        ->and($todo)->not->toContain('POL-SECRETA');
});
