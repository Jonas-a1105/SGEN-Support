<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Ports;

use Modules\Support\Application\DTOs\CreateTicketDTO;
use Modules\Support\Application\DTOs\SupportKpisDTO;
use Modules\Support\Application\DTOs\TicketDetailDTO;
use Modules\Support\Application\DTOs\UpdateTicketDTO;

interface SupportRepositoryInterface
{
    /**
     * Obtiene métricas KPI globales y específicas del usuario logueado.
     */
    public function getKpis(?int $currentUserId = null): SupportKpisDTO;

    /**
     * Lista tickets con filtros de estado, prioridad, técnico, solicitante y búsqueda.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function listTickets(array $filters = []): array;

    /**
     * Obtiene el detalle completo de un ticket con sus relaciones (activo, comentarios, archivos, materiales, etc.).
     */
    public function findById(int $id): ?TicketDetailDTO;

    /**
     * Crea un nuevo ticket y devuelve su ID.
     * Recibe la fecha de vencimiento SLA calculada por el dominio.
     */
    public function createTicket(CreateTicketDTO $dto, ?int $userId = null, ?string $fechaVencimiento = null): int;

    /**
     * Actualiza la información de un ticket existente.
     */
    public function updateTicket(int $id, UpdateTicketDTO $dto): bool;

    /**
     * Elimina permanentemente un ticket.
     */
    public function deleteTicket(int $id): bool;

    /**
     * Reasigna el técnico responsable de un ticket.
     */
    public function reassignTechnician(int $id, int $employeeId): bool;

    /**
     * Agrega un comentario al hilo de conversación del ticket.
     */
    public function addComment(int $ticketId, int $userId, string $comment, bool $isInternal = false): bool;

    /**
     * Registra el consumo de un material/repuesto en el ticket.
     */
    public function addMaterial(int $ticketId, int $itemId, int $quantity, int $userId): bool;

    /**
     * Registra la calificación y feedback del solicitante.
     */
    public function rateTicket(int $ticketId, string $rating, ?string $comment = null): bool;

    /**
     * Guarda la firma digital (base64) del solicitante sobre el ticket resuelto.
     */
    public function saveSignature(int $ticketId, string $signatureData): bool;

    public function uploadAttachment(int $ticketId, string $filePath, string $originalName, string $mimeType, int $size, int $userId, ?string $checksumSha256 = null): int;

    public function getAttachmentById(int $attachmentId): ?object;

    public function deleteAttachment(int $attachmentId): bool;

    /**
     * Pausa un ticket dejando el motivo obligatorio registrado.
     */
    public function pauseTicket(int $ticketId, string $motivo, \Carbon\Carbon $pausedAt): bool;

    /**
     * Reanuda un ticket, acumulando los minutos de pausa y extendiendo el SLA.
     */
    public function resumeTicket(int $ticketId, ?\Carbon\Carbon $resumedAt = null): bool;

    public function updateCloseDate(int $ticketId, string $newDate): bool;

    public function bulkDeleteTickets(array $ticketIds): int;

    /**
     * Reabre un ticket registrando el motivo obligatorio en la bitácora y comentarios.
     */
    public function reopenTicket(int $ticketId, string $motivo, ?int $userId = null): bool;

    public function getFormOptions(): array;
}
