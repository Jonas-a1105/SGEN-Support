<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sla:verify', function () {
    $this->info('Verificando vencimientos de SLA de tickets...');
    
    $now = Carbon::now();
    $tickets = DB::table('soportes')
        ->where('estado', '!=', 'resuelto')
        ->whereNotNull('fecha_vencimiento')
        ->where('fecha_vencimiento', '<=', $now)
        ->where('notificacion_vencimiento_enviada', false)
        ->get();

    foreach ($tickets as $ticket) {
        $this->info("Ticket T-{$ticket->id} ha superado el tiempo de resolución SLA. Enviando alertas...");
        
        // Registrar notificación en base de datos
        DB::table('notificaciones')->insert([
            'usuario_id' => $ticket->empleado_id ?? 38,
            'tipo' => 'ticket_vencimiento',
            'titulo' => 'Alerta de SLA superado',
            'mensaje' => "El ticket T-{$ticket->id} ({$ticket->titulo}) ha excedido su tiempo de vencimiento.",
            'enlace' => "/soportes/{$ticket->id}",
            'leido' => false,
            'created_at' => Carbon::now(),
        ]);

        DB::table('soportes')
            ->where('id', $ticket->id)
            ->update([
                'notificacion_vencimiento_enviada' => true,
                'notificacion_vencimiento_fecha' => Carbon::now(),
            ]);
    }

    $this->info('Verificación completada.');
})->purpose('Verifica los tiempos de SLA vencidos en tickets y genera notificaciones');

