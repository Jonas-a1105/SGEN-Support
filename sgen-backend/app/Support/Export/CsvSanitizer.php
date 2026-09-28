<?php

declare(strict_types=1);

namespace App\Support\Export;

use Stringable;

/**
 * Neutraliza la inyección de fórmulas en exportaciones CSV: una celda que
 * comienza por =, +, -, @, tabulador o retorno de carro se interpreta como
 * fórmula por Excel/LibreOffice, así que se antepone un apóstrofo para que
 * el programa de hojas la trate como texto plano.
 */
final class CsvSanitizer
{
    private const INICIOS_PELIGROSOS = ['=', '+', '-', '@', "\t", "\r"];

    public static function cell(mixed $value): string
    {
        $texto = match (true) {
            $value === null => '',
            is_bool($value) => $value ? '1' : '0',
            is_scalar($value), $value instanceof Stringable => (string) $value,
            default => '',
        };

        if ($texto === '') {
            return '';
        }

        return in_array($texto[0], self::INICIOS_PELIGROSOS, true)
            ? "'".$texto
            : $texto;
    }
}
