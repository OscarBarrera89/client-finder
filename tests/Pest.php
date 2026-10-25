<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/** Cabecera del Excel de clientes tal como está documentada. */
const CABECERA_EXCEL = [
    'Nombre', 'DNI / NIE / CIF', 'Matrícula', 'Compañía', 'Ramo', 'Nº de póliza',
    'Prima anual', 'Fecha de contratación', 'Vencimiento', 'Teléfono', 'Email', 'Dirección',
];

/**
 * Escribe un .xlsx temporal con datos inventados y devuelve su ruta.
 * Cada fila es un array asociativo con las columnas de CABECERA_EXCEL (las que falten quedan vacías)
 * o un array de valores en el orden de $cabecera. Las fechas se escriben con formato de fecha.
 */
function crearExcel(array $filas, array $cabecera = CABECERA_EXCEL): string
{
    $ruta = tempnam(sys_get_temp_dir(), 'excel').'.xlsx';
    $estiloFecha = (new Style)->setFormat('dd/mm/yyyy');

    $writer = new Writer;
    $writer->openToFile($ruta);
    $writer->addRow(Row::fromValues($cabecera));

    foreach ($filas as $fila) {
        $valores = array_is_list($fila)
            ? $fila
            : array_map(fn ($columna) => $fila[$columna] ?? null, $cabecera);

        $writer->addRow(new Row(array_map(
            fn ($valor) => Cell::fromValue($valor, $valor instanceof DateTimeInterface ? $estiloFecha : null),
            $valores,
        )));
    }

    $writer->close();

    return $ruta;
}

/** Fila de Excel con datos inventados; $cambios sobrescribe columnas concretas. */
function filaExcel(array $cambios = []): array
{
    return [
        'Nombre' => 'Cliente Prueba',
        'DNI / NIE / CIF' => '00000000T',
        'Matrícula' => '0000AAA',
        'Compañía' => 'Aseguradora Prueba',
        'Ramo' => 'Automóvil',
        'Nº de póliza' => 'POL-0001',
        'Prima anual' => 300.5,
        'Fecha de contratación' => new DateTimeImmutable('2026-01-15'),
        'Vencimiento' => 'Anual',
        'Teléfono' => '600000000',
        'Email' => 'cliente@example.com',
        'Dirección' => 'Calle Falsa 1',
        ...$cambios,
    ];
}
