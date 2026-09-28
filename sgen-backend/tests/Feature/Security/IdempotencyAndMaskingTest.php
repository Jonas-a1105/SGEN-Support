<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Audit\Domain\Models\AuditLogEntry;
use Modules\Audit\Domain\Ports\AuditLogRepositoryInterface;
use Tests\TestCase;

/**
 * #21 Idempotency-Key: replay idéntico sin reejecutar la acción.
 * #44 Máscara de secretos: la bitácora jamás almacena tokens ni claves.
 */
final class IdempotencyAndMaskingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private int $equipoId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_idemp_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $equipo = DB::table('equipos')->where('estado', '!=', 'de_baja')->first();
        $this->equipoId = (int) $equipo->id;
    }

    public function test_el_doble_post_con_la_misma_clave_no_duplica_el_ticket(): void
    {
        $clave = 'idem-'.uniqid();
        $payload = [
            'titulo' => 'Ticket idempotente de prueba',
            'descripcion' => 'Se envía dos veces con la misma Idempotency-Key',
            'equipo_id' => $this->equipoId,
            'prioridad' => 'media',
        ];

        $primera = $this->actingAs($this->admin)
            ->withHeader('Idempotency-Key', $clave)
            ->post('/soportes', $payload);
        $primera->assertRedirect();

        $conteoTrasPrimera = (int) DB::table('soportes')
            ->where('titulo', 'Ticket idempotente de prueba')->count();

        $segunda = $this->actingAs($this->admin)
            ->withHeader('Idempotency-Key', $clave)
            ->post('/soportes', $payload);

        // Replay: misma respuesta, cero filas nuevas, header identificador.
        $segunda->assertHeader('Idempotency-Replayed', '1');
        $this->assertSame(
            $conteoTrasPrimera,
            (int) DB::table('soportes')->where('titulo', 'Ticket idempotente de prueba')->count()
        );
    }

    public function test_sin_clave_los_posts_se_ejecutan_normal(): void
    {
        $this->actingAs($this->admin)->post('/soportes', [
            'titulo' => 'Ticket sin clave A', 'descripcion' => 'x', 'equipo_id' => $this->equipoId, 'prioridad' => 'baja',
        ])->assertRedirect();

        $this->actingAs($this->admin)->post('/soportes', [
            'titulo' => 'Ticket sin clave B', 'descripcion' => 'x', 'equipo_id' => $this->equipoId, 'prioridad' => 'baja',
        ])->assertRedirect();

        $this->assertEqualsWithDelta(2, (int) DB::table('soportes')->whereIn('titulo', ['Ticket sin clave A', 'Ticket sin clave B'])->count(), 0);
    }

    public function test_la_bitacora_enmascara_secretos_recursivamente(): void
    {
        $entry = AuditLogEntry::create(
            userId: (int) $this->admin->id,
            username: (string) $this->admin->username,
            action: 'update',
            entityType: 'usuarios',
            entityId: 1,
            oldData: null,
            newData: [
                'nombre' => 'OK visible',
                'password' => 'hash-no-debeaparecer',
                'perfil' => ['two_factor_token' => 'secreto-totp'],
                'firma_base64' => str_repeat('x', 5000),
            ]
        );

        app(AuditLogRepositoryInterface::class)->save($entry);

        $guardado = DB::table('bitacora_acciones')->latest('id')->value('datos_nuevos');
        $this->assertNotNull($guardado);

        $json = (string) $guardado;
        $datos = json_decode($json, true);
        $this->assertIsArray($datos);
        $this->assertSame('OK visible', $datos['nombre']);
        $this->assertSame('••• (oculto)', $datos['password']);
        $this->assertSame('••• (oculto)', $datos['perfil']['two_factor_token']);
        $this->assertStringStartsWith('•••', (string) $datos['firma_base64']);
        $this->assertStringNotContainsString('hash-no-debeaparecer', $json);
        $this->assertStringNotContainsString('secreto-totp', $json);
        $this->assertStringNotContainsString(str_repeat('x', 5000), $json);
    }
}
