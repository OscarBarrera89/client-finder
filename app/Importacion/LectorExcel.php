<?php

namespace App\Importacion;

use DateTimeInterface;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;
use Throwable;

/**
 * Lee la primera hoja de un .xlsx de clientes y pólizas.
 *
 * Reconoce las columnas por el texto de la cabecera (sin mayúsculas, tildes ni
 * signos) y devuelve las filas con su número de fila de Excel.
 */
class LectorExcel
{
    /**
     * Columna interna => [nombre que se enseña, alias de cabecera reconocidos].
     * Los alias se comparan tras pasar la cabecera por clave().
     */
    public const COLUMNAS = [
        'nombre' => ['Nombre', ['nombre', 'nombreyapellidos', 'cliente']],
        'identificacion' => ['DNI / NIE / CIF', ['dniniecif', 'dni', 'nie', 'cif', 'nif', 'identificacion']],
        'matricula' => ['Matrícula', ['matricula']],
        'compania' => ['Compañía', ['compania', 'aseguradora']],
        'ramo' => ['Ramo', ['ramo']],
        'numero_poliza' => ['Nº de póliza', ['nodepoliza', 'ndepoliza', 'numerodepoliza', 'npoliza', 'nopoliza', 'poliza']],
        'prima_anual' => ['Prima anual', ['primaanual', 'prima']],
        'fecha_contratacion' => ['Fecha de contratación', ['fechadecontratacion', 'fechacontratacion']],
        'tipo_vencimiento' => ['Vencimiento', ['vencimiento', 'tipodevencimiento']],
        'telefono' => ['Teléfono', ['telefono']],
        'email' => ['Email', ['email', 'correo', 'correoelectronico']],
        'direccion' => ['Dirección', ['direccion']],
    ];

    public const OBLIGATORIAS = ['nombre', 'identificacion', 'numero_poliza', 'compania', 'ramo'];

    /** Columnas que se guardan como texto aunque Excel las tenga como número. */
    private const TEXTO = ['nombre', 'identificacion', 'matricula', 'compania', 'ramo', 'numero_poliza', 'tipo_vencimiento', 'telefono', 'email', 'direccion'];

    /**
     * @return array{filas: array<int, array<string, mixed>>, faltantes: list<string>}
     */
    public function leer(string $ruta): array
    {
        $opciones = new Options;
        // Sin esto el iterador numera las filas leídas, no las filas reales de Excel.
        $opciones->SHOULD_PRESERVE_EMPTY_ROWS = true;
        $reader = new Reader($opciones);

        try {
            $reader->open($ruta);
            [$columnas, $filas] = $this->leerPrimeraHoja($reader);
        } catch (ArchivoNoValido $e) {
            throw $e;
        } catch (Throwable) {
            throw new ArchivoNoValido('El archivo no es un Excel (.xlsx) válido.');
        } finally {
            $reader->close();
        }

        $faltantes = array_values(array_map(
            fn ($columna) => self::COLUMNAS[$columna][0],
            array_diff(self::OBLIGATORIAS, $columnas),
        ));

        if ($filas === [] && $faltantes === []) {
            throw new ArchivoNoValido('El archivo no tiene filas para importar.');
        }

        return ['filas' => $filas, 'faltantes' => $faltantes];
    }

    /** @return array{0: array<int, string>, 1: array<int, array<string, mixed>>} */
    private function leerPrimeraHoja(Reader $reader): array
    {
        $columnas = null;
        $filas = [];

        foreach ($reader->getSheetIterator() as $hoja) {
            foreach ($hoja->getRowIterator() as $numero => $fila) {
                $valores = $fila->toArray();

                if ($columnas === null) {
                    $columnas = $this->mapearCabecera($valores);

                    continue;
                }

                $datos = [];
                foreach ($columnas as $indice => $columna) {
                    $datos[$columna] = $this->valor($valores[$indice] ?? null, $columna);
                }

                if (array_filter($datos, fn ($valor) => $valor !== null) !== []) {
                    $filas[$numero] = $datos;
                }
            }

            break; // solo la primera hoja
        }

        return [$columnas ?? [], $filas];
    }

    /** @return array<int, string> índice de columna => columna interna */
    private function mapearCabecera(array $cabecera): array
    {
        $alias = [];
        foreach (self::COLUMNAS as $columna => [, $nombres]) {
            foreach ($nombres as $nombre) {
                $alias[$nombre] = $columna;
            }
        }

        $columnas = [];
        foreach ($cabecera as $indice => $texto) {
            $columna = $alias[self::clave((string) $texto)] ?? null;
            if ($columna !== null && ! in_array($columna, $columnas, true)) {
                $columnas[$indice] = $columna;
            }
        }

        return $columnas;
    }

    private function valor(mixed $valor, string $columna): mixed
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if ($valor instanceof DateTimeInterface) {
            return in_array($columna, self::TEXTO, true) ? $valor->format('d/m/Y') : $valor;
        }

        if (is_float($valor) && in_array($columna, self::TEXTO, true) && floor($valor) === $valor) {
            return (string) (int) $valor;
        }

        if (is_string($valor)) {
            return Normalizador::texto($valor);
        }

        return in_array($columna, self::TEXTO, true) ? (string) $valor : $valor;
    }

    /** Cabecera reducida a letras y números sin tildes: "Nº de póliza" => "nodepoliza". */
    private static function clave(string $texto): string
    {
        return preg_replace('/[^a-z0-9]/', '', Normalizador::claveCatalogo($texto));
    }
}
