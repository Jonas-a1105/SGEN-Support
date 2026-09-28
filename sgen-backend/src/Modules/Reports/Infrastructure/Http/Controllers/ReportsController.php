<?php

declare(strict_types=1);

namespace Modules\Reports\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Reports\Application\UseCases\ExportInventoryExcelUseCase;
use Modules\Reports\Application\UseCases\ExportMaintenanceExcelUseCase;
use Modules\Reports\Application\UseCases\ExportTicketsExcelUseCase;
use Modules\Reports\Application\UseCases\GenerateInventoryPdfUseCase;
use Modules\Reports\Application\UseCases\GenerateMaintenancePdfUseCase;
use Modules\Reports\Application\UseCases\GeneratePerformancePdfUseCase;
use Modules\Reports\Application\UseCases\GenerateTicketsPdfUseCase;
use Modules\Reports\Application\UseCases\GetReportsFormDataUseCase;
use Modules\Support\Domain\Enums\TicketStatus;
use Symfony\Component\HttpFoundation\Response;

final class ReportsController extends Controller
{
    public function index(GetReportsFormDataUseCase $useCase): InertiaResponse
    {
        return Inertia::render('Reports/Index', $useCase->execute());
    }

    public function ticketsPdf(Request $request, GenerateTicketsPdfUseCase $useCase): Response
    {
        $this->validateTicketFilters($request);

        $filters = $request->only(['fecha_inicio', 'fecha_fin', 'estado', 'categoria_id', 'prioridad']);

        return $useCase->execute($filters);
    }

    public function ticketsExcel(Request $request, ExportTicketsExcelUseCase $useCase): Response
    {
        $this->validateTicketFilters($request);

        $filters = $request->only(['fecha_inicio', 'fecha_fin', 'estado', 'categoria_id', 'prioridad']);

        return $useCase->execute($filters);
    }

    public function inventoryPdf(GenerateInventoryPdfUseCase $useCase): Response
    {
        return $useCase->execute();
    }

    public function inventoryExcel(ExportInventoryExcelUseCase $useCase): Response
    {
        return $useCase->execute();
    }

    public function maintenancePdf(GenerateMaintenancePdfUseCase $useCase): Response
    {
        return $useCase->execute();
    }

    public function maintenanceExcel(ExportMaintenanceExcelUseCase $useCase): Response
    {
        return $useCase->execute();
    }

    public function performancePdf(GeneratePerformancePdfUseCase $useCase): Response
    {
        return $useCase->execute();
    }

    /**
     * Filtros del reporte de tickets: se validan ANTES de consultar la base
     * de datos. Un valor con formato libre (p. ej. `fecha_inicio=abc` o un
     * estado inexistente) responde con error de validación, nunca con un 500
     * de SQL.
     */
    private function validateTicketFilters(Request $request): void
    {
        $request->validate([
            'fecha_inicio' => ['nullable', 'date_format:Y-m-d'],
            'fecha_fin' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_inicio'],
            'estado' => ['nullable', 'string', Rule::in(array_column(TicketStatus::cases(), 'value'))],
            'prioridad' => ['nullable', 'string', Rule::in(['baja', 'media', 'alta', 'critica'])],
            'categoria_id' => ['nullable', 'integer'],
            'categoria' => ['nullable', 'integer'],
            'departamento_id' => ['nullable', 'integer'],
            'departamento' => ['nullable', 'integer'],
        ]);
    }
}
