<?php

declare(strict_types=1);

namespace App\Infrastructure\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Reports\Application\UseCases\ExportTicketsExcelUseCase;
use Modules\Reports\Application\UseCases\GenerateInventoryPdfUseCase;
use Modules\Reports\Application\UseCases\GenerateMaintenancePdfUseCase;
use Modules\Reports\Application\UseCases\GeneratePerformancePdfUseCase;
use Modules\Reports\Application\UseCases\GenerateTicketsPdfUseCase;
use Modules\Reports\Application\UseCases\GetReportsFormDataUseCase;

final class ReportsController extends Controller
{
    public function index(GetReportsFormDataUseCase $useCase): InertiaResponse
    {
        return Inertia::render('Reports/Index', $useCase->execute());
    }

    public function ticketsPdf(Request $request, GenerateTicketsPdfUseCase $useCase): Response
    {
        $filters = $request->only(['fecha_inicio', 'fecha_fin', 'estado', 'categoria_id', 'prioridad']);

        return $useCase->execute($filters);
    }

    public function ticketsExcel(Request $request, ExportTicketsExcelUseCase $useCase): Response
    {
        $filters = $request->only(['fecha_inicio', 'fecha_fin', 'estado', 'categoria_id', 'prioridad']);

        return $useCase->execute($filters);
    }

    public function inventoryPdf(GenerateInventoryPdfUseCase $useCase): Response
    {
        return $useCase->execute();
    }

    public function maintenancePdf(GenerateMaintenancePdfUseCase $useCase): Response
    {
        return $useCase->execute();
    }

    public function performancePdf(GeneratePerformancePdfUseCase $useCase): Response
    {
        return $useCase->execute();
    }
}
