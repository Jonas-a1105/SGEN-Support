<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\UseCases;

use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Equipment\Domain\Exceptions\EquipmentNotFoundException;
use Modules\Support\Domain\Enums\TicketStatus;

/**
 * Baja formal de activos (Módulo 11): precondiciones duras de throughput —
 * custodia activa cerrada, sin mantenimientos pendientes, sin tickets en
 * curso por planificar — antes de pasar el equipo a `de_baja` con motivo,
 * responsable, valor de recuperación y fecha/timestamp. El historial
 * permanece íntegro; la entidad nunca se destruye.
 */
final class DecommissionEquipmentUseCase
{
    public function __construct() {}

    public function execute(
        int $equipoId,
        string $motivo,
        ?float $valorRecuperacion,
        ?string $destino,
        ?string $nota,
        int $actorUserId
    ): void {
        DB::transaction(function () use ($equipoId, $motivo, $valorRecuperacion, $destino, $nota, $actorUserId): void {
            $equipo = DB::table('equipos')
                ->where('id', $equipoId)
                ->lockForUpdate()
                ->first(['id', 'estado', 'empleado_id', 'codigo_inventario']);

            if ($equipo === null) {
                throw EquipmentNotFoundException::withId($equipoId);
            }

            if ($equipo->estado === 'de_baja') {
                throw new DomainException("El equipo {$equipo->codigo_inventario} ya está dado de baja; la operación es irreversible.");
            }

            // Prerequisitos patrimoniales: fuera todas las precondiciones antes
            // de que el activo deje el ciclo operativo diario.
            $custodiaActiva = DB::table('custodias')
                ->where('equipo_id', $equipoId)
                ->whereNull('fecha_fin')
                ->exists();

            if ($custodiaActiva) {
                throw new DomainException('El activo aún tiene una custodia vigente: cierre la entrega actual antes de dar la baja.');
            }

            $mantenimientosPendientes = (int) DB::table('mantenimientos')
                ->where('equipo_id', $equipoId)
                ->where('estado', '!=', 'completado')
                ->count();

            if ($mantenimientosPendientes > 0) {
                throw new DomainException("El activo tiene {$mantenimientosPendientes} mantenimiento(s) sin completar: termine/cierre sus órdenes primero.");
            }

            $ticketsAbiertos = (int) DB::table('soportes')
                ->where('equipo_id', $equipoId)
                ->whereNotIn('estado', TicketStatus::finalValues())
                ->count();

            if ($ticketsAbiertos > 0) {
                throw new DomainException("El activo tiene {$ticketsAbiertos} ticket(s) abiertos: resuélvalos o transfírelos antes de la baja.");
            }

            DB::table('equipos')->where('id', $equipoId)->update([
                'estado' => 'de_baja',
                // El custodio queda en custodias.* (historia completa); el
                // campo operativo queda vacío porque el activo se retira.
                'empleado_id' => null,
                'motivo_baja' => $motivo,
                'valor_recuperacion' => $valorRecuperacion,
                'destino_baja' => $destino,
                'responsable_baja_id' => $actorUserId,
                'fecha_baja' => Carbon::now(),
                'nota_baja' => $nota,
                'version' => DB::raw('version + 1'),
                'updated_at' => Carbon::now(),
            ]);
        });
    }
}
