<?php

declare(strict_types=1);

namespace Modules\Reports\Application\UseCases;

use Illuminate\Http\Response;
use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;

final readonly class ExportTicketsExcelUseCase
{
    public function __construct(
        private ReportsRepositoryInterface $repository
    ) {}

    public function execute(array $filters): Response
    {
        $tickets = $this->repository->getTicketsData($filters);

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="Reporte_Tickets_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($tickets) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, ['ID', 'Título', 'Fecha', 'Estado', 'Prioridad', 'Categoría', 'Técnico', 'Departamento', 'Equipo', 'Fecha Cierre']);

            foreach ($tickets as $ticket) {
                fputcsv($output, [
                    'T-' . $ticket->id,
                    $ticket->titulo,
                    $ticket->fecha,
                    ucfirst(str_replace('_', ' ', (string) $ticket->estado)),
                    ucfirst((string) $ticket->prioridad),
                    $ticket->categoria_nombre ?? 'Sin categoría',
                    trim(($ticket->tech_nombre ?? '') . ' ' . ($ticket->tech_apellido ?? '')) ?: 'Sin asignar',
                    $ticket->depto_nombre ?? 'Sin departamento',
                    $ticket->equipo_codigo ?? 'N/A',
                    $ticket->fecha_cierre ?? 'Pendiente',
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
