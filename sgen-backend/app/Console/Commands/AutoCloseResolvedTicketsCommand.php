<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Support\Domain\Enums\TicketStatus;

final class AutoCloseResolvedTicketsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sgen:autocerrar-tickets {--dias=7 : Número de días en estado resuelto antes de autocierre}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cierra automáticamente los tickets en estado resuelto tras N días de inactividad sin reapertura';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dias = max(1, (int) $this->option('dias'));
        $threshold = Carbon::now()->subDays($dias);

        $this->info("Buscando tickets resueltos antes del {$threshold->format('Y-m-d H:i:s')} ({$dias} días)...");

        $tickets = DB::table('soportes')
            ->where('estado', TicketStatus::RESUELTO->value)
            ->where(function ($query) use ($threshold) {
                $query->where('fecha_resolucion', '<=', $threshold)
                    ->orWhere(function ($q) use ($threshold) {
                        $q->whereNull('fecha_resolucion')
                            ->where('updated_at', '<=', $threshold);
                    });
            })
            ->get(['id', 'titulo', 'estado', 'empleado_id', 'usuario_creacion_id']);

        if ($tickets->isEmpty()) {
            $this->info('No hay tickets resueltos pendientes de autocierre.');

            return self::SUCCESS;
        }

        $closedCount = 0;
        $now = Carbon::now();

        foreach ($tickets as $ticket) {
            // Defensa en profundidad: el comando jamás viola el ciclo de vida
            // del dominio. Solo RESUELTO → CERRADO es una transición legal.
            $currentStatus = TicketStatus::tryFromString((string) $ticket->estado);

            if (! $currentStatus->canTransitionTo(TicketStatus::CERRADO)) {
                $this->warn("  · Ticket #T-{$ticket->id} omitido: transición {$currentStatus->label()} → Cerrado no permitida.");

                continue;
            }

            DB::transaction(function () use ($ticket, $now, $dias, &$closedCount) {
                DB::table('soportes')
                    ->where('id', $ticket->id)
                    ->update([
                        'estado' => TicketStatus::CERRADO->value,
                        'fecha_cierre' => $now,
                        'updated_at' => $now,
                    ]);

                $systemUser = null;
                if ($ticket->usuario_creacion_id) {
                    $systemUser = DB::table('usuarios')->where('id', $ticket->usuario_creacion_id)->first();
                }
                if ($systemUser === null) {
                    $systemUser = DB::table('usuarios')->orderBy('id')->first();
                }

                $systemUserId = $systemUser?->id;
                $systemUsername = $systemUser->username ?? 'sistema';

                // Comentario automático del sistema
                if ($systemUserId !== null) {
                    DB::table('ticket_comentarios')->insert([
                        'ticket_id' => $ticket->id,
                        'usuario_id' => $systemUserId,
                        'comentario' => "Ticket cerrado automáticamente por el sistema tras cumplir la ventana de {$dias} días en estado resuelto sin observaciones del solicitante.",
                        'es_interno' => false,
                        'fecha' => $now,
                    ]);
                }

                // Notificación al solicitante o creador
                if ($systemUserId !== null) {
                    DB::table('notificaciones')->insert([
                        'usuario_id' => $systemUserId,
                        'tipo' => 'ticket_autocierre',
                        'titulo' => 'Ticket cerrado automáticamente',
                        'mensaje' => "El ticket #T-{$ticket->id} ({$ticket->titulo}) ha sido cerrado automáticamente tras {$dias} días resuelto.",
                        'enlace' => "/soportes/{$ticket->id}",
                        'icono' => 'bi-check-circle',
                        'leido' => false,
                        'read_at' => null,
                        'created_at' => $now,
                    ]);
                }

                // Bitácora de auditoría
                DB::table('bitacora_acciones')->insert([
                    'usuario_id' => $systemUserId,
                    'username' => $systemUsername,
                    'accion' => 'autocierre_ticket',
                    'entidad' => 'soporte',
                    'entidad_id' => $ticket->id,
                    'enlace_tipo' => 'soporte',
                    'enlace_id' => $ticket->id,
                    'datos_nuevos' => json_encode([
                        'dias_espera' => $dias,
                        'motivo' => 'Autocierre programado según política de SLA y calidad',
                    ]),
                    'ip_address' => '127.0.0.1',
                    'created_at' => $now,
                ]);

                $closedCount++;
            });

            $this->line("  ✓ Ticket #T-{$ticket->id} autocerrado.");
        }

        $this->info("Autocierre completado: {$closedCount} ticket(s) cerrado(s) exitosamente.");

        return self::SUCCESS;
    }
}
