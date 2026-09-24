<?php

declare(strict_types=1);

namespace Tests\Unit\Settings;

use Modules\Settings\Domain\Models\SystemSettings;
use PHPUnit\Framework\TestCase;

final class SettingsDomainTest extends TestCase
{
    public function test_can_instantiate_default_settings(): void
    {
        $settings = new SystemSettings();

        $this->assertSame('es', $settings->language());
        $this->assertSame('America/Caracas', $settings->timezone());
        $this->assertSame('d/m/Y', $settings->dateFormat());
        $this->assertSame('dark', $settings->theme());
        $this->assertSame('2px', $settings->strokeWidth());
        $this->assertSame('#4f46e5', $settings->accentColor());
        $this->assertTrue($settings->isPushEnabled());
        $this->assertTrue($settings->isEmailTicketsEnabled());
        $this->assertFalse($settings->isEmailWeeklyDigest());
    }

    public function test_can_instantiate_custom_settings(): void
    {
        $settings = new SystemSettings(
            language: 'en',
            timezone: 'UTC',
            dateFormat: 'Y-m-d',
            theme: 'light',
            strokeWidth: '1px',
            accentColor: '#10b981',
            pushEnabled: false,
            emailTicketsEnabled: false,
            emailWeeklyDigest: true
        );

        $this->assertSame('en', $settings->language());
        $this->assertSame('UTC', $settings->timezone());
        $this->assertSame('Y-m-d', $settings->dateFormat());
        $this->assertSame('light', $settings->theme());
        $this->assertSame('1px', $settings->strokeWidth());
        $this->assertSame('#10b981', $settings->accentColor());
        $this->assertFalse($settings->isPushEnabled());
        $this->assertFalse($settings->isEmailTicketsEnabled());
        $this->assertTrue($settings->isEmailWeeklyDigest());
    }
}
