<?php

namespace App\Importacion;

use App\Models\Cliente;
use App\Models\Compania;
use App\Models\Poliza;
use App\Models\Ramo;
use App\Models\TipoVencimiento;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Calcula qué pasaría al importar las filas leídas del Excel, sin guardar nada:
 * clientes nuevos o existentes, pólizas nuevas o coincidentes, diferencias de datos,
 * catálogos que se crearían, avisos y errores por fila.
 */
class SimuladorImportacion
{
    /** Longitud máxima de cada columna (la de la base de datos). */
    private const LONGITUDES = [
        'nombre' => 150, 'identificacion' => 20, 'matricula' => 15, 'compania' => 150, 'ramo' => 150,
        'numero_poliza' => 50, 'tipo_vencimiento' => 50, 'telefono' => 20, 'email' => 150, 'direccion' => 255,
    ];

    private const PRIMA_MAXIMA = 99999999.99;

    /** Datos del cliente que se comparan y actualizan. */
    private const DATOS_CLIENTE = ['nombre', 'telefono', 'email', 'direccion'];

    private const CONTACTO = ['telefono', 'email', 'direccion'];

    /** @var list<array{fila: int, columna: string, motivo: string}> */
    private array $errores = [];

    /**
     * @param  array{filas: array<int, array<string, mixed>>, faltantes: list<string>}  $lectura
     */
    public function simular(array $lectura): array
    {
        $this->errores = [];

        if ($lectura['faltantes'] !== []) {
            foreach ($lectura['faltantes'] as $columna) {
                $this->error(1, $columna, 'Falta esta columna en la cabecera.');
            }

            return $this->resultado([], $this->catalogosVacios());
        }

        $filas = $this->validarFilas($lectura['filas']);
        $this->validarPolizasRepetidas($filas);
        $grupos = $this->agruparPorCliente($filas);

        $clientes = $this->compararConBaseDeDatos($grupos);

        return $this->resultado($clientes, $this->catalogosNuevos($filas));
    }

