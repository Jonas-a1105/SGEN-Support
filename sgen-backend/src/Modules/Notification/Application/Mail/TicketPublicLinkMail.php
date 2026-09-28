<?php

declare(strict_types=1);

namespace Modules\Notification\Application\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo con el enlace público de seguimiento del ticket (canal multicanal
 * del Módulo 03). El solicitante no necesita cuenta: verá estado y respuestas
 * públicas con un token intransferible por correo.
 *
 * Se entrega por cola: el alta del ticket jamás espera al SMTP.
 */
final class TicketPublicLinkMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $codigo,
        public string $titulo,
        public string $urlPublica,
        public string $prioridad
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu ticket {$this->codigo} fue registrado — seguimiento en vivo",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket_public_link',
            with: [
                'codigo' => $this->codigo,
                'titulo' => $this->titulo,
                'url' => $this->urlPublica,
                'prioridad' => $this->prioridad,
            ],
        );
    }
}
