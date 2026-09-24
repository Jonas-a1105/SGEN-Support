<?php

declare(strict_types=1);

namespace Modules\Settings\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Settings\Domain\Models\SystemSettings;
use Modules\Settings\Domain\Ports\SettingsRepositoryInterface;

final class EloquentSettingsRepository implements SettingsRepositoryInterface
{
    public function getSettingsForUser(int $userId): SystemSettings
    {
        $user = DB::table('usuarios')->where('id', $userId)->first();
        $cached = Cache::get("user_settings_{$userId}", []);

        return new SystemSettings(
            language: (string) ($cached['language'] ?? 'es'),
            timezone: (string) ($cached['timezone'] ?? 'America/Caracas'),
            dateFormat: (string) ($cached['date_format'] ?? 'd/m/Y'),
            theme: (string) ($user->tema ?? $cached['theme'] ?? 'dark'),
            strokeWidth: (string) ($cached['stroke_width'] ?? '2px'),
            accentColor: (string) ($cached['accent_color'] ?? '#4f46e5'),
            pushEnabled: (bool) ($cached['push_enabled'] ?? true),
            emailTicketsEnabled: (bool) ($cached['email_tickets_enabled'] ?? true),
            emailWeeklyDigest: (bool) ($cached['email_weekly_digest'] ?? false)
        );
    }

    public function saveSettingsForUser(int $userId, SystemSettings $settings): void
    {
        DB::table('usuarios')
            ->where('id', $userId)
            ->update([
                'tema' => $settings->theme(),
                'updated_at' => now(),
            ]);

        Cache::forever("user_settings_{$userId}", [
            'language' => $settings->language(),
            'timezone' => $settings->timezone(),
            'date_format' => $settings->dateFormat(),
            'theme' => $settings->theme(),
            'stroke_width' => $settings->strokeWidth(),
            'accent_color' => $settings->accentColor(),
            'push_enabled' => $settings->isPushEnabled(),
            'email_tickets_enabled' => $settings->isEmailTicketsEnabled(),
            'email_weekly_digest' => $settings->isEmailWeeklyDigest(),
        ]);
    }

    public function updateUserPassword(int $userId, string $hashedPassword): void
    {
        DB::table('usuarios')
            ->where('id', $userId)
            ->update([
                'password' => $hashedPassword,
                'updated_at' => now(),
            ]);
    }

    public function verifyUserPassword(int $userId, string $plainPassword): bool
    {
        $user = DB::table('usuarios')->where('id', $userId)->first();
        if ($user === null || empty($user->password)) {
            return false;
        }

        return Hash::check($plainPassword, (string) $user->password);
    }
}
