<?php

namespace App\Importacion;

use App\Models\Cliente;
use App\Models\Compania;
use App\Models\Poliza;
use App\Models\Ramo;
use App\Models\TipoVencimiento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Guarda los clientes aceptados de una simulación. Vuelve a simular dentro de la
 * transacción y solo guarda si el resultado es el mismo que vio el admin: o entra
 * todo lo aceptado, o no entra nada.
 */
class EjecutorImportacion
{
    /** Clave normalizada => id, por modelo de catálogo. */
    private array $catalogos = [];

    private array $totales = [];

    public function __construct(
        private LectorExcel $lector,
        private SimuladorImportacion $simulador,
    ) {}

    /**
     * @param  list<string>  $aceptados  identificaciones normalizadas de los clientes aceptados
     * @return array<string, int> totales de lo guardado
     */
    public function ejecutar(string $ruta, array $aceptados, string $huella, int $usuarioId): array
    {
        $totales = DB::transaction(function () use ($ruta, $aceptados, $huella) {
            $simulacion = $this->simulador->simular($this->lector->leer($ruta));

            if (SimuladorImportacion::huella($simulacion) !== $huella) {
                throw new SimulacionCaducada;
            }

            if ($simulacion['errores'] !== []) {
                throw new ArchivoNoValido('El Excel tiene errores. Corrígelos antes de importar.');
            }

            $this->catalogos = [];
            $this->totales = [
                'clientes_creados' => 0, 'clientes_actualizados' => 0, 'polizas_creadas' => 0,
                'companias_creadas' => 0, 'ramos_creados' => 0, 'tipos_vencimiento_creados' => 0,
            ];

            foreach ($simulacion['clientes'] as $datos) {
                if (in_array($datos['identificacion'], $aceptados, true)) {
                    $this->guardarCliente($datos);
                }
            }

            return $this->totales;
        });

        // Solo totales: nunca DNI, nombres ni números de póliza (constitución, principio 18).
        Log::info('Importación de Excel completada', ['usuario_id' => $usuarioId, ...$totales]);

        return $totales;
    }

    private function guardarCliente(array $datos): void
    {
        $valores = array_filter($datos['datos'], fn ($valor) => $valor !== null);

        if ($datos['estado'] === 'nuevo') {
            $cliente = Cliente::create([...$valores, 'identificacion' => $datos['identificacion'], 'activo' => true]);
            $this->totales['clientes_creados']++;
        } else {
            $cliente = Cliente::where('identificacion', $datos['identificacion'])->firstOrFail();
            $cliente->update($valores);
            $this->totales['clientes_actualizados']++;
        }

        foreach ($datos['polizas_nuevas'] as $poliza) {
            Poliza::create([
                'cliente_id' => $cliente->id,
                'numero_poliza' => $poliza['numero_poliza'],
                'compania_id' => $this->catalogo(Compania::class, $poliza['compania'], 'companias_creadas'),
                'ramo_id' => $this->catalogo(Ramo::class, $poliza['ramo'], 'ramos_creados'),
                'tipo_vencimiento_id' => $poliza['tipo_vencimiento'] === null
                    ? null
                    : $this->catalogo(TipoVencimiento::class, $poliza['tipo_vencimiento'], 'tipos_vencimiento_creados'),
                'matricula' => $poliza['matricula'],
                'prima_anual' => $poliza['prima_anual'],
                'fecha_contratacion' => $poliza['fecha_contratacion'],
                'activo' => true,
            ]);
            $this->totales['polizas_creadas']++;
        }
    }

    /** Id del catálogo con ese nombre (sin mayúsculas ni tildes); lo crea si no existe. */
    private function catalogo(string $modelo, string $nombre, string $total): int
    {
        $this->catalogos[$modelo] ??= $modelo::pluck('id', 'nombre')
            ->mapWithKeys(fn ($id, $existente) => [Normalizador::claveCatalogo($existente) => $id])
            ->all();

        $clave = Normalizador::claveCatalogo($nombre);

        if (! isset($this->catalogos[$modelo][$clave])) {
            /** @var Model $nuevo */
            $nuevo = $modelo::create(['codigo' => $this->codigoLibre($modelo, $nombre), 'nombre' => $nombre, 'activo' => true]);
            $this->catalogos[$modelo][$clave] = $nuevo->id;
            $this->totales[$total]++;
        }

        return $this->catalogos[$modelo][$clave];
    }

    /** Código generado del nombre; si ya está usado (también por uno dado de baja), añade _2, _3… */
    private function codigoLibre(string $modelo, string $nombre): string
    {
        $base = Normalizador::codigoCatalogo($nombre) ?: 'CATALOGO';
        $codigo = $base;

        for ($i = 2; $modelo::withTrashed()->where('codigo', $codigo)->exists(); $i++) {
            $sufijo = "_{$i}";
            $codigo = substr($base, 0, 20 - strlen($sufijo)).$sufijo;
        }

        return $codigo;
    }
}
