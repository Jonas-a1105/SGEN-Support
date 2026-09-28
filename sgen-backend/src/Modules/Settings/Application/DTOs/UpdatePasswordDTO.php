<?php

declare(strict_types=1);

namespace Modules\Settings\Application\DTOs;

final readonly class UpdatePasswordDTO
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            currentPassword: (string) ($data['current_password'] ?? ''),
            newPassword: (string) ($data['new_password'] ?? '')
        );
    }
}
