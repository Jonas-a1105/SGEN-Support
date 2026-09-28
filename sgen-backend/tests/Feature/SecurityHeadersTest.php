<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
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
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $this->actingAs($admin)->get('/dashboard')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');
    }

    public function test_csp_estricta_con_nonce_por_peticion(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'headers_csp_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $response = $this->actingAs($admin)->get('/dashboard');

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertNotSame('', $csp, 'La respuesta debe anunciar una Content-Security-Policy.');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self' 'nonce-", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);

        // Los scripts en línea de la plantilla (Ziggy, tema) deben llevar el nonce.
        preg_match("/'nonce-([^']+)'/", $csp, $coincidencia);
        $this->assertNotEmpty($coincidencia[1] ?? null);
        $response->assertSee('nonce="'.$coincidencia[1].'"', false);
    }

    public function test_hsts_solo_se_anuncia_bajo_https(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'headers_https_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $this->actingAs($admin)->get('http://localhost/dashboard')
            ->assertHeaderMissing('Strict-Transport-Security');

        $this->actingAs($admin)->get('https://localhost/dashboard')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
