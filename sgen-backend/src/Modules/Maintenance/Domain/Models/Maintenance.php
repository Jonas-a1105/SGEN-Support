<?php

declare(strict_types=1);

namespace Modules\Maintenance\Domain\Models;

use DateTimeImmutable;
use DateTimeInterface;
use DomainException;
use InvalidArgumentException;
use Modules\Maintenance\Domain\Enums\MaintenanceFrequency;
use Modules\Maintenance\Domain\Enums\MaintenanceStatus;
use Modules\Maintenance\Domain\Enums\MaintenanceType;

final class Maintenance
{
    public function __construct(
        private ?int $id,
        private int $equipmentId,
        private DateTimeImmutable $scheduledDate,
        private MaintenanceType $type,
        private MaintenanceStatus $status,
        private string $description,
        private MaintenanceFrequency $frequency,
        private ?DateTimeImmutable $nextDate = null,
        private ?float $cost = null,
        private ?int $technicianId = null,
        private ?string $performedBy = null,
        private ?string $observations = null,
        private ?array $checklist = null,
        private ?int $durationMinutes = null,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null
    ) {
        $this->description = trim($this->description);
        if ($this->description === '') {
            throw new InvalidArgumentException('La descripción del mantenimiento es obligatoria.');
        }
    }

    public static function create(
        int $equipmentId,
        DateTimeImmutable $scheduledDate,
        MaintenanceType $type,
        string $description,
        MaintenanceFrequency $frequency = MaintenanceFrequency::UNICA,
        ?int $technicianId = null,
        ?float $cost = null
    ): self {
        $status = self::determineInitialStatus($scheduledDate);
        
        $nextDate = $frequency->isRecurring() 
            ? self::calculateNextDate($scheduledDate, $frequency)
            : null;

        return new self(
            id: null,
            equipmentId: $equipmentId,
            scheduledDate: $scheduledDate,
            type: $type,
            status: $status,
            description: $description,
            frequency: $frequency,
            nextDate: $nextDate,
            cost: $cost,
            technicianId: $technicianId
        );
    }

    private static function determineInitialStatus(DateTimeImmutable $scheduledDate): MaintenanceStatus
    {
        $now = new DateTimeImmutable();
        return $scheduledDate <= $now 
            ? MaintenanceStatus::EN_PROCESO 
            : MaintenanceStatus::PENDIENTE;
    }

    private static function calculateNextDate(
        DateTimeImmutable $fromDate, 
        MaintenanceFrequency $frequency
    ): DateTimeImmutable {
        $months = $frequency->monthsInterval();
        return $fromDate->modify("+{$months} months");
    }

    public function complete(
        ?string $observations = null,
        ?float $finalCost = null,
        ?string $performedBy = null,
        ?int $durationMinutes = null
    ): void {
        if ($this->status === MaintenanceStatus::COMPLETADO) {
            throw new DomainException('El mantenimiento ya está completado.');
        }

        if ($this->status === MaintenanceStatus::CANCELADO) {
            throw new DomainException('No se puede completar un mantenimiento cancelado.');
        }

        $this->status = MaintenanceStatus::COMPLETADO;
        
        if ($observations !== null) {
            $this->observations = trim($observations);
        }
        
        if ($finalCost !== null) {
            $this->cost = $finalCost;
        }
        
        if ($performedBy !== null) {
            $this->performedBy = trim($performedBy);
        }
        
        if ($durationMinutes !== null) {
            $this->durationMinutes = $durationMinutes;
        }

        if ($this->frequency->isRecurring()) {
            $this->nextDate = self::calculateNextDate(new DateTimeImmutable(), $this->frequency);
        }
    }

    public function postpone(DateTimeImmutable $newDate): void
    {
        if (!$this->status->isActive()) {
            throw new DomainException('Solo se puede posponer un mantenimiento pendiente o en proceso.');
        }

        $this->status = MaintenanceStatus::POSPUESTO;
        $this->scheduledDate = $newDate;
    }

    public function cancel(string $reason): void
    {
        if ($this->status === MaintenanceStatus::COMPLETADO) {
            throw new DomainException('No se puede cancelar un mantenimiento ya completado.');
        }

        $this->status = MaintenanceStatus::CANCELADO;
        $this->observations = trim($reason);
    }

    public function start(): void
    {
        if ($this->status !== MaintenanceStatus::PENDIENTE) {
            throw new DomainException('Solo se puede iniciar un mantenimiento pendiente.');
        }

        $this->status = MaintenanceStatus::EN_PROCESO;
    }

    public function updateStatusBasedOnTime(DateTimeInterface $referenceTime): void
    {
        if ($this->status === MaintenanceStatus::PENDIENTE && $this->scheduledDate <= $referenceTime) {
            $this->status = MaintenanceStatus::EN_PROCESO;
        }
    }

    public function isOverdue(DateTimeInterface $referenceTime): bool
    {
        return $this->status->isActive() && $this->scheduledDate < $referenceTime;
    }

    public function isDueWithin(int $days, DateTimeInterface $referenceTime): bool
    {
        if (!$this->status->isActive() || $this->nextDate === null) {
            return false;
        }

        $threshold = (new DateTimeImmutable('@' . $referenceTime->getTimestamp()))
            ->modify("+{$days} days");
        
        return $this->nextDate <= $threshold;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function equipmentId(): int
    {
        return $this->equipmentId;
    }

    public function scheduledDate(): DateTimeImmutable
    {
        return $this->scheduledDate;
    }

    public function type(): MaintenanceType
    {
        return $this->type;
    }

    public function status(): MaintenanceStatus
    {
        return $this->status;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function frequency(): MaintenanceFrequency
    {
        return $this->frequency;
    }

    public function nextDate(): ?DateTimeImmutable
    {
        return $this->nextDate;
    }

    public function cost(): ?float
    {
        return $this->cost;
    }

    public function technicianId(): ?int
    {
        return $this->technicianId;
    }

    public function performedBy(): ?string
    {
        return $this->performedBy;
    }

    public function observations(): ?string
    {
        return $this->observations;
    }

    public function checklist(): ?array
    {
        return $this->checklist;
    }

    public function durationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
