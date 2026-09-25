<?php

declare(strict_types=1);

namespace Modules\Reports\Application\UseCases;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;

final readonly class ExportInventoryExcelUseCase
{
    public function __construct(
        private ReportsRepositoryInterface $repository
    ) {}

    public function execute(): StreamedResponse
    {
        $items = $this->repository->getInventoryData();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="Reporte_Inventario_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($items) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, [
                'ID',
                'Código SKU',
                'Nombre del Artículo',
                'Categoría',
                'Stock Actual',
                'Stock Mínimo',
                'Unidad',
                'Precio Compra ($)',
                'Valuación Total ($)',
                'Ubicación',
                'Estado Stock',
            ]);

            foreach ($items as $item) {
                $stockActual = (int) ($item->stock_actual ?? 0);
                $stockMinimo = (int) ($item->stock_minimo ?? 0);
                $precioCompra = (float) ($item->valor_compra ?? 0.0);
                $valuacion = round($stockActual * $precioCompra, 2);
                $estadoStock = $stockActual <= $stockMinimo ? 'Bajo Stock' : 'Óptimo';

                fputcsv($output, [
                    $item->id,
                    $item->codigo,
                    $item->nombre,
                    $item->categoria_nombre ?? $item->categoria ?? 'General',
                    $stockActual,
                    $stockMinimo,
                    $item->unidad_medida ?? 'uds',
                    number_format($precioCompra, 2, '.', ''),
                    number_format($valuacion, 2, '.', ''),
                    $item->ubicacion ?? 'Almacén Central',
                    $estadoStock,
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
