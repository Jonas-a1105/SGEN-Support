<?php

declare(strict_types=1);

namespace Modules\Support\Infrastructure\Presentation;

use Modules\Support\Application\DTOs\TicketDetailDTO;
use Modules\Support\Application\Ports\TicketPdfDataAssembler;

final class PdfTicketDataAssembler implements TicketPdfDataAssembler
{
    public function assemble(TicketDetailDTO $detail): array
    {
        $ticket = $detail->toArray()['ticket'];

        return [
            'ticket' => $ticket,
            'asset' => $detail->asset,
            'materials' => $detail->materials,
            'solution' => $this->extractSolution($detail),
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];
    }

    private function extractSolution(TicketDetailDTO $detail): ?string
    {
        $raw = $detail->ticket['solution'] ?? null;

        return is_string($raw) && $raw !== '' ? $raw : null;
    }
}
