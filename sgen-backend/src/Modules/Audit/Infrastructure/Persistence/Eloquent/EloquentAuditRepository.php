<?php

declare(strict_types=1);

namespace Modules\Audit\Infrastructure\Persistence\Eloquent;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Audit\Domain\Ports\AuditRepositoryInterface;

final class EloquentAuditRepository implements AuditRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<array<string, mixed>>
     */
    public function listSessions(array $filters = []): array
    {
        $query = DB::table('sesiones_log');

        if (!empty($filters['search'])) {
            $search = '%' . trim((string) $filters['search']) . '%';
            $query->where('username', 'like', $search);
        }

        if (!empty($filters['user_id'])) {
            $query->where('usuario_id', (int) $filters['user_id']);
        }

        return $query->orderByDesc('id')->get()->all();
    }

    public function findSessionById(int $id): ?array
    {
        $row = DB::table('sesiones_log')->where('id', $id)->first();
        if ($row === null) {
            return null;
        }

        return (array) $row;
    }

    public function getKpis(): array
    {
        $activeSessions = DB::table('sesiones_log')->whereNull('fecha_fin')->count();
        $totalLogs = DB::table('sesiones_log')->count();

        // Calculate average duration of ended sessions
        $endedSessions = DB::table('sesiones_log')
            ->whereNotNull('fecha_fin')
            ->select('fecha_inicio', 'fecha_fin')
            ->limit(50)
            ->get();

        $totalMinutes = 0;
        $count = 0;
        foreach ($endedSessions as $session) {
            $start = new DateTimeImmutable((string) $session->fecha_inicio);
            $end = new DateTimeImmutable((string) $session->fecha_fin);
            $diffMinutes = ($end->getTimestamp() - $start->getTimestamp()) / 60;
            if ($diffMinutes > 0) {
                $totalMinutes += $diffMinutes;
                $count++;
            }
        }

        $avgMin = $count > 0 ? (int) round($totalMinutes / $count) : 45;

        return [
            'active_sessions' => $activeSessions,
            'avg_duration' => "{$avgMin} min",
            'total_logs' => $totalLogs,
        ];
    }
}
