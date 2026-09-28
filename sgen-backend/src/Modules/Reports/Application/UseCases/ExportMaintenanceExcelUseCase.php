<?php

declare(strict_types=1);

namespace Modules\Reports\Application\UseCases;

use App\Support\Export\CsvSanitizer;
use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'Content-Disposition' => 'attachment; filename="Reporte_Mantenimientos_'.date('Y-m-d').'.csv"',
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
                    CsvSanitizer::cell('#M-'.$order->id),
                    CsvSanitizer::cell($order->equipo_codigo ?? 'N/A'),
                    CsvSanitizer::cell(ucfirst((string) ($order->equipo_tipo ?? 'Genérico'))),
                    CsvSanitizer::cell(ucfirst((string) ($order->tipo_mantenimiento ?? 'Preventivo'))),
                    CsvSanitizer::cell(ucfirst(str_replace('_', ' ', (string) ($order->estado ?? 'Pendiente')))),
                    CsvSanitizer::cell(ucfirst((string) ($order->frecuencia ?? 'Única'))),
                    CsvSanitizer::cell($order->fecha ?? 'Sin fecha'),
                    CsvSanitizer::cell($order->proxima_fecha ?? 'N/A'),
                    CsvSanitizer::cell($order->tecnico_nombre ?? 'Sin asignar'),
                    CsvSanitizer::cell(number_format((float) ($order->costo ?? 0), 2, '.', '')),
                    CsvSanitizer::cell($order->descripcion ?? ''),
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
