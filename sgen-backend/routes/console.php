<?php

use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Modules\Maintenance\Domain\Services\RecurrenceEngine;
use Modules\Support\Domain\Enums\TicketStatus;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sla:verify', function () {
    $this->info('Verificando vencimientos de SLA de tickets...');

    $now = Carbon::now();
    $tickets = DB::table('soportes')
        ->whereNotIn('estado', TicketStatus::finalValues())
        ->whereNotNull('fecha_vencimiento')
        ->where('fecha_vencimiento', '<=', $now)
        ->where('notificacion_vencimiento_enviada', false)
        ->get();

    foreach ($tickets as $ticket) {
        $this->info("Ticket T-{$ticket->id} ha superado el tiempo de resoluciÃ³n SLA. Enviando alertas...");

        // Registrar notificaciÃ³n en base de datos
        DB::table('notificaciones')->insert([
            'usuario_id' => $ticket->empleado_id ?? 38,
            'tipo' => 'ticket_vencimiento',
            'titulo' => 'Alerta de SLA superado',
            'mensaje' => "El ticket T-{$ticket->id} ({$ticket->titulo}) ha excedido su tiempo de vencimiento.",
            'enlace' => "/soportes/{$ticket->id}",
            'icono' => 'bi-exclamation-triangle',
            'leido' => false,
            'read_at' => null,
            'created_at' => Carbon::now(),
        ]);

        DB::table('soportes')
            ->where('id', $ticket->id)
            ->update([
                'notificacion_vencimiento_enviada' => true,
                'notificacion_vencimiento_fecha' => Carbon::now(),
            ]);
    }

    $this->info('VerificaciÃ³n completada.');
})->purpose('Verifica los tiempos de SLA vencidos en tickets y genera notificaciones');

Artisan::command('mantenimientos:materializar', function () {
    $this->info('Materializando órdenes recurrentes faltantes...');

    $engine = new RecurrenceEngine;

    $candidates = DB::table('mantenimientos')
        ->whereNotIn('estado', ['cancelado'])
        ->whereNotNull('proxima_fecha')
        ->get(['id', 'equipo_id', 'fecha', 'frecuencia', 'proxima_fecha', 'descripcion', 'tipo_mantenimiento', 'checklist', 'tecnico_id']);

    $created = 0;
    foreach ($candidates as $m) {
        if (! $engine->shouldMaterializeNext((string) $m->frecuencia, $m->proxima_fecha)) {
            continue;
        }

        $exists = DB::table('mantenimientos')->where('serie_padre_id', $m->id)->exists();
        if ($exists) {
            continue;
        }

        $nextDate = Carbon::parse($m->proxima_fecha);

        DB::table('mantenimientos')->insert([
            'equipo_id' => $m->equipo_id,
            'fecha' => $nextDate->format('Y-m-d H:i:s'),
            'frecuencia' => $m->frecuencia,
            'proxima_fecha' => $engine->nextDate((string) $m->frecuencia, $nextDate)?->toDateString(),
            'descripcion' => $m->descripcion,
            'tipo_mantenimiento' => $m->tipo_mantenimiento,
            'estado' => 'pendiente',
            'checklist' => $m->checklist,
            'tecnico_id' => $m->tecnico_id,
            'serie_padre_id' => $m->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $created++;
        $this->info("Materializada siguiente orden de la serie #{$m->id} para el {$nextDate->format('d/m/Y')}.");
    }

    $this->info("Completado: {$created} orden(es) recurrente(s) materializada(s).");
})->purpose('Job de seguridad: materializa órdenes recurrentes faltantes (7 días antes de la fecha)');

Schedule::command('sgen:autocerrar-tickets')->dailyAt('00:05');
