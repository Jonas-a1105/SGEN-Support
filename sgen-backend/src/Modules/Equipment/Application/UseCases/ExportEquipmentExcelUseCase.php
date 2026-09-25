<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\UseCases;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;

final readonly class ExportEquipmentExcelUseCase
{
    public function __construct(
        private EquipmentRepositoryInterface $repository
    ) {}

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): StreamedResponse
    {
        $items = $this->repository->list($filters);

        $filename = 'Equipos_Tecnologicos_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($items) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 para Excel

            fputcsv($output, [
                'ID Interno',
                'Código Patrimonial',
                'Tipo de Activo',
                'Modelo / Nombre',
                'Número de Serie',
                'Departamento',
                'Ubicación Física',
                'Asignado a',
                'Dirección IP',
                'Estado Operativo',
            ]);

            foreach ($items as $item) {
                fputcsv($output, [
                    $item->numericId,
                    $item->id,
                    $item->type,
                    $item->name,
                    $item->serialNumber ?? 'S/N',
                    $item->dept ?? 'No Asignado',
                    $item->location ?? 'N/D',
                    $item->assignedTo ?? 'Sin Asignar',
                    $item->ipAddress ?? 'N/D',
                    $item->status,
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
