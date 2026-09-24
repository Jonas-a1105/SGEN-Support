<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Models;

use DateTimeImmutable;
use Modules\Inventory\Domain\Enums\MovementType;
use Modules\Inventory\Domain\ValueObjects\Quantity;

final class StockMovement
{
    public function __construct(
        private ?int $id,
        private int $productId,
        private int $userId,
        private MovementType $type,
        private Quantity $quantity,
        private string $reason,
        private DateTimeImmutable $createdAt,
        private ?int $originDepartmentId = null,
        private ?int $destinationDepartmentId = null,
        private ?int $referenceId = null
    ) {}

    public static function create(
        int $productId,
        int $userId,
        MovementType $type,
        Quantity $quantity,
        string $reason,
        ?int $originDepartmentId = null,
        ?int $destinationDepartmentId = null,
        ?int $referenceId = null
    ): self {
        return new self(
            id: null,
            productId: $productId,
            userId: $userId,
            type: $type,
            quantity: $quantity,
            reason: $reason,
            createdAt: new DateTimeImmutable,
            originDepartmentId: $originDepartmentId,
            destinationDepartmentId: $destinationDepartmentId,
            referenceId: $referenceId
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function type(): MovementType
    {
        return $this->type;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function originDepartmentId(): ?int
    {
        return $this->originDepartmentId;
    }

    public function destinationDepartmentId(): ?int
    {
        return $this->destinationDepartmentId;
    }

    public function referenceId(): ?int
    {
        return $this->referenceId;
    }
}
