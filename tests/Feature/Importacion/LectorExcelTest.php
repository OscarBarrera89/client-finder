<?php

use App\Importacion\ArchivoNoValido;
use App\Importacion\LectorExcel;

test('reads rows keyed by excel row number with internal column names', function () {
    $ruta = crearExcel([filaExcel(), filaExcel(['Nº de póliza' => 'POL-0002'])]);

    $lectura = (new LectorExcel)->leer($ruta);

    expect($lectura['faltantes'])->toBe([])
        ->and(array_keys($lectura['filas']))->toBe([2, 3])
        ->and($lectura['filas'][2]['nombre'])->toBe('Cliente Prueba')
        ->and($lectura['filas'][2]['identificacion'])->toBe('00000000T')
        ->and($lectura['filas'][3]['numero_poliza'])->toBe('POL-0002')
        ->and($lectura['filas'][2]['tipo_vencimiento'])->toBe('Anual');
});

test('recognizes headers ignoring case, accents and spacing, and ignores extra columns', function () {
    $ruta = crearExcel(
        [['Cliente Prueba', '00000000T', 'pol-9', 'Aseguradora Prueba', 'Hogar', 'ignorado']],
        ['  NOMBRE ', 'dni/nie/cif', 'Nº DE POLIZA', 'compania', 'RAMO', 'Columna extra'],
    );

    $lectura = (new LectorExcel)->leer($ruta);

    expect($lectura['faltantes'])->toBe([])
        ->and($lectura['filas'][2])->toMatchArray([
            'nombre' => 'Cliente Prueba',
            'numero_poliza' => 'pol-9',
            'ramo' => 'Hogar',
        ])
        ->and($lectura['filas'][2])->not->toHaveKey('columna extra');
});

test('skips empty rows but keeps the real excel row number', function () {
    $ruta = crearExcel([filaExcel(), [], filaExcel(['Nº de póliza' => 'POL-0003'])]);

    $lectura = (new LectorExcel)->leer($ruta);

    expect(array_keys($lectura['filas']))->toBe([2, 4]);
});

test('reports missing mandatory columns', function () {
    $ruta = crearExcel([['Cliente Prueba', 'Hogar']], ['Nombre', 'Ramo']);

    $lectura = (new LectorExcel)->leer($ruta);

    expect($lectura['faltantes'])->toBe(['DNI / NIE / CIF', 'Nº de póliza', 'Compañía']);
});

test('returns dates as dates and numeric cells as text where text is expected', function () {
    $ruta = crearExcel([filaExcel(['Teléfono' => 600000001, 'Nº de póliza' => 12345])]);

    $fila = (new LectorExcel)->leer($ruta)['filas'][2];

    expect($fila['fecha_contratacion'])->toBeInstanceOf(DateTimeInterface::class)
        ->and($fila['fecha_contratacion']->format('Y-m-d'))->toBe('2026-01-15')
        ->and($fila['telefono'])->toBe('600000001')
        ->and($fila['numero_poliza'])->toBe('12345')
        ->and($fila['prima_anual'])->toBe(300.5);
});

test('rejects a file that is not a valid xlsx', function () {
    $ruta = tempnam(sys_get_temp_dir(), 'excel').'.xlsx';
    file_put_contents($ruta, 'esto no es un excel');

    (new LectorExcel)->leer($ruta);
})->throws(ArchivoNoValido::class);

test('rejects a file without data rows', function () {
    (new LectorExcel)->leer(crearExcel([]));
})->throws(ArchivoNoValido::class, 'El archivo no tiene filas para importar.');
