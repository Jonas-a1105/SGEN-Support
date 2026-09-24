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
     */
    public function createTicket(CreateTicketDTO $dto, ?int $userId = null): int;

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
     * Obtiene catálogos de opciones para selectores (técnicos, equipos, categorías, departamentos).
     *
     * @return array{
     *     technicians: array<int, array{id: int, name: string, email: string, initial: string, specialty: string, active_tickets: int}>,
     *     equipments: array<int, array{id: int, code: string, serial: string, type: string, model: string, department: string, assigned_to: string}>,
     *     categories: array<int, array{id: int, name: string}>,
     *     departments: array<int, array{id: int, name: string}>,
     *     inventory_items: array<int, array{id: int, code: string, name: string, stock: number}>
     * }
     */
    public function generateTicketPdf(int $ticketId): string;

    public function saveSignature(int $ticketId, string $signatureData): bool;

    public function uploadAttachment(int $ticketId, string $filePath, string $originalName, string $mimeType, int $size, int $userId): int;

    public function deleteAttachment(int $attachmentId): bool;

    public function pauseTicket(int $ticketId): bool;

    public function resumeTicket(int $ticketId): bool;

    public function updateCloseDate(int $ticketId, string $newDate): bool;

    public function bulkDeleteTickets(array $ticketIds): int;

    public function getFormOptions(): array;
}
