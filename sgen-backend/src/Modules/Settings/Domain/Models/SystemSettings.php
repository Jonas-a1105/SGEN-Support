<?php

declare(strict_types=1);

namespace Modules\Settings\Domain\Models;

final class SystemSettings
{
    public function __construct(
        private string $language = 'es',
        private string $timezone = 'America/Caracas',
        private string $dateFormat = 'd/m/Y',
        private string $theme = 'dark',
        private string $strokeWidth = '2px',
        private string $accentColor = '#4f46e5',
        private bool $pushEnabled = true,
        private bool $emailTicketsEnabled = true,
        private bool $emailWeeklyDigest = false
    ) {}

    public function language(): string { return $this->language; }
    public function timezone(): string { return $this->timezone; }
    public function dateFormat(): string { return $this->dateFormat; }
    public function theme(): string { return $this->theme; }
    public function strokeWidth(): string { return $this->strokeWidth; }
    public function accentColor(): string { return $this->accentColor; }
    public function isPushEnabled(): bool { return $this->pushEnabled; }
    public function isEmailTicketsEnabled(): bool { return $this->emailTicketsEnabled; }
    public function isEmailWeeklyDigest(): bool { return $this->emailWeeklyDigest; }
}
