<?php

use App\Http\Controllers\Admin\ImportacionController;
use App\Models\Cliente;
use App\Models\Poliza;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

function archivoSubido(array $filas, string $nombre = 'clientes.xlsx'): UploadedFile
{
    return new UploadedFile(crearExcel($filas), $nombre, null, null, true);
}

beforeEach(function () {
    Storage::fake('local');
    $this->admin = User::factory()->admin()->create();
});

test('employees can not use the importer', function () {
    $empleado = User::factory()->create();

    $this->actingAs($empleado)->get('/admin/importacion')->assertForbidden();
    $this->actingAs($empleado)->post('/admin/importacion', ['archivo' => archivoSubido([filaExcel()])])->assertForbidden();
    $this->actingAs($empleado)->post('/admin/importacion/confirmar', [])->assertForbidden();
    $this->actingAs($empleado)->delete('/admin/importacion')->assertForbidden();
});

test('guests are redirected to login from the importer', function () {
    $this->get('/admin/importacion')->assertRedirect('/login');
});

test('admin sees the upload page without a simulation', function () {
    $this->actingAs($this->admin)->get('/admin/importacion')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/ImportacionPage')
            ->where('simulacion', null));
});

test('uploading an excel shows the simulation without saving anything', function () {
    $this->actingAs($this->admin)
        ->post('/admin/importacion', ['archivo' => archivoSubido([filaExcel(), filaExcel(['Nº de póliza' => 'POL-2'])])])
        ->assertRedirect('/admin/importacion');

    expect(Storage::disk('local')->files(ImportacionController::DIRECTORIO))->toHaveCount(1);

    $this->actingAs($this->admin)->get('/admin/importacion')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('simulacion.resumen.clientes_nuevos', 1)
            ->where('simulacion.resumen.polizas_nuevas', 2)
            ->has('huella'));

    expect(Cliente::count())->toBe(0);
});

test('confirming saves the accepted clients and deletes the file', function () {
    $this->actingAs($this->admin)->post('/admin/importacion', ['archivo' => archivoSubido([filaExcel()])]);
    $huella = $this->get('/admin/importacion')->viewData('page')['props']['huella'];

    $this->post('/admin/importacion/confirmar', ['aceptados' => ['00000000T'], 'huella' => $huella])
        ->assertRedirect('/admin/importacion')
        ->assertSessionHas('exito', 'Importación completada: 1 cliente nuevo, 0 actualizados y 1 póliza nueva.');

    expect(Cliente::count())->toBe(1)
        ->and(Poliza::count())->toBe(1)
        ->and(Storage::disk('local')->files(ImportacionController::DIRECTORIO))->toBe([]);

    $this->get('/admin/importacion')->assertInertia(fn (AssertableInertia $page) => $page->where('simulacion', null));
});

test('confirming with an outdated fingerprint saves nothing and keeps the simulation', function () {
    $this->actingAs($this->admin)->post('/admin/importacion', ['archivo' => archivoSubido([filaExcel()])]);

    $this->post('/admin/importacion/confirmar', ['aceptados' => ['00000000T'], 'huella' => str_repeat('0', 64)])
        ->assertRedirect('/admin/importacion')
        ->assertSessionHasErrors('importacion');

    expect(Cliente::count())->toBe(0)
        ->and(Storage::disk('local')->files(ImportacionController::DIRECTORIO))->toHaveCount(1);
});

test('a file that is not xlsx is rejected with a clear error', function () {
    $this->actingAs($this->admin)
        ->post('/admin/importacion', ['archivo' => UploadedFile::fake()->createWithContent('clientes.txt', 'hola')])
        ->assertSessionHasErrors('archivo');

    $falso = UploadedFile::fake()->createWithContent('clientes.xlsx', 'esto no es un excel');
    $this->post('/admin/importacion', ['archivo' => $falso])
        ->assertSessionHasErrors(['archivo' => 'El archivo no es un Excel (.xlsx) válido.']);

    expect(Storage::disk('local')->files(ImportacionController::DIRECTORIO))->toBe([])
        ->and(Cliente::count())->toBe(0);
});

test('a file over 10 MB is rejected', function () {
    $this->actingAs($this->admin)
        ->post('/admin/importacion', ['archivo' => UploadedFile::fake()->create('clientes.xlsx', 10241)])
        ->assertSessionHasErrors('archivo');
});

test('cancelling deletes the file and the simulation', function () {
    $this->actingAs($this->admin)->post('/admin/importacion', ['archivo' => archivoSubido([filaExcel()])]);

    $this->delete('/admin/importacion')->assertRedirect('/admin/importacion');

    expect(Storage::disk('local')->files(ImportacionController::DIRECTORIO))->toBe([]);
    $this->get('/admin/importacion')->assertInertia(fn (AssertableInertia $page) => $page->where('simulacion', null));
});

test('a simulation older than one hour expires and its file is deleted', function () {
    $this->actingAs($this->admin)->post('/admin/importacion', ['archivo' => archivoSubido([filaExcel()])]);

    $this->travel(61)->minutes();

    $this->get('/admin/importacion')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('simulacion', null)
            ->where('caducada', true));

    expect(Storage::disk('local')->files(ImportacionController::DIRECTORIO))->toBe([]);
});
