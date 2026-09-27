<?php

declare(strict_types=1);

namespace Modules\Audit\Domain\Services;

/**
 * Máscara de secretos para la bitácora (checklist #44).
 *
 * El log forense jamás registra valores que comprometan a usuarios o
 * sistemas (contraseñas, tokens, claves, firmas). Coincidencia por nombre
 * de clave — insensible a mayúsculas y a separadores — aplicada
 * recursivamente a todo el payload antes de persistir.
 */
final class SensitiveDataMasker
{
    private const MASCARA = '••• (oculto)';

    /**
     * Patrón de claves sensibles: nombre completo o sufijo tipo
     * `user.password`, `firma_base64`, `api_key`, `remember_token`, etc.
     */
    private const PATRON = '/(^|[_\-\.])(password|passwd|pwd|secret|token|apikey|api_key|api-key|access[_\-]?key|private[_\-]?key|pwd_hash|firma|firma[_\-]?base64|signature|credit[_\-]?card|card[_\-]?number|cvv|pin)([_\-\.]|$)/i';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function maskArray(array $data): array
    {
        $salida = [];

        foreach ($data as $clave => $valor) {
            $esClaveSensible = is_string($clave) && preg_match(self::PATRON, $clave) === 1;

            if ($esClaveSensible) {
                $salida[$clave] = self::MASCARA;
            } elseif (is_array($valor)) {
                $salida[$clave] = self::maskArray($valor);
            } elseif (is_string($valor) && strlen($valor) > 4000) {
                // Las firmas binarias/base64 nunca deben inflar el log (regla de archivos).
                $salida[$clave] = self::MASCARA.' [contenido binario]';
            } else {
                $salida[$clave] = $valor;
            }
        }

        return $salida;
    }
}
