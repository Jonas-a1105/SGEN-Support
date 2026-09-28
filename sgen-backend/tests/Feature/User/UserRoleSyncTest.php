<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regresión del hallazgo crítico: la columna `rol` y el rol Spatie deben
 * quedar sincronizados en creación y edición. Antes, degradar un admin
 * conservaba los permisos Spatie (escalada de privilegios latente).
 */
final class UserRoleSyncTest extends TestCase
{
    use DatabaseTransactions;

    private function actingAdmin(string $username = 'role_sync_admin'): User
    {
        $admin = User::firstOrCreate(
            ['username' => $username],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
        $admin->syncSpatieRoleFromColumn();

        $this->actingAs($admin);

        return $admin;
    }

    private function spatieRoleOf(int $userId): ?string
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $userId)
            ->value('roles.name');
    }

    public function test_crear_usuario_sincroniza_el_rol_spatie(): void
    {
        $this->actingAdmin();
        $username = 'spatie_new_'.uniqid();

        $this->post('/usuarios', [
            'username' => $username,
            'password' => 'Soporte2026*sgen',
            'rol' => 'consultor',
            'email' => $username.'@empresa.com',
        ])->assertRedirect('/usuarios');

        $id = (int) DB::table('usuarios')->where('username', $username)->value('id');

        $this->assertSame('consultor', $this->spatieRoleOf($id));
    }

    public function test_degradar_un_admin_actualiza_su_rol_spatie(): void
    {
        $this->actingAdmin();

        $otroAdmin = User::firstOrCreate(
            ['username' => 'role_sync_otro_admin'],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
        $otroAdmin->syncSpatieRoleFromColumn();

        $this->assertSame('admin', $this->spatieRoleOf($otroAdmin->id));

        $this->put("/usuarios/{$otroAdmin->id}", [
            'username' => $otroAdmin->username,
            'rol' => 'tecnico',
        ])->assertRedirect();

        $this->assertSame('tecnico', $this->spatieRoleOf($otroAdmin->id));
        $this->assertDatabaseHas('usuarios', ['id' => $otroAdmin->id, 'rol' => 'tecnico']);
    }

    public function test_nadie_puede_cambiar_su_propio_rol(): void
    {
        $admin = $this->actingAdmin('role_sync_self');

        $this->put("/usuarios/{$admin->id}", [
            'username' => $admin->username,
            'rol' => 'tecnico',
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('usuarios', ['id' => $admin->id, 'rol' => 'admin']);
        $this->assertSame('admin', $this->spatieRoleOf($admin->id));
    }

    public function test_desactivar_revoca_sesiones_y_remember_token(): void
    {
        $admin = $this->actingAdmin('role_sync_deactivator');

        $target = User::firstOrCreate(
            ['username' => 'role_sync_target'],
            ['password' => Hash::make('secret'), 'rol' => 'operador', 'tema' => 'light']
        );
        DB::table('usuarios')->where('id', $target->id)->update([
            'remember_token' => 'token-vivo',
            'activo' => true,
        ]);
        DB::table('sessions')->insert([
            'id' => 'sesion-role-sync-'.uniqid(),
            'user_id' => $target->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'payload',
            'last_activity' => time(),
        ]);

        $this->post("/usuarios/{$target->id}/alternar-estado")->assertRedirect();

        $this->assertDatabaseHas('usuarios', ['id' => $target->id, 'activo' => false, 'remember_token' => null]);
        $this->assertSame(0, DB::table('sessions')->where('user_id', $target->id)->count());
    }
}
