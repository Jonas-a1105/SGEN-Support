<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;
use Modules\Maintenance\Domain\Services\ChecklistValidator;
use Modules\Maintenance\Domain\Services\RecurrenceEngine;

/**
 * Completa una orden de trabajo (PASO de cierre del CMMS):
 *
 * 1. Valida el checklist: si hay tareas pendientes, exige flag explÃ­cito
 *    de omisiÃ³n con justificaciÃ³n (regla del documento maestro).
 * 2. Cierra la orden con garantÃ­a de reparaciÃ³n de 90 dÃ­as.
 * 3. Si es recurrente, materializa automÃ¡ticamente la SIGUIENTE orden
 *    de la serie con serie_padre_id encadenado â€” nadie la crea a mano.
 */
final class CompleteMaintenanceUseCase
{
    private const GARANTIA_DIAS = 90;

    public function __construct(
        private readonly MaintenanceRepositoryInterface $repository,
        private readonly ChecklistValidator $checklistValidator,
        private readonly RecurrenceEngine $recurrenceEngine
    ) {}

    public function execute(
        int $id,
        ?string $observations = null,
        ?float $cost = null,
        ?string $checklistRaw = null,
        bool $omitPendingTasks = false,
        ?string $omissionJustification = null
    ): bool {
        $mantenimiento = DB::table('mantenimientos')->where('id', $id)->first();

        if ($mantenimiento === null) {
            throw new \DomainException("La orden de trabajo #{$id} no existe.");
        }

        if ($mantenimiento->estado === 'completado') {
            throw new \DomainException('La orden ya estÃ¡ completada.');
        }

        if ($mantenimiento->estado === 'cancelado') {
            throw new \DomainException('No se puede completar una orden cancelada.');
        }

        $checklist = $this->checklistValidator->normalize($checklistRaw ?? $mantenimiento->checklist);
        $pending = $this->checklistValidator->pendingTasks($checklist);

        if ($pending !== [] && ! $omitPendingTasks) {
            throw new \DomainException(
                'El checklist tiene tareas pendientes: '.implode(', ', $pending).
                '. ComplÃ©talas o omÃ­telas con justificaciÃ³n.'
            );
        }

        if ($pending !== [] && $omitPendingTasks && ($omissionJustification === null || trim($omissionJustification) === '')) {
            throw new \InvalidArgumentException('La omisiÃ³n de tareas pendientes exige una justificaciÃ³n.');
        }

        if ($pending !== [] && $omitPendingTasks) {
            $omissionNote = 'Tareas omitidas ('.now()->format('d/m/Y H:i').'): '
                .implode(', ', $pending)
                .' â€” JustificaciÃ³n: '.trim($omissionJustification);
            $observations = $observations !== null && $observations !== ''
                ? $observations.' | '.$omissionNote
                : $omissionNote;
        }

        return DB::transaction(function () use ($id, $mantenimiento, $observations, $cost): bool {
            $nextDate = $this->recurrenceEngine->nextDate(
                (string) $mantenimiento->frecuencia,
                Carbon::parse($mantenimiento->fecha)
            );

            $completed = $this->repository->complete(
                $id,
                $observations,
                $cost,
                Carbon::now()->addDays(self::GARANTIA_DIAS)->toDateString()
            );

            if (! $completed) {
                return false;
            }

            if ($nextDate !== null) {
                DB::table('mantenimientos')->where('id', $id)->update([
                    'proxima_fecha' => $nextDate->toDateString(),
                ]);
            }

            $this->materializeNextInSeries($id, $mantenimiento, $nextDate);

            // Restaurar equipo a operativo si estaba en reparaciÃ³n
            $equipo = DB::table('equipos')->where('id', $mantenimiento->equipo_id)->first();
            if ($equipo && $equipo->estado === 'en_reparacion') {
                DB::table('equipos')->where('id', $mantenimiento->equipo_id)->update([
                    'estado' => $equipo->empleado_id ? 'en_uso' : 'disponible',
                    'updated_at' => Carbon::now(),
                ]);
            }

            return true;
        });
    }

    /**
     * Genera la siguiente orden de la serie recurrente si procede.
     */
    private function materializeNextInSeries(int $id, object $mantenimiento, ?Carbon $nextDate = null): void
    {
        $calculatedDate = $nextDate ?? $this->recurrenceEngine->nextDate(
            (string) $mantenimiento->frecuencia,
            Carbon::parse($mantenimiento->fecha)
        );

        if ($calculatedDate === null) {
            return;
        }

        $yaExiste = DB::table('mantenimientos')
            ->where('serie_padre_id', $id)
            ->exists();

        if ($yaExiste) {
            return;
        }

        DB::table('mantenimientos')->insert([
            'equipo_id' => $mantenimiento->equipo_id,
            'fecha' => $calculatedDate->format('Y-m-d H:i:s'),
            'frecuencia' => $mantenimiento->frecuencia,
            'proxima_fecha' => $this->recurrenceEngine
                ->nextDate((string) $mantenimiento->frecuencia, $calculatedDate)
                ?->toDateString(),
            'descripcion' => $mantenimiento->descripcion,
            'tipo_mantenimiento' => $mantenimiento->tipo_mantenimiento,
            'estado' => 'pendiente',
            'checklist' => $mantenimiento->checklist,
            'tecnico_id' => $mantenimiento->tecnico_id,
            'serie_padre_id' => $id,
            'costo' => 0.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
