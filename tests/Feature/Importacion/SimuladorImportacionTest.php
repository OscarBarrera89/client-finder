<?php

use App\Importacion\LectorExcel;
use App\Importacion\SimuladorImportacion;
use App\Models\Cliente;
use App\Models\Compania;
use App\Models\Poliza;
use App\Models\Ramo;

function simularExcel(array $filas, array $cabecera = CABECERA_EXCEL): array
{
    return (new SimuladorImportacion)->simular((new LectorExcel)->leer(crearExcel($filas, $cabecera)));
}

function crearClienteConPoliza(string $identificacion, string $numeroPoliza, array $datosCliente = []): Cliente
{
    $cliente = Cliente::create([
        'nombre' => 'Cliente Prueba',
        'identificacion' => $identificacion,
        'telefono' => '600000000',
        'activo' => true,
        ...$datosCliente,
    ]);

    Poliza::create([
        'cliente_id' => $cliente->id,
        'compania_id' => Compania::create(['codigo' => 'ASEG_'.$numeroPoliza, 'nombre' => 'Aseguradora Prueba', 'activo' => true])->id,
        'ramo_id' => Ramo::create(['codigo' => 'RAMO_'.$numeroPoliza, 'nombre' => 'Automóvil', 'activo' => true])->id,
        'numero_poliza' => $numeroPoliza,
        'activo' => true,
    ]);

    return $cliente;
}

test('new clients and policies are counted without touching the database', function () {
    $simulacion = simularExcel([
        filaExcel(['DNI / NIE / CIF' => '00000001R', 'Nº de póliza' => 'POL-1']),
        filaExcel(['DNI / NIE / CIF' => '00000001R', 'Nº de póliza' => 'POL-2']),
        filaExcel(['DNI / NIE / CIF' => '00000002W', 'Nombre' => 'Otro Cliente', 'Nº de póliza' => 'POL-3']),
    ]);

    expect($simulacion['resumen'])->toMatchArray([
        'clientes_nuevos' => 2,
        'clientes_existentes' => 0,
        'polizas_nuevas' => 3,
        'errores' => 0,
    ])
        ->and($simulacion['clientes'][0]['estado'])->toBe('nuevo')
        ->and($simulacion['clientes'][0]['polizas_nuevas'])->toHaveCount(2)
        ->and(Cliente::count())->toBe(0)
        ->and(Poliza::count())->toBe(0);
});

test('an existing client shows matching and new policies', function () {
    crearClienteConPoliza('00000003A', 'POL-EXISTE');

    $simulacion = simularExcel([
        filaExcel(['DNI / NIE / CIF' => '00000003A', 'Nº de póliza' => 'POL-EXISTE']),
        filaExcel(['DNI / NIE / CIF' => '00000003A', 'Nº de póliza' => 'POL-NUEVA']),
    ]);

    $cliente = $simulacion['clientes'][0];

    expect($cliente['estado'])->toBe('existente')
        ->and($cliente['polizas_coincidentes'])->toBe(['POL-EXISTE'])
        ->and(array_column($cliente['polizas_nuevas'], 'numero_poliza'))->toBe(['POL-NUEVA'])
        ->and($simulacion['resumen'])->toMatchArray(['clientes_existentes' => 1, 'polizas_coincidentes' => 1, 'polizas_nuevas' => 1]);
});

test('an existing client shows the differences between the database and the excel', function () {
    crearClienteConPoliza('00000004G', 'POL-4');

    $cliente = simularExcel([
        filaExcel(['DNI / NIE / CIF' => '00000004G', 'Nº de póliza' => 'POL-4', 'Teléfono' => '611111111', 'Email' => null]),
    ])['clientes'][0];

    expect($cliente['diferencias'])->toContain(['campo' => 'telefono', 'actual' => '600000000', 'nuevo' => '611111111'])
        ->and(array_column($cliente['diferencias'], 'campo'))->not->toContain('email');
});

test('identificacion is matched after normalizing', function () {
    crearClienteConPoliza('00000005M', 'POL-5');

    $simulacion = simularExcel([filaExcel(['DNI / NIE / CIF' => '00000005-m', 'Nº de póliza' => 'POL-5'])]);

    expect($simulacion['clientes'][0]['estado'])->toBe('existente')
        ->and($simulacion['clientes'][0]['identificacion'])->toBe('00000005M');
});

test('a row without identificacion is an error with its row number', function () {
    $simulacion = simularExcel([filaExcel(), filaExcel(['DNI / NIE / CIF' => null, 'Nº de póliza' => 'POL-X'])]);

    expect($simulacion['errores'])->toContain(['fila' => 3, 'columna' => 'DNI / NIE / CIF', 'motivo' => 'Es obligatorio.'])
        ->and($simulacion['resumen']['errores'])->toBe(1);
});

test('a policy number that belongs to another client is an error', function () {
    crearClienteConPoliza('00000006Y', 'POL-AJENA');

    $simulacion = simularExcel([filaExcel(['DNI / NIE / CIF' => '00000007F', 'Nº de póliza' => 'POL-AJENA'])]);

    expect($simulacion['errores'][0])->toMatchArray(['fila' => 2, 'columna' => 'Nº de póliza'])
        ->and($simulacion['errores'][0]['motivo'])->toContain('otro cliente');
});

test('missing mandatory columns are errors and nothing else is simulated', function () {
    $simulacion = simularExcel([['Cliente Prueba', 'Hogar']], ['Nombre', 'Ramo']);

    expect($simulacion['clientes'])->toBe([])
        ->and(array_column($simulacion['errores'], 'columna'))->toBe(['DNI / NIE / CIF', 'Nº de póliza', 'Compañía'])
        ->and($simulacion['errores'][0]['fila'])->toBe(1);
});

