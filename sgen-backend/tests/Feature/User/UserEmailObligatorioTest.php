<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Datos maestros mínimos: sin email (empleado vinculado con email o directo
 * de la cuenta) no existe ruta de recuperación self-service — bloqueado.
 */
final class UserEmailObligatorioTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_email_req_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
    }

    private function crearEmpleado(string $email): int
    {
        return (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Sostén',
            'apellido' => 'Técnico',
            'email' => $email,
            'cedula' => 'V-'.random_int(10000000, 99999999),
            'rol' => 'tecnico',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function test_crear_usuario_sin_vinculacion_ni_email_es_rechazado(): void
    {
        $this->actingAs($this->admin)
            ->post('/usuarios', [
                'username' => 'sin_vinculo_'.uniqid(),
                'password' => 'Clave-Valida-123',
                'rol' => 'tecnico',
                // Sin empleado vinculado y sin email: no hay vía de recuperación.
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_crear_usuario_con_email_directo_es_valido(): void
    {
        $this->actingAs($this->admin)
            ->post('/usuarios', [
                'username' => 'con_mail_'.uniqid(),
                'password' => 'Clave-Valida-123',
                'rol' => 'tecnico',
                'email' => 'user.con.mail@empresa.com',
            ])
            ->assertSessionMissing('error');

        $this->assertDatabaseHas('usuarios', ['email' => 'user.con.mail@empresa.com']);
    }

    public function test_crear_usuario_vinculado_a_empleado_con_email_es_valido(): void
    {
        // empleado.email es NOT NULL en BD: la ligada garantiza la recuperación.
        $empleadoConEmail = $this->crearEmpleado('empleado.con.mail@empresa.com');

        $this->actingAs($this->admin)
            ->post('/usuarios', [
                'username' => 'vinculado_'.uniqid(),
                'password' => 'Clave-Valida-123',
                'rol' => 'tecnico',
                'empleado_id' => $empleadoConEmail,
            ])
            ->assertSessionMissing('error');

        $this->assertDatabaseHas('usuarios', ['empleado_id' => $empleadoConEmail]);
    }
}
