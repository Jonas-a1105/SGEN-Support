<?php

declare(strict_types=1);

namespace App\Infrastructure\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Maintenance\Application\DTOs\UpdateMaintenanceDTO;

final class UpdateMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('mantenimientos.manage');
    }

    public function rules(): array
    {
        return [
            'fecha' => 'nullable|date',
            'tipo_mantenimiento' => 'nullable|in:preventivo,correctivo,predictivo',
            'estado' => 'nullable|in:pendiente,en_proceso,completado,pospuesto,cancelado',
            'descripcion' => 'nullable|string|max:1000',
            'frecuencia' => 'nullable|in:unica,mensual,trimestral,semestral,anual',
            'proxima_fecha' => 'nullable|date',
            'costo' => 'nullable|numeric|min:0',
            'tecnico_id' => 'nullable|integer',
            'observaciones' => 'nullable|string|max:500',
            'duracion' => 'nullable|integer|min:1',
        ];
    }

    public function toDTO(): UpdateMaintenanceDTO
    {
        $validated = $this->validated();

        return new UpdateMaintenanceDTO(
            fecha: $validated['fecha'] ?? null,
            tipoMantenimiento: $validated['tipo_mantenimiento'] ?? null,
            estado: $validated['estado'] ?? null,
            descripcion: $validated['descripcion'] ?? null,
            frecuencia: $validated['frecuencia'] ?? null,
            proximaFecha: $validated['proxima_fecha'] ?? null,
            costo: isset($validated['costo']) ? (float) $validated['costo'] : null,
            tecnicoId: isset($validated['tecnico_id']) ? (int) $validated['tecnico_id'] : null,
            observaciones: $validated['observaciones'] ?? null,
            duracion: isset($validated['duracion']) ? (int) $validated['duracion'] : null
        );
    }
}
