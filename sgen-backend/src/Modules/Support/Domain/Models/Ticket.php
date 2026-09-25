<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Models;

use DateTimeImmutable;
use DateTimeInterface;
use DomainException;
use InvalidArgumentException;
use Modules\Support\Domain\Enums\TicketPriority;
use Modules\Support\Domain\Enums\TicketStatus;
use Modules\Support\Domain\Exceptions\InvalidTicketStatusTransitionException;
use Modules\Support\Domain\ValueObjects\TicketId;

final class Ticket
{
    public function __construct(
        private ?TicketId $id,
        private string $title,
        private string $description,
        private TicketStatus $status,
        private TicketPriority $priority,
        private ?int $assignedTechnicianId,
        private ?int $equipmentId,
        private ?int $categoryId,
        private ?int $creatorUserId,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $closedAt = null,
        private ?int $attentionMinutes = null,
        private ?string $rating = null,
        private ?string $ratingComment = null,
        private ?DateTimeImmutable $ratingDate = null
    ) {
        $this->title = trim($this->title);
        if ($this->title === '') {
            throw new InvalidArgumentException('El título del ticket no puede estar vacío.');
        }
    }

    public static function create(
        string $title,
        string $description,
        TicketPriority $priority,
        ?int $equipmentId = null,
        ?int $assignedTechnicianId = null,
        ?int $categoryId = null,
        ?int $creatorUserId = null
    ): self {
        return new self(
            id: null,
            title: $title,
            description: $description,
            status: TicketStatus::PENDIENTE,
            priority: $priority,
            assignedTechnicianId: $assignedTechnicianId,
            equipmentId: $equipmentId,
            categoryId: $categoryId,
            creatorUserId: $creatorUserId,
            createdAt: new DateTimeImmutable
        );
    }

    public function reassignTechnician(int $newTechId): void
    {
        if ($this->status->isFinal()) {
            throw new DomainException('No se puede reasignar el técnico de un ticket que ya ha sido resuelto o cerrado.');
        }

        if ($newTechId <= 0) {
            throw new InvalidArgumentException('El identificador del técnico debe ser válido.');
        }

        $this->assignedTechnicianId = $newTechId;
    }

    public function changeStatus(TicketStatus $newStatus): void
    {
        if (! $this->status->canTransitionTo($newStatus)) {
            throw InvalidTicketStatusTransitionException::from($this->status, $newStatus);
        }

        $this->status = $newStatus;

        if ($newStatus === TicketStatus::RESUELTO && $this->closedAt === null) {
            $this->closedAt = new DateTimeImmutable;
            if ($this->attentionMinutes === null) {
                $seconds = $this->closedAt->getTimestamp() - $this->createdAt->getTimestamp();
                $this->attentionMinutes = max(1, (int) round($seconds / 60));
            }
        }
    }

    public function resolve(): void
    {
        $this->changeStatus(TicketStatus::RESUELTO);
    }

    /**
     * Reapertura formal: solo es válida desde RESUELTO y exige un motivo
     * justificado. La legalidad de la transición la garantiza la máquina
     * de estados; un ticket CERRADO jamás puede reabrirse.
     */
    public function reopen(string $motivo): void
    {
        if (trim($motivo) === '') {
            throw new InvalidArgumentException('La reapertura de un ticket requiere un motivo justificado.');
        }

        $this->changeStatus(TicketStatus::EN_PROCESO);
    }

    /**
     * Cierre definitivo del ciclo de vida (manual o por autocierre programado).
     * CERRADO es un estado terminal: la máquina de estados bloquea
     * cualquier transición posterior.
     */
    public function close(): void
    {
        $this->changeStatus(TicketStatus::CERRADO);
    }

    public function isEditable(): bool
    {
        return $this->status !== TicketStatus::CERRADO;
    }

    public function rate(string $rating, ?string $comment = null): void
    {
        $validRatings = ['excelente', 'bueno', 'regular', 'malo'];
        $cleanRating = strtolower(trim($rating));

        if (! in_array($cleanRating, $validRatings, true)) {
            throw new InvalidArgumentException("La calificación [{$rating}] no es válida. Valores permitidos: excelente, bueno, regular, malo.");
        }

        $this->rating = $cleanRating;
        $this->ratingComment = $comment !== null ? trim($comment) : null;
        $this->ratingDate = new DateTimeImmutable;
    }

    public function isSlaOnTime(?DateTimeInterface $referenceTime = null): bool
    {
        $endTime = $this->closedAt ?? $referenceTime ?? new DateTimeImmutable;
        $diffSeconds = $endTime->getTimestamp() - $this->createdAt->getTimestamp();
        $diffHours = $diffSeconds / 3600;

        return $diffHours <= $this->priority->slaHours();
    }

    public function id(): ?TicketId
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function status(): TicketStatus
    {
        return $this->status;
    }

    public function priority(): TicketPriority
    {
        return $this->priority;
    }

    public function assignedTechnicianId(): ?int
    {
        return $this->assignedTechnicianId;
    }

    public function equipmentId(): ?int
    {
        return $this->equipmentId;
    }

    public function categoryId(): ?int
    {
        return $this->categoryId;
    }

    public function creatorUserId(): ?int
    {
        return $this->creatorUserId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function closedAt(): ?DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function attentionMinutes(): ?int
    {
        return $this->attentionMinutes;
    }

    public function rating(): ?string
    {
        return $this->rating;
    }

    public function ratingComment(): ?string
    {
        return $this->ratingComment;
    }

    public function ratingDate(): ?DateTimeImmutable
    {
        return $this->ratingDate;
    }
}
