<?php

declare(strict_types=1);

namespace Modules\Notification\Application\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Notification\Application\Mail\TicketPublicLinkMail;
use Modules\Notification\Application\Services\EmailThrottler;
use Modules\Support\Domain\Events\TicketCreated;

/**
 * Cuando se crea un ticket (por cualquier canal), el solicitante recibe un
 * enlace público personalizado de seguimiento sin necesitar cuenta. Ronda
 * anti-spam por EmailThrottler.
 */
final class NotifyRequesterOnTicketCreated
{
    public function handle(TicketCreated $event): void
    {
        if ($event->usuarioCreacionId === null) {
            return;
        }

        $usuario = DB::table('usuarios')
            ->leftJoin('empleados', 'usuarios.empleado_id', '=', 'empleados.id')
            ->where('usuarios.id', $event->usuarioCreacionId)
            ->select(['usuarios.email as usu_email', 'empleados.email as emp_email'])
            ->first();

        $destino = $usuario !== null ? (($usuario->usu_email !== null && $usuario->usu_email !== '') ? $usuario->usu_email : $usuario->emp_email) : null;

        if ($destino === null || $destino === '') {
            return;
        }

        $url = url('/portal/ticket/'.$event->tokenPublico);

        if (! EmailThrottler::permitir((int) $event->usuarioCreacionId, $destino, 'ticket_creado', ucfirst($event->ticketTitulo))) {
            return;
        }

        Mail::to($destino)->send(new TicketPublicLinkMail(
            codigo: $event->codigo,
            titulo: $event->ticketTitulo,
            urlPublica: $url,
            prioridad: 'media'
        ));
    }
}
