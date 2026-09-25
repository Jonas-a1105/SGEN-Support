<?php

declare(strict_types=1);

namespace Modules\Maintenance\Domain\Services;

/**
 * Valida el checklist JSON de una orden de trabajo al completarla.
 * Formato esperado: [{ "tarea": string, "completada": bool, "observacion"?: string }]
 */
final class ChecklistValidator
{
    /**
     * @param mixed $checklist Valor crudo de la columna checklist (JSON string o array ya decodificado).
     * @return array<int, array{tarea: string, completada: bool, observacion: ?string}>
     */
    public function normalize(mixed $checklist): array
    {
        if (is_string($checklist)) {
            $decoded = json_decode($checklist, true);
            $checklist = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($checklist)) {
            return [];
        }

        $tasks = [];
        foreach ($checklist as $entry) {
            if (!is_array($entry) || !isset($entry['tarea'])) {
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

    /**
     * @return string[] Nombres de las tareas pendientes.
     */
    public function pendingTasks(array $tasks): array
    {
        return array_values(
            array_filter(
                array_map(fn (array $t): ?string => $t['completada'] ? null : $t['tarea'], $tasks),
                fn (?string $name): bool => $name !== null
            )
        );
    }

    /**
     * @return bool True si el checklist está completo (o vacío = sin tareas definidas).
     */
    public function isComplete(array $tasks): bool
    {
        return $this->pendingTasks($tasks) === [];
    }
}
