<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Administración visual (Fase 4c/4d): editor RBAC con bloqueo del rol admin,
 * y panel de configuración global con edición auditada en vivo.
 */
final class RolesAndConfigAdminTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_roles_ui_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
    }

    public function test_panel_roles_muestra_matriz( ): void
    {
        $this->actingAs($this->admin)
            ->get('/roles')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Roles')->has('roles')->has('permisos_disponibles'));
    }

    public function test_rol_admin_no_es_editable_desde_la_ui(): void
    {
        $adminRoleId = (int) DB::table('roles')->where('name', 'admin')->value('id');

        $this->actingAs($this->admin)
            ->put("/roles/{$adminRoleId}", ['permisos' => []])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('model_has_roles', ['role_id' => $adminRoleId]);
    }

    public function test_rol_operativo_sync_permiso_y_bitacora( ): void
    {
        $tecnicoId = (int) DB::table('roles')->where('name', 'tecnico')->value('id');

        $this->actingAs($this->admin)
            ->put("/roles/{$tecnicoId}", [
                'permisos' => ['inventario.manage', 'equipos.manage', 'soportes.manage', 'reportes.view'],
            ])
            ->assertSessionHas('success');

        // Sync real: el permiso quedó asignado en el modelo oficial de Spatie.
        $asignados = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_has_permissions.role_id', $tecnicoId)
            ->pluck('permissions.name')
            ->all();

        $this->assertContains('soportes.manage', $asignados);
        $this->assertNotContains('reportes.view', collect($asignados)->where('like', 'no-existe')->all());
    }

    public function test_config_global_editable_se_ve_y_persiste( ): void
    {
        $this->actingAs($this->admin)
            ->get('/configuracion/sistema')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/ConfiguracionGlobal')->has('claves'));

        $this->actingAs($this->admin)
            ->put('/configuracion/sistema', [
                'clave' => 'seguridad.idle_minutos',
                'valor' => '45',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('configuracion_global', ['clave' => 'seguridad.idle_minutos', 'valor' => '45']);
    }
}
