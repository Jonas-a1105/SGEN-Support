<?php

declare(strict_types=1);

namespace App\Infrastructure\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CompleteMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'observaciones' => 'nullable|string|max:2000',
            'costo' => 'nullable|numeric|min:0',
            'checklist' => 'nullable|array',
            'checklist.*.tarea' => 'required_with:checklist|string|max:255',
            'checklist.*.completada' => 'nullable|boolean',
            'checklist.*.observacion' => 'nullable|string|max:500',
            'omitir_pendientes' => 'nullable|boolean',
            'justificacion_omision' => 'nullable|string|max:1000',
        ];
    }

    /**
     * @return array<int, array{tarea: string, completada: bool, observacion: ?string}>|null
     */
    public function checklistArray(): ?array
    {
        $checklist = $this->input('checklist');

        if (! is_array($checklist)) {
            return null;
        }

        $tasks = [];
        foreach ($checklist as $entry) {
            if (! is_array($entry) || ! isset($entry['tarea'])) {
                continue;
            }

            $tasks[] = [
                'tarea' => (string) $entry['tarea'],
                'completada' => (bool) ($entry['completada'] ?? false),
                'observacion' => isset($entry['observacion']) && $entry['observacion'] !== ''
                    ? (string) $entry['observacion']
                    : null,
            ];
        }

        return $tasks;
    }
}
