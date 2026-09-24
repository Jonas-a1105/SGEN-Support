<?php

declare(strict_types=1);

namespace Modules\Audit\Application\Services;

final class AuditCsvExporter
{
    /**
     * @param resource $handle
     * @param array<int, array<string, mixed>> $sessions
     */
    public static function write($handle, array $sessions): void
    {
        fputcsv($handle, ['ID', 'Usuario ID', 'Usuario', 'Inicio', 'Fin', 'Duración', 'Estado']);
        foreach ($sessions as $s) {
            fputcsv($handle, [
                $s['id'],
                $s['user_id'],
                $s['username'],
                $s['start_time'],
                $s['end_time'] ?? 'En curso',
                $s['duration'],
                $s['status'],
            ]);
        }
        fclose($handle);
    }
}
