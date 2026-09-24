<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class AuditControllerTest extends TestCase
{
    use DatabaseTransactions;

    private int $sessionId;
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_audit_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->userId = $user->id;
        $this->actingAs($user);

        $this->sessionId = (int) DB::table('sesiones_log')->insertGetId([
            'usuario_id' => $this->userId,
            'username' => 'admin_audit_test',
            'fecha_inicio' => now()->subHours(2),
            'fecha_fin' => now()->subHour(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_can_render_audit_index_page_with_inertia(): void
    {
        $response = $this->get('/auditoria');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Audit/Index')
            ->has('sessions')
            ->has('kpis')
            ->has('filters')
        );
    }

    public function test_can_show_session_detail_json(): void
    {
        $response = $this->getJson("/auditoria/{$this->sessionId}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $this->sessionId)
            ->assertJsonPath('username', 'admin_audit_test');
    }

    public function test_export_returns_streamed_csv(): void
    {
        $response = $this->get('/auditoria/export');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type', ''), 'text/csv'));
    }

    public function test_can_render_audit_index_with_bitacora_actions(): void
    {
        DB::table('bitacora_acciones')->insert([
            'usuario_id' => $this->userId,
            'username' => 'admin_audit_test',
            'accion' => 'Creó un equipo de prueba',
            'enlace_tipo' => 'equipo',
            'enlace_id' => 1,
            'entidad' => 'equipo',
            'entidad_id' => 1,
            'created_at' => now(),
        ]);

        $response = $this->get('/auditoria?tab=bitacora');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Audit/Index')
            ->has('actions')
            ->has('sessions')
            ->has('kpis')
            ->where('active_tab', 'bitacora')
        );
    }

    public function test_export_bitacora_returns_streamed_csv(): void
    {
        $response = $this->get('/auditoria/export-bitacora');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type', ''), 'text/csv'));
    }
}