test('a policy number repeated in the excel is an error in both rows', function () {
    $simulacion = simularExcel([
        filaExcel(['Nº de póliza' => 'POL-REP']),
        filaExcel(['Nº de póliza' => 'POL-REP']),
    ]);

    expect(array_column($simulacion['errores'], 'fila'))->toBe([2, 3]);
});

test('the same identificacion with different names is an error', function () {
    $simulacion = simularExcel([
        filaExcel(['Nº de póliza' => 'POL-A']),
        filaExcel(['Nº de póliza' => 'POL-B', 'Nombre' => 'Nombre Distinto']),
    ]);

    expect($simulacion['errores'])->toHaveCount(1)
        ->and($simulacion['errores'][0])->toMatchArray(['fila' => 3, 'columna' => 'Nombre']);
});

test('the same identificacion with different contact data is a warning using the first value', function () {
    $simulacion = simularExcel([
        filaExcel(['Nº de póliza' => 'POL-A', 'Teléfono' => null]),
        filaExcel(['Nº de póliza' => 'POL-B', 'Teléfono' => '622222222']),
        filaExcel(['Nº de póliza' => 'POL-C', 'Teléfono' => '633333333']),
    ]);

    $cliente = $simulacion['clientes'][0];

    expect($simulacion['errores'])->toBe([])
        ->and($cliente['datos']['telefono'])->toBe('622222222')
        ->and($cliente['avisos'])->toHaveCount(1)
        ->and($simulacion['resumen']['avisos'])->toBe(1);
});

test('invalid email, prima and date are errors', function () {
    $simulacion = simularExcel([filaExcel([
        'Email' => 'no-es-un-email',
        'Prima anual' => 'mucho',
        'Fecha de contratación' => '31/02/2026',
    ])]);

    expect(array_column($simulacion['errores'], 'columna'))->toEqualCanonicalizing(['Email', 'Prima anual', 'Fecha de contratación']);
});

test('prima and date written as spanish text are accepted', function () {
    $poliza = simularExcel([filaExcel([
        'Prima anual' => '1.234,56',
        'Fecha de contratación' => '05/03/2026',
    ])])['clientes'][0]['polizas_nuevas'][0];

    expect($poliza['prima_anual'])->toBe(1234.56)
        ->and($poliza['fecha_contratacion'])->toBe('2026-03-05');
});

test('a text longer than allowed is an error', function () {
    $simulacion = simularExcel([filaExcel(['Matrícula' => str_repeat('A', 16)])]);

    expect($simulacion['errores'][0])->toMatchArray(['columna' => 'Matrícula']);
});

test('identificacion or policy number of a deleted record is an error', function () {
    crearClienteConPoliza('00000008P', 'POL-BORRADA')->delete();
    Poliza::where('numero_poliza', 'POL-BORRADA')->first()->delete();

    $simulacion = simularExcel([filaExcel(['DNI / NIE / CIF' => '00000008P', 'Nº de póliza' => 'POL-BORRADA'])]);

    expect(array_column($simulacion['errores'], 'columna'))->toEqualCanonicalizing(['DNI / NIE / CIF', 'Nº de póliza']);
});

test('catalog values that do not exist are listed as new, ignoring case and accents', function () {
    Compania::create(['codigo' => 'MAPFRE', 'nombre' => 'Mapfre', 'activo' => true]);

    $simulacion = simularExcel([
        filaExcel(['Compañía' => 'mapfre', 'Ramo' => 'Automóvil', 'Nº de póliza' => 'POL-A']),
        filaExcel(['Compañía' => 'Nueva Aseguradora', 'Ramo' => 'AUTOMOVIL', 'Nº de póliza' => 'POL-B']),
    ]);

    expect($simulacion['catalogos_nuevos'])->toBe([
        'companias' => ['Nueva Aseguradora'],
        'ramos' => ['Automóvil'],
        'tipos_vencimiento' => ['Anual'],
    ]);
});

test('the fingerprint is stable and changes when the database changes', function () {
    $filas = [filaExcel(['DNI / NIE / CIF' => '00000009X', 'Nº de póliza' => 'POL-9'])];

    $antes = SimuladorImportacion::huella(simularExcel($filas));
    expect(SimuladorImportacion::huella(simularExcel($filas)))->toBe($antes);

    crearClienteConPoliza('00000009X', 'POL-OTRA');

    expect(SimuladorImportacion::huella(simularExcel($filas)))->not->toBe($antes);
});

test('policy numbers are compared ignoring case, like mysql does', function () {
    crearClienteConPoliza('00000010K', 'POL-MAY');
    crearClienteConPoliza('00000011E', 'POL-AJENA2');

    $simulacion = simularExcel([
        filaExcel(['DNI / NIE / CIF' => '00000010K', 'Nº de póliza' => 'pol-may']),
        filaExcel(['DNI / NIE / CIF' => '00000010K', 'Nº de póliza' => 'pol-ajena2']),
        filaExcel(['DNI / NIE / CIF' => '00000010K', 'Nº de póliza' => 'POL-X']),
        filaExcel(['DNI / NIE / CIF' => '00000010K', 'Nº de póliza' => 'pol-x']),
    ]);

    expect($simulacion['clientes'][0]['polizas_coincidentes'])->toBe(['POL-MAY'])
        ->and($simulacion['errores'])->toContain(['fila' => 3, 'columna' => 'Nº de póliza', 'motivo' => 'Ya existe en la aplicación y es de otro cliente.'])
        ->and(array_column(array_filter($simulacion['errores'], fn ($e) => $e['motivo'] === 'Está repetido en el Excel.'), 'fila'))->toBe([4, 5]);
});
