<?php

declare(strict_types=1);

namespace Modules\Reports\Application\UseCases;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;

final readonly class GenerateTicketsPdfUseCase
{
    public function __construct(
        private ReportsRepositoryInterface $repository
    ) {}

    public function execute(array $filters): Response
    {
        $tickets = $this->repository->getTicketsData($filters);

        $pdf = Pdf::loadView('reports.tickets-pdf', [
            'tickets' => $tickets,
            'filters' => $filters,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_Tickets_' . date('Y-m-d') . '.pdf');
    }
}
