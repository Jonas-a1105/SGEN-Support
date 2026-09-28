<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Portal público del solicitante (Módulo 03 · PASO 0 multicanal): ver estado,
 * comentarios públicos y prioridad por enlace único sin login. El token de 40
 * caracteres es de un solo propósito y jamás expone comentarios internos.
 */
final class PublicTicketPortalController extends Controller
{
    public function show(string $token): Response
    {
        $ticket = DB::table('soportes')
            ->where('token_publico', $token)
            ->first();

        abort_unless($ticket !== null, 404, 'Enlace de seguimiento no reconocido.');

        $comentarios = DB::table('ticket_comentarios')
            ->leftJoin('usuarios', 'ticket_comentarios.usuario_id', '=', 'usuarios.id')
            ->where('ticket_comentarios.ticket_id', (int) $ticket->id)
            ->where('ticket_comentarios.es_interno', false)
            ->orderByDesc('ticket_comentarios.fecha')
            ->limit(20)
            ->get(['ticket_comentarios.id', 'ticket_comentarios.comentario', 'usuarios.username', 'ticket_comentarios.fecha'])
            ->map(static fn ($c) => [
                'id' => (int) $c->id,
                'autor' => (string) ($c->username ?? 'Sistema'),
                'mensaje' => (string) $c->comentario,
                'fecha' => Carbon::parse((string) $c->fecha)->format('d/m/Y H:i'),
            ])
            ->all();

        $equipoNombre = $ticket->equipo_id !== null
            ? DB::table('equipos')->where('id', (int) $ticket->equipo_id)->value('modelo')
            : 'Ticket general';

        return Inertia::render('Portal/EstadoTicket', [
            'ticket' => [
                'codigo' => (string) ($ticket->codigo ?? 'T-'.$ticket->id),
                'titulo' => (string) $ticket->titulo,
                'estado' => (string) $ticket->estado,
                'prioridad' => (string) $ticket->prioridad,
                'equipo' => (string) $equipoNombre,
                'fecha_creacion' => Carbon::parse((string) $ticket->fecha)->format('d/m/Y H:i'),
                'ultima_actualizacion' => Carbon::parse((string) $ticket->updated_at)->format('d/m/Y H:i'),
                'solucion' => $ticket->solucion !== null ? (string) $ticket->solucion : null,
            ],
            'comentarios_publicos' => $comentarios,
        ]);
    }
}
