<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Checklist #52: el driver oficial es Argon2id. Los hashes heredados
 * (bcrypt) verifican sin fricción y se migran transparentemente en el
 * primer login exitoso (needsRehash).
 */
final class Argon2idMigrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        RateLimiter::clear('hash_legacy_user|127.0.0.1');

        parent::tearDown();
    }

    public function test_el_driver_de_hash_oficial_es_argon2id(): void
    {
        $this->assertSame('argon2id', config('hashing.driver'));
        $this->assertTrue(Hash::isHashed(Hash::make('prueba')));
        $this->assertStringStartsWith('$argon2id$', Hash::make('prueba'));
    }

    public function test_un_hash_bcrypt_heredado_inicia_sesion_y_se_migra(): void
    {
        // Hash heredado intencionalmente bcrypt (así quedaron las cuentas viejas).
        $hashLegacy = password_hash('Clave-Legada-123', PASSWORD_BCRYPT);
        $this->assertStringStartsWith('$2y$', $hashLegacy);

        $userId = (int) DB::table('usuarios')->insertGetId([
            'username' => 'hash_legacy_user',
            'password' => $hashLegacy,
            'rol' => 'consultor',
            'tema' => 'light',
            'activo' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->post('/login', [
            'username' => 'hash_legacy_user',
            'password' => 'Clave-Legada-123',
        ])->assertRedirect();

        $hashActual = (string) DB::table('usuarios')->where('id', $userId)->value('password');
        $this->assertStringStartsWith('$argon2id$', $hashActual, 'El hash legado debe migrar a Argon2id tras un login exitoso.');
    }
}
