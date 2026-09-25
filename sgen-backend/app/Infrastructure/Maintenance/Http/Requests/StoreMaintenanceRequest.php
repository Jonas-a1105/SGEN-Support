<?php

declare(strict_types=1);

namespace App\Infrastructure\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Maintenance\Application\DTOs\CreateMaintenanceDTO;

final class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('mantenimientos.manage');
    }

    public function rules(): array
    {
        return [
            'equipo_id' => 'required|integer|exists:equipos,id',
            'fecha' => 'required|date',
            'tipo_mantenimiento' => 'required|in:preventivo,correctivo,predictivo',
            'descripcion' => 'required|string|max:1000',
            'frecuencia' => 'nullable|in:unica,mensual,trimestral,semestral,anual',
            'proxima_fecha' => 'nullable|date',
            'costo' => 'nullable|numeric|min:0',
            'tecnico_id' => 'nullable|integer',
            'observaciones' => 'nullable|string|max:500',
            'duracion' => 'nullable|integer|min:1',
        ];
    }

    public function toDTO(): CreateMaintenanceDTO
    {
        $validated = $this->validated();

        return new CreateMaintenanceDTO(
            equipoId: (int) $validated['equipo_id'],
            fecha: (string) $validated['fecha'],
            tipoMantenimiento: (string) $validated['tipo_mantenimiento'],
            descripcion: (string) $validated['descripcion'],
            frecuencia: $validated['frecuencia'] ?? 'unica',
            proximaFecha: $validated['proxima_fecha'] ?? null,
            costo: isset($validated['costo']) ? (float) $validated['costo'] : null,
            tecnicoId: isset($validated['tecnico_id']) ? (int) $validated['tecnico_id'] : null,
            observaciones: $validated['observaciones'] ?? null,
            duracion: isset($validated['duracion']) ? (int) $validated['duracion'] : null
        );
    }
}
