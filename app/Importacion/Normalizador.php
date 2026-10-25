<?php

namespace App\Importacion;

use Illuminate\Support\Str;

/**
 * Normaliza los valores que llegan del Excel (y de las búsquedas) para que
 * un mismo dato escrito de formas distintas se guarde y se encuentre igual.
 */
class Normalizador
{
    /** DNI/NIE/CIF: mayúsculas, sin espacios, guiones ni puntos. */
    public static function identificacion(?string $valor): string
    {
        return Str::upper(preg_replace('/[\s.\-]+/u', '', (string) $valor));
    }

    /** Matrícula: mayúsculas, sin espacios ni guiones. */
    public static function matricula(?string $valor): string
    {
        return Str::upper(preg_replace('/[\s\-]+/u', '', (string) $valor));
    }

    /** Clave para comparar nombres de catálogo: minúsculas, sin tildes y con espacios simples. */
    public static function claveCatalogo(?string $valor): string
    {
        return Str::lower(Str::ascii((string) self::texto($valor)));
    }

    /** Código de catálogo a partir del nombre: mayúsculas, guiones bajos y máximo 20 caracteres. */
    public static function codigoCatalogo(string $nombre): string
    {
        return rtrim(Str::substr(Str::upper(Str::slug(Str::ascii($nombre), '_')), 0, 20), '_');
    }

    /** Texto sin espacios sobrantes; null si queda vacío. */
    public static function texto(?string $valor): ?string
    {
        $limpio = trim(preg_replace('/\s+/u', ' ', (string) $valor));

        return $limpio === '' ? null : $limpio;
    }
}
