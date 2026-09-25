<?php

declare(strict_types=1);

namespace Tests\Feature\EndToEnd;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Prueba de Ciclo de Vida Completo E2E: Módulos de Usuarios, Configuración y Auditoría
 *
 * Flujo:
 * 1. Crear nuevo usuario en el sistema con rol 'tecnico'.
 * 2. Actualizar credenciales y rol a 'admin'.
 * 3. Actualizar preferencias de sistema (tema dark / light).
 * 4. Actualizar contraseña del usuario autenticado.
 * 5. Consultar directorio de usuarios con KPIs de Inertia.
 * 6. Consultar panel de Auditoría y Bitácora del sistema.
 * 7. Eliminar usuario y confirmar revocación de acceso.
 */
final class UserAuditLifecycleE2ETest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['username' => 'super_admin_e2e'],
            [
                'password' => bcrypt('secret1234'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($this->adminUser);
    }

    public function test_complete_user_and_audit_lifecycle(): void
    {
        $uniqueUsername = 'tecnico_e2e_' . rand(1000, 9999);

        // 1. Crear nuevo usuario
        $createPayload = [
            'username' => $uniqueUsername,
            'password' => 'Temporal2026*',
            'rol' => 'tecnico',
        ];

        $createResponse = $this->post('/usuarios', $createPayload);
        $createResponse->assertRedirect('/usuarios');

        $user = DB::table('usuarios')->where('username', $uniqueUsername)->first();
        $this->assertNotNull($user, 'El usuario debe haberse registrado en la base de datos.');
        $userId = (int) $user->id;
        $this->assertSame('tecnico', $user->rol);

        // 2. Actualizar rol a 'admin'
        $updatePayload = [
            'username' => $uniqueUsername . '_promoted',
            'rol' => 'admin',
        ];

        $updateResponse = $this->put("/usuarios/{$userId}", $updatePayload);
        $updateResponse->assertRedirect();

        $updatedUser = DB::table('usuarios')->where('id', $userId)->first();
        $this->assertSame('admin', $updatedUser->rol);
        $this->assertSame($uniqueUsername . '_promoted', $updatedUser->username);

        // 3. Actualizar preferencias de tema
        $settingsResponse = $this->post('/configuracion', [
            'tema' => 'dark',
        ]);
        $settingsResponse->assertRedirect();

        $reloadedAdmin = DB::table('usuarios')->where('id', $this->adminUser->id)->first();
        $this->assertSame('dark', $reloadedAdmin->tema);

        // 4. Actualizar contraseña del usuario
        $passwordResponse = $this->post('/configuracion/password', [
            'current_password' => 'secret1234',
            'password' => 'NuevaPassword2026!',
            'password_confirmation' => 'NuevaPassword2026!',
        ]);
        $passwordResponse->assertRedirect();

        // 5. Consultar directorio de usuarios
        $usersIndexResponse = $this->get('/usuarios');
        $usersIndexResponse->assertStatus(200);
        $usersIndexResponse->assertInertia(fn (Assert $page) => $page
            ->component('User/Index')
            ->has('users')
            ->has('kpis')
            ->has('departamentos')
            ->has('empleados')
        );

        // 6. Consultar panel de Auditoría y Bitácora
        $auditResponse = $this->get('/auditoria');
        $auditResponse->assertStatus(200);
        $auditResponse->assertInertia(fn (Assert $page) => $page
            ->component('Audit/Index')
            ->has('sessions')
            ->has('actions')
            ->has('kpis')
        );

        // 7. Eliminar usuario
        $deleteResponse = $this->delete("/usuarios/{$userId}");
        $deleteResponse->assertRedirect('/usuarios');

        $this->assertDatabaseMissing('usuarios', ['id' => $userId]);
    }
}
