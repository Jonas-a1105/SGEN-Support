<?php

declare(strict_types=1);

namespace Modules\Audit\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Audit\Domain\Models\AuditLogEntry;
use Modules\Audit\Domain\Ports\AuditLogRepositoryInterface;

final class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function save(AuditLogEntry $logEntry): int
    {
        $id = DB::table('bitacora_acciones')->insertGetId([
            'usuario_id' => $logEntry->userId(),
            'username' => $logEntry->username(),
            'accion' => $logEntry->action(),
            'enlace_tipo' => $logEntry->entityType(),
            'enlace_id' => $logEntry->entityId(),
            'entidad' => $logEntry->entityType(),
            'entidad_id' => $logEntry->entityId(),
            'datos_anteriores' => $logEntry->oldData() ? json_encode($logEntry->oldData()) : null,
            'datos_nuevos' => $logEntry->newData() ? json_encode($logEntry->newData()) : null,
            'ip_address' => $logEntry->ipAddress(),
            'created_at' => $logEntry->createdAt()?->format('Y-m-d H:i:s'),
        ]);

        return (int) $id;
    }

    public function findByEntity(string $entityType, int $entityId): array
    {
        return DB::table('bitacora_acciones')
            ->where('entidad', $entityType)
            ->where('entidad_id', $entityId)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn($row) => [
                'id' => (int) $row->id,
                'usuario_id' => (int) $row->usuario_id,
                'username' => $row->username,
                'accion' => $row->accion,
                'entidad' => $row->entidad,
                'entidad_id' => (int) $row->entidad_id,
                'created_at' => $row->created_at,
                'ip_address' => $row->ip_address,
            ])
            ->all();
    }

    public function findByUser(int $userId, int $limit = 50): array
    {
        return DB::table('bitacora_acciones')
            ->where('usuario_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'id' => (int) $row->id,
                'usuario_id' => (int) $row->usuario_id,
                'username' => $row->username,
                'accion' => $row->accion,
                'entidad' => $row->entidad,
                'entidad_id' => (int) $row->entidad_id,
                'created_at' => $row->created_at,
            ])
            ->all();
    }

    public function findRecent(int $limit = 50): array
    {
        return DB::table('bitacora_acciones')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'id' => (int) $row->id,
                'usuario_id' => (int) $row->usuario_id,
                'username' => $row->username,
                'accion' => $row->accion,
                'entidad' => $row->entidad,
                'entidad_id' => (int) $row->entidad_id,
                'created_at' => $row->created_at,
                'ip_address' => $row->ip_address,
            ])
            ->all();
    }

    public function countAll(): int
    {
        return (int) DB::table('bitacora_acciones')->count();
    }

    public function countByUser(int $userId): int
    {
        return (int) DB::table('bitacora_acciones')->where('usuario_id', $userId)->count();
    }

    public function countByEntity(string $entityType, int $entityId): int
    {
        return (int) DB::table('bitacora_acciones')
            ->where('entidad', $entityType)
            ->where('entidad_id', $entityId)
            ->count();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function listActions(array $filters = [], int $limit = 100): array
    {
        $query = DB::table('bitacora_acciones');

        if (!empty($filters['search'])) {
            $search = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', $search)
                  ->orWhere('accion', 'like', $search)
                  ->orWhere('enlace_tipo', 'like', $search)
                  ->orWhere('entidad', 'like', $search);
            });
        }

        if (!empty($filters['entity']) && $filters['entity'] !== 'all') {
            $entity = (string) $filters['entity'];
            $query->where(function ($q) use ($entity) {
                $q->where('entidad', $entity)
                  ->orWhere('enlace_tipo', $entity);
            });
        }

        return $query->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'id' => (int) $row->id,
                'usuario_id' => $row->usuario_id ? (int) $row->usuario_id : null,
                'username' => $row->username ?? 'Sistema',
                'accion' => $row->accion,
                'entidad' => $row->entidad ?? $row->enlace_tipo ?? 'general',
                'entidad_id' => $row->entidad_id ? (int) $row->entidad_id : ($row->enlace_id ? (int) $row->enlace_id : null),
                'enlace_tipo' => $row->enlace_tipo,
                'enlace_id' => $row->enlace_id ? (int) $row->enlace_id : null,
                'datos_anteriores' => $row->datos_anteriores ? json_decode((string) $row->datos_anteriores, true) : null,
                'datos_nuevos' => $row->datos_nuevos ? json_decode((string) $row->datos_nuevos, true) : null,
                'created_at' => (string) $row->created_at,
                'ip_address' => $row->ip_address ?? '127.0.0.1',
            ])
            ->all();
    }
}
