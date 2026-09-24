<?php

declare(strict_types=1);

namespace Modules\Reports\Application\UseCases;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;

final readonly class GeneratePerformancePdfUseCase
{
    public function __construct(
        private ReportsRepositoryInterface $repository
    ) {}

    public function execute(): Response
    {
        $data = $this->repository->getPerformanceData();

        $pdf = Pdf::loadView('reports.performance-pdf', [
            'kpis' => $data['kpis'],
            'topTechnicians' => $data['topTechnicians'],
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Reporte_Rendimiento_' . date('Y-m-d') . '.pdf');
    }
}
