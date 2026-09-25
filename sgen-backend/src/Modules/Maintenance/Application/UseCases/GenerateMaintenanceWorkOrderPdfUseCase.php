<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class GenerateMaintenanceWorkOrderPdfUseCase
{
    public function __construct(
        private readonly MaintenanceRepositoryInterface $repository
    ) {
    }

    public function execute(int $maintenanceId): Response
    {
        $detail = $this->repository->findById($maintenanceId);

        if ($detail === null) {
            abort(404, "Mantenimiento #{$maintenanceId} no encontrado.");
        }

        $materiales = $this->repository->getMateriales($maintenanceId);

        $viewData = [
            'maintenance' => (array) $detail,
            'materiales' => $materiales,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('maintenance.work-order-pdf', $viewData)
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Orden_Mantenimiento_MAN-{$maintenanceId}.pdf");
    }
}
