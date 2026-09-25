<?php

declare(strict_types=1);

namespace Modules\Audit\Application\Services;

use Modules\Audit\Domain\Models\AuditLogEntry;
use Modules\Audit\Domain\Ports\AuditLogRepositoryInterface;

/**
 * Punto ÚNICO de escritura de la bitácora de acciones.
 *
 * Antes de este servicio, los INSERT a bitacora_acciones estaban duplicados
 * en controladores, repositorios y comandos (y podían fallar en silencio).
 * Ahora toda escritura de auditoría pasa por aquí: un fallo se reporta al
 * log y nunca interrumpe la operación de negocio, pero jamás desaparece.
 */
final class BitacoraLogger
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogs
    ) {}

    /**
     * @param  array<string, mixed>|null  $datosAnteriores
     * @param  array<string, mixed>|null  $datosNuevos
     */
    public function record(
        string $accion,
        string $entidad,
        int $entidadId,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        ?int $usuarioId = null,
        ?string $username = null,
        ?string $ip = null,
    ): void {
        try {
            $entry = AuditLogEntry::create(
                userId: (int) ($usuarioId ?? auth()->id() ?? 0),
                username: $username ?? ((string) (auth()->user()->username ?? 'sistema')),
                action: $accion,
                entityType: $entidad,
                entityId: $entidadId,
                oldData: $datosAnteriores,
                newData: $datosNuevos,
                ipAddress: $ip ?? (string) (request()->ip() ?: '127.0.0.1'),
            );

            $this->auditLogs->save($entry);
        } catch (\Throwable $e) {
            // La bitácora es evidencia forense: un fallo jamás pasa en silencio.
            report($e);
        }
    }
}