    /** Huella de una simulación: cambia si cambia cualquier dato que se fuera a guardar. */
    public static function huella(array $simulacion): string
    {
        return hash('sha256', json_encode($simulacion, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /** @return array<int, array<string, mixed>> filas normalizadas que se pueden usar */
    private function validarFilas(array $filas): array
    {
        $validas = [];

        foreach ($filas as $numero => $fila) {
            $fila['identificacion'] = Normalizador::identificacion($fila['identificacion'] ?? null) ?: null;
            $fila['matricula'] = Normalizador::matricula($fila['matricula'] ?? null) ?: null;

            foreach (LectorExcel::OBLIGATORIAS as $columna) {
                if (($fila[$columna] ?? null) === null) {
                    $this->error($numero, $this->nombreColumna($columna), 'Es obligatorio.');
                }
            }

            foreach (self::LONGITUDES as $columna => $maximo) {
                if (is_string($fila[$columna] ?? null) && mb_strlen($fila[$columna]) > $maximo) {
                    $this->error($numero, $this->nombreColumna($columna), "No puede tener más de {$maximo} caracteres.");
                }
            }

            if (($fila['email'] ?? null) !== null && filter_var($fila['email'], FILTER_VALIDATE_EMAIL) === false) {
                $this->error($numero, 'Email', 'No es un email válido.');
            }

            $fila['prima_anual'] = $this->prima($numero, $fila['prima_anual'] ?? null);
            $fila['fecha_contratacion'] = $this->fecha($numero, $fila['fecha_contratacion'] ?? null);
            $fila['fila'] = $numero;

            $validas[$numero] = $fila;
        }

        return $validas;
    }

    private function prima(int $fila, mixed $valor): ?float
    {
        if ($valor === null) {
            return null;
        }

        if (is_string($valor)) {
            // Formato español: 1.234,56
            $valor = str_replace(',', '.', str_replace('.', '', $valor));
        }

        if (! is_numeric($valor) || $valor < 0 || $valor > self::PRIMA_MAXIMA) {
            $this->error($fila, 'Prima anual', 'Debe ser un importe válido (por ejemplo 1.234,56).');

            return null;
        }

        return round((float) $valor, 2);
    }

    private function fecha(int $fila, mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if ($valor instanceof DateTimeInterface) {
            return $valor->format('Y-m-d');
        }

        $fecha = is_string($valor) ? CarbonImmutable::createFromFormat('!d/m/Y', $valor) : false;

        if ($fecha === false || $fecha->format('d/m/Y') !== $valor) {
            $this->error($fila, 'Fecha de contratación', 'Debe ser una fecha válida (dd/mm/aaaa).');

            return null;
        }

        return $fecha->format('Y-m-d');
    }

    private function validarPolizasRepetidas(array $filas): void
    {
        collect($filas)
            ->filter(fn ($fila) => $fila['numero_poliza'] !== null)
            ->groupBy(fn ($fila) => mb_strtoupper($fila['numero_poliza']))
            ->filter(fn (Collection $grupo) => $grupo->count() > 1)
            ->flatten(1)
            ->each(fn ($fila) => $this->error($fila['fila'], 'Nº de póliza', 'Está repetido en el Excel.'));
    }

    /** @return array<string, list<array<string, mixed>>> identificación => filas */
    private function agruparPorCliente(array $filas): array
    {
        $grupos = collect($filas)
            ->filter(fn ($fila) => $fila['identificacion'] !== null)
            ->groupBy('identificacion')
            ->map(fn (Collection $grupo) => $grupo->values()->all())
            ->all();

        foreach ($grupos as $grupo) {
            $nombre = Normalizador::claveCatalogo($grupo[0]['nombre'] ?? '');
            foreach (array_slice($grupo, 1) as $fila) {
                if (($fila['nombre'] ?? null) !== null && Normalizador::claveCatalogo($fila['nombre']) !== $nombre) {
                    $this->error($fila['fila'], 'Nombre', 'El mismo DNI / NIE / CIF aparece con otro nombre en el Excel.');
                }
            }
        }

        return $grupos;
    }

    private function compararConBaseDeDatos(array $grupos): array
    {
        $existentes = $this->buscarPorLotes(Cliente::withTrashed(), 'identificacion', array_keys($grupos));
        $numeros = collect($grupos)->flatten(1)->pluck('numero_poliza')->filter()->unique()->values()->all();
        $polizas = $this->buscarPorLotes(Poliza::withTrashed(), 'numero_poliza', $numeros);

        $clientes = [];

        foreach ($grupos as $identificacion => $filas) {
            /** @var Cliente|null $existente */
            $existente = $existentes->get($identificacion);

            if ($existente?->trashed()) {
                foreach ($filas as $fila) {
                    $this->error($fila['fila'], 'DNI / NIE / CIF', 'Pertenece a un cliente dado de baja en la aplicación.');
                }
            }

            [$datos, $avisos] = $this->datosCliente($filas);
            $nuevas = [];
            $coincidentes = [];

            foreach ($filas as $fila) {
                /** @var Poliza|null $poliza */
                $poliza = $fila['numero_poliza'] !== null ? $polizas->get(mb_strtoupper($fila['numero_poliza'])) : null;

                if ($poliza === null) {
                    $nuevas[] = $this->polizaNueva($fila);
                } elseif ($poliza->trashed()) {
                    $this->error($fila['fila'], 'Nº de póliza', 'Pertenece a una póliza dada de baja en la aplicación.');
                } elseif ($existente !== null && $poliza->cliente_id === $existente->id) {
                    $coincidentes[] = $poliza->numero_poliza;
                } else {
                    $this->error($fila['fila'], 'Nº de póliza', 'Ya existe en la aplicación y es de otro cliente.');
                }
            }

            $clientes[] = [
                'identificacion' => $identificacion,
                'nombre' => $datos['nombre'],
                'estado' => $existente === null ? 'nuevo' : 'existente',
                'datos' => $datos,
                'diferencias' => $existente === null ? [] : $this->diferencias($existente, $datos),
                'polizas_nuevas' => $nuevas,
                'polizas_coincidentes' => $coincidentes,
                'avisos' => $avisos,
            ];
        }

        return $clientes;
    }

    /**
     * Busca registros por una columna en lotes (límite de parámetros de SQLite) y los indexa por
     * ella en mayúsculas: MySQL/MariaDB comparan sin distinguir mayúsculas y SQLite sí.
     */
    private function buscarPorLotes($consulta, string $columna, array $valores): Collection
    {
        return collect($valores)
            ->map(fn ($valor) => mb_strtoupper($valor))
            ->unique()
            ->chunk(500)
            ->flatMap(fn (Collection $lote) => (clone $consulta)
                ->where(fn ($q) => $q->whereIn($columna, $lote->values())
                    ->orWhereIn(DB::raw("UPPER({$columna})"), $lote->values()))
                ->get())
            ->keyBy(fn ($modelo) => mb_strtoupper($modelo->{$columna}));
    }

    /** Datos del cliente: primer valor no vacío de cada campo; avisa si el contacto difiere entre filas. */
    private function datosCliente(array $filas): array
    {
        $datos = [];
        $avisos = [];

        foreach (self::DATOS_CLIENTE as $campo) {
            $valores = array_values(array_unique(array_filter(array_column($filas, $campo), fn ($v) => $v !== null)));
            $datos[$campo] = $valores[0] ?? null;

            if (in_array($campo, self::CONTACTO, true) && count($valores) > 1) {
                $avisos[] = "{$this->nombreColumna($campo)} distinto entre filas; se usa \"{$valores[0]}\".";
            }
        }

        return [$datos, $avisos];
    }

    /** Campos con valor en el Excel que difieren de lo guardado. */
    private function diferencias(Cliente $cliente, array $datos): array
    {
        $diferencias = [];

        foreach (self::DATOS_CLIENTE as $campo) {
            if ($datos[$campo] !== null && (string) $cliente->{$campo} !== $datos[$campo]) {
                $diferencias[] = ['campo' => $campo, 'actual' => $cliente->{$campo}, 'nuevo' => $datos[$campo]];
            }
        }

        return $diferencias;
    }

    private function polizaNueva(array $fila): array
    {
        return [
            'fila' => $fila['fila'],
            'numero_poliza' => $fila['numero_poliza'],
            'compania' => $fila['compania'],
            'ramo' => $fila['ramo'],
            'tipo_vencimiento' => $fila['tipo_vencimiento'] ?? null,
            'matricula' => $fila['matricula'],
            'prima_anual' => $fila['prima_anual'],
            'fecha_contratacion' => $fila['fecha_contratacion'],
        ];
    }

    /** Nombres de catálogo del Excel que no existen todavía (primera forma escrita de cada uno). */
    private function catalogosNuevos(array $filas): array
    {
        $nuevos = fn (string $modelo, string $columna) => collect($filas)
            ->pluck($columna)
            ->filter()
            ->unique(fn ($nombre) => Normalizador::claveCatalogo($nombre))
            ->reject(fn ($nombre) => $this->clavesExistentes($modelo)->contains(Normalizador::claveCatalogo($nombre)))
            ->values()
            ->all();

        return [
            'companias' => $nuevos(Compania::class, 'compania'),
            'ramos' => $nuevos(Ramo::class, 'ramo'),
            'tipos_vencimiento' => $nuevos(TipoVencimiento::class, 'tipo_vencimiento'),
        ];
    }

    private array $claves = [];

    private function clavesExistentes(string $modelo): Collection
    {
        return $this->claves[$modelo] ??= $modelo::pluck('nombre')->map(fn ($nombre) => Normalizador::claveCatalogo($nombre));
    }

    private function catalogosVacios(): array
    {
        return ['companias' => [], 'ramos' => [], 'tipos_vencimiento' => []];
    }

    private function resultado(array $clientes, array $catalogos): array
    {
        // Ordenación estable: por fila, y dentro de cada fila en el orden en que se detectaron.
        $errores = collect($this->errores)->sortBy('fila')->values()->all();

        return [
            'resumen' => [
                'clientes_nuevos' => count(array_filter($clientes, fn ($c) => $c['estado'] === 'nuevo')),
                'clientes_existentes' => count(array_filter($clientes, fn ($c) => $c['estado'] === 'existente')),
                'polizas_nuevas' => array_sum(array_map(fn ($c) => count($c['polizas_nuevas']), $clientes)),
                'polizas_coincidentes' => array_sum(array_map(fn ($c) => count($c['polizas_coincidentes']), $clientes)),
                'companias_nuevas' => count($catalogos['companias']),
                'ramos_nuevos' => count($catalogos['ramos']),
                'tipos_vencimiento_nuevos' => count($catalogos['tipos_vencimiento']),
                'errores' => count($errores),
                'avisos' => array_sum(array_map(fn ($c) => count($c['avisos']), $clientes)),
            ],
            'clientes' => $clientes,
            'catalogos_nuevos' => $catalogos,
            'errores' => $errores,
        ];
    }

    private function error(int $fila, string $columna, string $motivo): void
    {
        $this->errores[] = ['fila' => $fila, 'columna' => $columna, 'motivo' => $motivo];
    }

    private function nombreColumna(string $columna): string
    {
        return LectorExcel::COLUMNAS[$columna][0];
    }
}
