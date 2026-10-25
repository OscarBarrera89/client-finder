<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Importacion\ArchivoNoValido;
use App\Importacion\EjecutorImportacion;
use App\Importacion\LectorExcel;
use App\Importacion\SimulacionCaducada;
use App\Importacion\SimuladorImportacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Importación de clientes y pólizas desde Excel (solo admin).
 *
 * El archivo subido se guarda en el disco privado mientras dura la simulación y se
 * borra al confirmar, al cancelar o al caducar. En la sesión solo se guarda su nombre.
 */
class ImportacionController extends Controller
{
    public const DIRECTORIO = 'importaciones';

    private const SESION = 'importacion';

    private const CADUCIDAD_MINUTOS = 60;

    public function __construct(
        private LectorExcel $lector,
        private SimuladorImportacion $simulador,
    ) {}

    public function index(Request $request): Response
    {
        $this->borrarCaducados();
        $caducada = false;
        $simulacion = null;

        if ($importacion = $request->session()->get(self::SESION)) {
            if ($this->haCaducado($importacion)) {
                $this->olvidar($request);
                $caducada = true;
            } else {
                try {
                    $simulacion = $this->simulador->simular($this->lector->leer($this->ruta($importacion['archivo'])));
                } catch (ArchivoNoValido) {
                    $this->olvidar($request);
                }
            }
        }

        return Inertia::render('admin/ImportacionPage', [
            'simulacion' => $simulacion,
            'huella' => $simulacion === null ? null : SimuladorImportacion::huella($simulacion),
            'caducada' => $caducada,
        ]);
    }

    public function simular(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo' => ['required', 'file', 'extensions:xlsx', 'max:10240'],
        ], [
            'archivo.extensions' => 'El archivo debe ser un Excel (.xlsx).',
            'archivo.max' => 'El archivo no puede pesar más de 10 MB.',
        ]);

        $this->borrarCaducados();
        $this->olvidar($request);

        $nombre = Str::uuid().'.xlsx';
        $request->file('archivo')->storeAs(self::DIRECTORIO, $nombre, 'local');

        try {
            $this->lector->leer($this->ruta($nombre));
        } catch (ArchivoNoValido $e) {
            Storage::disk('local')->delete(self::DIRECTORIO.'/'.$nombre);

            return back()->withErrors(['archivo' => $e->getMessage()]);
        }

        $request->session()->put(self::SESION, ['archivo' => $nombre, 'subido' => now()->timestamp]);

        return to_route('admin.importacion.index');
    }

    public function confirmar(Request $request, EjecutorImportacion $ejecutor): RedirectResponse
    {
        $datos = $request->validate([
            'aceptados' => ['present', 'array'],
            'aceptados.*' => ['string'],
            'huella' => ['required', 'string'],
        ]);

        $importacion = $request->session()->get(self::SESION);

        if ($importacion === null || $this->haCaducado($importacion)) {
            $this->olvidar($request);

            return to_route('admin.importacion.index')
                ->withErrors(['importacion' => 'La simulación ha caducado. Vuelve a subir el archivo.']);
        }

        try {
            $totales = $ejecutor->ejecutar($this->ruta($importacion['archivo']), $datos['aceptados'], $datos['huella'], $request->user()->id);
        } catch (SimulacionCaducada|ArchivoNoValido $e) {
            return to_route('admin.importacion.index')->withErrors(['importacion' => $e->getMessage()]);
        }

        $this->olvidar($request);

        return to_route('admin.importacion.index')->with('exito', sprintf(
            'Importación completada: %d clientes nuevos, %d actualizados y %d pólizas nuevas.',
            $totales['clientes_creados'],
            $totales['clientes_actualizados'],
            $totales['polizas_creadas'],
        ));
    }

    public function cancelar(Request $request): RedirectResponse
    {
        $this->olvidar($request);

        return to_route('admin.importacion.index')->with('exito', 'Importación cancelada.');
    }

    private function ruta(string $archivo): string
    {
        return Storage::disk('local')->path(self::DIRECTORIO.'/'.$archivo);
    }

    private function haCaducado(array $importacion): bool
    {
        return now()->timestamp - $importacion['subido'] > self::CADUCIDAD_MINUTOS * 60;
    }

    /** Borra el archivo de la importación en curso y la quita de la sesión. */
    private function olvidar(Request $request): void
    {
        if ($importacion = $request->session()->pull(self::SESION)) {
            Storage::disk('local')->delete(self::DIRECTORIO.'/'.$importacion['archivo']);
        }
    }

    /** Borra archivos de importaciones abandonadas (de cualquier admin) que ya han caducado. */
    private function borrarCaducados(): void
    {
        $disco = Storage::disk('local');
        $limite = now()->subMinutes(self::CADUCIDAD_MINUTOS)->timestamp;

        foreach ($disco->files(self::DIRECTORIO) as $archivo) {
            if ($disco->lastModified($archivo) < $limite) {
                $disco->delete($archivo);
            }
        }
    }
}
