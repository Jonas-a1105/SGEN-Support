<?php

declare(strict_types=1);

namespace Modules\Support\Application\Ports;

use Modules\Support\Application\DTOs\TicketDetailDTO;

/**
 * Ensambla los datos listos para la vista del PDF de un ticket.
 * Devuelve un array plano con claves nombradas (ticket, asset, materials, solution, generatedAt).
 */
interface TicketPdfDataAssembler
{
    /**
     * @return array{
     *     ticket: array<string, mixed>,
     *     asset: array<string, mixed>,
     *     materials: array<int, array<string, mixed>>,
     *     solution: ?string,
     *     generatedAt: string
     * }
     */
    public function assemble(TicketDetailDTO $detail): array;
}
