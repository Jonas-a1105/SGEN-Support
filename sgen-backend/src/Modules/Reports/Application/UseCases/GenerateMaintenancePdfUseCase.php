<?php

declare(strict_types=1);

namespace Modules\Reports\Application\UseCases;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;

final readonly class GenerateMaintenancePdfUseCase
{
    public function __construct(
        private ReportsRepositoryInterface $repository
    ) {}

    public function execute(): Response
    {
        $maintenances = $this->repository->getMaintenanceData();

        $pdf = Pdf::loadView('reports.maintenance-pdf', [
            'maintenances' => $maintenances,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_Mantenimientos_' . date('Y-m-d') . '.pdf');
    }
}
