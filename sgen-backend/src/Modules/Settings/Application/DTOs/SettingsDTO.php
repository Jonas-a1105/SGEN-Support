<?php

declare(strict_types=1);

namespace Modules\Settings\Application\DTOs;

use Modules\Settings\Domain\Models\SystemSettings;

final readonly class SettingsDTO
{
    public function __construct(
        public string $language,
        public string $timezone,
        public string $dateFormat,
        public string $theme,
        public string $strokeWidth,
        public string $accentColor,
        public bool $pushEnabled,
        public bool $emailTicketsEnabled,
        public bool $emailWeeklyDigest
    ) {}

    public static function fromModel(SystemSettings $settings): self
    {
        return new self(
            language: $settings->language(),
            timezone: $settings->timezone(),
            dateFormat: $settings->dateFormat(),
            theme: $settings->theme(),
            strokeWidth: $settings->strokeWidth(),
            accentColor: $settings->accentColor(),
            pushEnabled: $settings->isPushEnabled(),
            emailTicketsEnabled: $settings->isEmailTicketsEnabled(),
            emailWeeklyDigest: $settings->isEmailWeeklyDigest()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'language' => $this->language,
            'timezone' => $this->timezone,
            'date_format' => $this->dateFormat,
            'theme' => $this->theme,
            'stroke_width' => $this->strokeWidth,
            'accent_color' => $this->accentColor,
            'push_enabled' => $this->pushEnabled,
            'email_tickets_enabled' => $this->emailTicketsEnabled,
            'email_weekly_digest' => $this->emailWeeklyDigest,
        ];
    }
}
