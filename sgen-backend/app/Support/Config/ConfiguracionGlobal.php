<?php

declare(strict_types=1);

namespace App\Support\Config;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Lectura cacheada de la configuración global persistida
 * (`configuracion_global`): parámetros operativos del negocio que la
 * administración ajusta sin desplegar código.
 *
 * Los valores se almacenan como texto; los accesores los proyectan al tipo
 * esperado. Si la tabla aún no existe (primeras migraciones, arranque en
 * frío) se devuelve silenciosamente el valor por defecto: la configuración
 * nunca debe derribar el sistema.
 */
final class ConfiguracionGlobal
{
    private const CACHE_PREFIX = 'config_global:';

    public static function obtener(string $clave, ?string $porDefecto = null): ?string
    {
        try {
            return Cache::rememberForever(
                self::CACHE_PREFIX.$clave,
                static fn (): ?string => self::leerDeBaseDeDatos($clave) ?? $porDefecto
            );
        } catch (Throwable) {
            return $porDefecto;
        }
    }

    public static function entero(string $clave, int $porDefecto): int
    {
        $valor = self::obtener($clave);
        $entero = $valor !== null ? filter_var($valor, FILTER_VALIDATE_INT) : false;

        return $entero !== false ? (int) $entero : $porDefecto;
    }

    public static function booleano(string $clave, bool $porDefecto): bool
    {
        $valor = self::obtener($clave);

        if ($valor === null) {
            return $porDefecto;
        }

        $filtrado = filter_var($valor, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $filtrado ?? $porDefecto;
    }

    /**
     * Actualiza una clave e invalida su caché en la misma operación.
     */
    public static function establecer(string $clave, ?string $valor, ?int $usuarioId = null): void
    {
        DB::table('configuracion_global')->updateOrInsert(
            ['clave' => $clave],
            [
                'valor' => $valor,
                'actualizado_por' => $usuarioId,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        Cache::forget(self::CACHE_PREFIX.$clave);
    }

    private static function leerDeBaseDeDatos(string $clave): ?string
    {
        $valor = DB::table('configuracion_global')->where('clave', $clave)->value('valor');

        return $valor !== null ? (string) $valor : null;
    }
}
