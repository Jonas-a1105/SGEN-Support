<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Verifica que todas las respuestas web salen con las cabeceras de
 * seguridad endurecidas (auditoría: sin headers era hallazgo MEDIO).
 */
final class SecurityHeadersTest extends TestCase
{
    use DatabaseTransactions;

    public function test_las_respuestas_web_incluyen_cabeceras_de_seguridad(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'headers_test_'.uniqid()],
            ['password' => bcrypt('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $this->actingAs($admin)->get('/dashboard')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');
    }

    public function test_hsts_solo_se_anuncia_bajo_https(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'headers_https_'.uniqid()],
            ['password' => bcrypt('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $this->actingAs($admin)->get('http://localhost/dashboard')
            ->assertHeaderMissing('Strict-Transport-Security');

        $this->actingAs($admin)->get('https://localhost/dashboard')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
