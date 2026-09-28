<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regla de negocio: un usuario cuyo empleado vinculado tiene tickets
 * activos no puede eliminarse sin reasignar antes su carga operativa;
 * nunca debe quedar un ticket huérfano de técnico responsable.
 */
final class UserActiveTicketsProtectionTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private int $equipoId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_tickets_guard_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $equipo = DB::table('equipos')->first();
        $this->equipoId = $equipo
            ? (int) $equipo->id
            : (int) DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'EQ-GUARD-'.uniqid(),
                'numero_serie' => 'SN-'.uniqid(),
                'tipo' => 'computadora',
                'modelo' => 'Dell Latitude',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
    }

    /**
     * @return array{0: User, 1: int} usuario técnico y su empleado vinculado.
     */
    private function crearTecnico(): array
    {
        $empleadoId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Técnico',
            'apellido' => 'Guardia',
            'email' => 'tecnico.guard.'.uniqid().'@empresa.com',
            'cedula' => 'V-'.random_int(10000000, 99999999),
            'rol' => 'tecnico',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $usuarioId = (int) DB::table('usuarios')->insertGetId([
            'username' => 'tecnico_guard_'.uniqid(),
            'password' => Hash::make('secret'),
            'rol' => 'tecnico',
            'tema' => 'light',
            'empleado_id' => $empleadoId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return [User::findOrFail($usuarioId), $empleadoId];
    }

    private function crearTicket(int $empleadoId, string $estado): int
    {
        return (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket de guardia de eliminación',
            'descripcion' => 'Caso de prueba de protección por carga activa',
            'equipo_id' => $this->equipoId,
            'empleado_id' => $empleadoId,
            'estado' => $estado,
            'prioridad' => 'media',
            'fecha' => Carbon::now(),
            'usuario_creacion_id' => $this->admin->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function test_no_se_puede_eliminar_usuario_con_tickets_activos(): void
    {
        [$tecnico, $empleadoId] = $this->crearTecnico();
        $this->crearTicket($empleadoId, 'en_proceso');

        $this->actingAs($this->admin)
            ->delete("/usuarios/{$tecnico->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('usuarios', ['id' => $tecnico->id]);
    }

    public function test_el_error_menciona_los_tickets_por_reasignar(): void
    {
        [$tecnico, $empleadoId] = $this->crearTecnico();
        $ticketId = $this->crearTicket($empleadoId, 'pendiente');

        $this->actingAs($this->admin)
            ->delete("/usuarios/{$tecnico->id}")
            ->assertSessionHas('error', fn (string $mensaje) => str_contains($mensaje, "#{$ticketId}"));
    }

    public function test_se_puede_eliminar_cuando_sus_tickets_estan_cerrados(): void
    {
        [$tecnico, $empleadoId] = $this->crearTecnico();
        $this->crearTicket($empleadoId, 'resuelto');
        $this->crearTicket($empleadoId, 'cerrado');

        $this->actingAs($this->admin)
            ->delete("/usuarios/{$tecnico->id}")
            ->assertSessionMissing('error');

        $this->assertDatabaseMissing('usuarios', ['id' => $tecnico->id]);
    }
}
