<?php

declare(strict_types=1);

namespace Modules\About\Application\DTOs;

final readonly class SystemAboutDTO
{
    /**
     * @param  array<array{title: string, subtitle: string, kicker: string, type: string}>  $technicalCards
     * @param  array<array{name: string, description: string, icon: string}>  $modules
     * @param  array<string, string>  $environment
     * @param  array<array{version: string, date: string, notes: array<string>}>  $versions
     */
    public function __construct(
        public string $appName,
        public string $appVersion,
        public string $systemStatus,
        public array $technicalCards,
        public array $modules,
        public array $environment,
        public array $versions
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'app_name' => $this->appName,
            'app_version' => $this->appVersion,
            'system_status' => $this->systemStatus,
            'technical_cards' => $this->technicalCards,
            'modules' => $this->modules,
            'environment' => $this->environment,
            'versions' => $this->versions,
        ];
    }
}
