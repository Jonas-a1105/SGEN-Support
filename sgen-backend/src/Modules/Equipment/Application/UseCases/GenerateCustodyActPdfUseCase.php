<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\UseCases;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;

final class GenerateCustodyActPdfUseCase
{
    public function __construct(
        private readonly EquipmentRepositoryInterface $repository
    ) {}

    public function execute(int $equipmentId): Response
    {
        $detail = $this->repository->getCompleteDetail($equipmentId);

        if ($detail === null) {
            abort(404, "Equipo #{$equipmentId} no encontrado.");
        }

        $employeeData = null;
        if ($detail->employeeId) {
            $employeeData = DB::table('empleados')->where('id', $detail->employeeId)->first([
                'nombre',
                'apellido',
                'cedula',
                'cargo',
                'email',
            ]);
        }

        $viewData = [
            'equipment' => $detail->toArray(),
            'employee' => $employeeData,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('equipment.custody-act-pdf', $viewData)
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Acta_Entrega_{$detail->inventoryCode}.pdf");
    }
}
