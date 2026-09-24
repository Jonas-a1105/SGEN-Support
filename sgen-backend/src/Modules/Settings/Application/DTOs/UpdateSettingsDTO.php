<?php

declare(strict_types=1);

namespace Modules\Settings\Application\DTOs;

final readonly class UpdateSettingsDTO
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

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            language: (string) ($data['language'] ?? 'es'),
            timezone: (string) ($data['timezone'] ?? 'America/Caracas'),
            dateFormat: (string) ($data['date_format'] ?? 'd/m/Y'),
            theme: (string) ($data['theme'] ?? 'dark'),
            strokeWidth: (string) ($data['stroke_width'] ?? '2px'),
            accentColor: (string) ($data['accent_color'] ?? '#4f46e5'),
            pushEnabled: (bool) ($data['push_enabled'] ?? true),
            emailTicketsEnabled: (bool) ($data['email_tickets_enabled'] ?? true),
            emailWeeklyDigest: (bool) ($data['email_weekly_digest'] ?? false)
        );
    }
}
