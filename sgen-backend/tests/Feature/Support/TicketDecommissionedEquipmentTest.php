<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regla #30: un equipo dado de baja no puede recibir tickets nuevos
 * (su ciclo operativo terminó; el historial permanece intacto). Un equipo
 * "fuera_de_servicio" solo advierte — puede repararse y volver.
 */
final class TicketDecommissionedEquipmentTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_equipo_baja_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
    }

    private function crearEquipo(string $estado): int
    {
        return (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-BAJA-'.uniqid(),
            'numero_serie' => 'SN-'.uniqid(),
            'tipo' => 'computadora',
            'modelo' => 'Dell Latitude',
            'estado' => $estado,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function test_no_se_crea_ticket_a_equipo_de_baja(): void
    {
        $equipoId = $this->crearEquipo('de_baja');

        $this->actingAs($this->admin)
            ->post('/soportes', [
                'titulo' => 'Ticket sobre equipo dado de baja',
                'descripcion' => 'Debe rechazarse por regla #30',
                'equipo_id' => $equipoId,
                'prioridad' => 'media',
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, (int) DB::table('soportes')->where('equipo_id', $equipoId)->count());
    }

    public function test_equipo_fuera_de_servicio_si_acepta_ticket(): void
    {
        // Fuera de servicio ≠ de baja: la máquina puede volver a operar.
        $equipoId = $this->crearEquipo('fuera_de_servicio');

        $this->actingAs($this->admin)
            ->post('/soportes', [
                'titulo' => 'Ticket sobre equipo fuera de servicio',
                'descripcion' => 'Debe admitirse (solo advierte en UI)',
                'equipo_id' => $equipoId,
                'prioridad' => 'alta',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('soportes', ['equipo_id' => $equipoId]);
    }
}
