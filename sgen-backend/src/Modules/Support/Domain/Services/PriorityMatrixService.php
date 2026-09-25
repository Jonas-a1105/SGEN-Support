<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Services;

use Modules\Support\Domain\Enums\TicketPriority;

/**
 * Matriz ITIL de Prioridad de Soporte:
 * Deriva la prioridad a partir de Impacto y Urgencia, aplicando reglas de negocio como el flag VIP.
 */
final class PriorityMatrixService
{
    /**
     * Deriva la prioridad del ticket a partir de la matriz Impacto x Urgencia.
     *
     * @param string $impacto 'bajo' | 'medio' | 'alto'
     * @param string $urgencia 'baja' | 'media' | 'alta'
     * @param bool $isVip Si el solicitante tiene condición VIP, la urgencia mínima se eleva a 'media'.
     */
    public static function derive(string $impacto, string $urgencia, bool $isVip = false): TicketPriority
    {
        $impacto = strtolower(trim($impacto));
        $urgencia = strtolower(trim($urgencia));

        if ($isVip && $urgencia === 'baja') {
            $urgencia = 'media';
        }

        return match ([$impacto, $urgencia]) {
            ['alto', 'alta'] => TicketPriority::CRITICA,
            ['alto', 'media'], ['medio', 'alta'] => TicketPriority::ALTA,
            ['alto', 'baja'], ['medio', 'media'], ['bajo', 'alta'] => TicketPriority::MEDIA,
            default => TicketPriority::BAJA,
        };
    }
}
