<?php

use App\Importacion\Normalizador;

test('identificacion is uppercased without spaces, hyphens or dots', function () {
    expect(Normalizador::identificacion(' 12345678-z '))->toBe('12345678Z')
        ->and(Normalizador::identificacion('x.1234.567 l'))->toBe('X1234567L')
        ->and(Normalizador::identificacion(''))->toBe('')
        ->and(Normalizador::identificacion(null))->toBe('');
});

test('matricula is uppercased without spaces or hyphens', function () {
    expect(Normalizador::matricula('1234-abc'))->toBe('1234ABC')
        ->and(Normalizador::matricula(' 1234 abc '))->toBe('1234ABC')
        ->and(Normalizador::matricula(null))->toBe('');
});

test('clave de catalogo ignores case, accents and extra spaces', function () {
    expect(Normalizador::claveCatalogo('  Mapfre   Vida '))->toBe('mapfre vida')
        ->and(Normalizador::claveCatalogo('AUTOMÓVIL'))->toBe('automovil')
        ->and(Normalizador::claveCatalogo('Automovil'))->toBe('automovil');
});

test('texto trims and collapses inner spaces, returning null when empty', function () {
    expect(Normalizador::texto('  Calle   Mayor  1 '))->toBe('Calle Mayor 1')
        ->and(Normalizador::texto('   '))->toBeNull()
        ->and(Normalizador::texto(null))->toBeNull();
});

test('codigo de catalogo is uppercase, underscored and at most 20 characters', function () {
    expect(Normalizador::codigoCatalogo('Mapfre Vida'))->toBe('MAPFRE_VIDA')
        ->and(Normalizador::codigoCatalogo('Automóvil y responsabilidad civil'))->toHaveLength(20)
        ->and(Normalizador::codigoCatalogo('Automóvil y responsabilidad civil'))->toStartWith('AUTOMOVIL_Y_');
});
