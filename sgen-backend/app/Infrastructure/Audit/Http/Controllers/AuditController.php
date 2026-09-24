<?php

declare(strict_types=1);

namespace App\Infrastructure\Audit\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Audit\Application\UseCases\GetAuditDashboardUseCase;
use Modules\Audit\Application\UseCases\GetSessionDetailUseCase;
use Modules\Audit\Domain\Ports\AuditLogRepositoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AuditController extends Controller
{
    public function index(
        Request $request,
        GetAuditDashboardUseCase $useCase,
        AuditLogRepositoryInterface $logRepo
    ): Response {
        $filters = $request->only(['search', 'user_id', 'entity', 'tab']);
        $data = $useCase->execute($filters);
        $data['actions'] = $logRepo->listActions($filters);
        $data['active_tab'] = $request->input('tab', 'sesiones');

        return Inertia::render('Audit/Index', $data);
    }

    public function show(int $id, GetSessionDetailUseCase $useCase): JsonResponse
    {
        $session = $useCase->execute($id);
        abort_if($session === null, 404, 'Sesión no encontrada.');

        return response()->json($session->toArray());
    }

    public function export(Request $request, GetAuditDashboardUseCase $useCase): StreamedResponse
    {
        $data = $useCase->execute($request->only(['search', 'user_id']));

        return response()->streamDownload(
            fn () => \Modules\Audit\Application\Services\AuditCsvExporter::write(fopen('php://output', 'w'), $data['sessions']),
            'auditoria_sesiones_' . date('Y-m-d_His') . '.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    public function exportBitacora(Request $request, AuditLogRepositoryInterface $logRepo): StreamedResponse
    {
        $filters = $request->only(['search', 'entity']);
        $actions = $logRepo->listActions($filters, 1000);

        return response()->streamDownload(
            function () use ($actions) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
                fputcsv($handle, ['ID', 'Usuario', 'Acción Realizada', 'Módulo / Entidad', 'Referencia ID', 'Dirección IP', 'Fecha y Hora']);
                foreach ($actions as $act) {
                    fputcsv($handle, [
                        $act['id'],
                        $act['username'],
                        $act['accion'],
                        ucfirst((string) $act['entidad']),
                        $act['entidad_id'] ?? '',
                        $act['ip_address'],
                        $act['created_at'],
                    ]);
                }
                fclose($handle);
            },
            'bitacora_movimientos_' . date('Y-m-d_His') . '.csv',
            ['Content-Type' => 'text/csv; charset=utf-8']
        );
    }
}
