<?php

declare(strict_types=1);

namespace Modules\Reports\Application\UseCases;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;

final readonly class ExportMaintenanceExcelUseCase
{
    public function __construct(
        private ReportsRepositoryInterface $repository
    ) {}

    public function execute(): StreamedResponse
    {
        $orders = $this->repository->getMaintenanceData();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="Reporte_Mantenimientos_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, [
                'ID Orden',
                'Equipo Código',
                'Tipo Equipo',
                'Tipo Mantenimiento',
                'Estado',
                'Frecuencia',
                'Fecha Programada',
                'Próxima Fecha',
                'Técnico Asignado',
                'Costo ($)',
                'Descripción',
            ]);

            foreach ($orders as $order) {
                fputcsv($output, [
                    '#M-' . $order->id,
                    $order->equipo_codigo ?? 'N/A',
                    ucfirst((string) ($order->equipo_tipo ?? 'Genérico')),
                    ucfirst((string) ($order->tipo_mantenimiento ?? 'Preventivo')),
                    ucfirst(str_replace('_', ' ', (string) ($order->estado ?? 'Pendiente'))),
                    ucfirst((string) ($order->frecuencia ?? 'Única')),
                    $order->fecha ?? 'Sin fecha',
                    $order->proxima_fecha ?? 'N/A',
                    $order->tecnico_nombre ?? 'Sin asignar',
                    number_format((float) ($order->costo ?? 0), 2, '.', ''),
                    $order->descripcion ?? '',
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
