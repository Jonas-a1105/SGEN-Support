<?php

declare(strict_types=1);

namespace Modules\Reports\Application\UseCases;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;

final readonly class GenerateInventoryPdfUseCase
{
    public function __construct(
        private ReportsRepositoryInterface $repository
    ) {}

    public function execute(): Response
    {
        $items = $this->repository->getInventoryData();

        $pdf = Pdf::loadView('reports.inventory-pdf', [
            'items' => $items,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_Inventario_' . date('Y-m-d') . '.pdf');
    }
}
