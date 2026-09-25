<?php

declare(strict_types=1);

namespace Modules\Notification\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo transaccional del ciclo de vida del ticket. Diseño deliberadamente
 * simple: texto plano HTML básico con el enlace al ticket.
 */
final class TicketNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $titulo,
        private readonly string $mensaje,
        private readonly string $enlace,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'SGEN Support: '.$this->titulo);
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.ticket-notification-text',
            with: [
                'titulo' => $this->titulo,
                'mensaje' => $this->mensaje,
                'url' => url($this->enlace),
            ],
        );
    }
}
