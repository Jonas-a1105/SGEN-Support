<?php

declare(strict_types=1);

namespace Tests\Feature\Equipment;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regla de integridad patrimonial: un equipo con tickets, mantenimientos o
 * custodias JAMÁS se borra físicamente — la historia sobrevive (y el mensaje
 * lo explica con el conteo exacto de lo pendiente).
 */
final class EquipmentHistoryProtectionTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_history_equipo_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
    }

    private function crearEquipo(): int
    {
        return (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-HIST-'.uniqid(),
            'numero_serie' => 'SN-'.uniqid(),
            'tipo' => 'computadora',
            'modelo' => 'Test Patrimonio',
            'estado' => 'disponible',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function crearTicket(int $equipoId, string $estado = 'cerrado'): void
    {
        DB::table('soportes')->insert([
            'titulo' => 'Ticket histórico del equipo',
            'descripcion' => 'Evidencia forense',
            'equipo_id' => $equipoId,
            'estado' => $estado,
            'prioridad' => 'media',
            'fecha' => Carbon::now()->subMonths(3),
            'fecha_cierre' => Carbon::now()->subMonths(3)->addDay(),
            'created_at' => Carbon::now()->subMonths(3),
            'updated_at' => Carbon::now()->subMonths(3),
        ]);
    }

    private function crearMantenimiento(int $equipoId): void
    {
        DB::table('mantenimientos')->insert([
            'equipo_id' => $equipoId,
            'tipo_mantenimiento' => 'preventivo',
            'descripcion' => 'PM histórico',
            'fecha' => Carbon::now(),
            'estado' => 'completado',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function test_equipo_con_historial_operativo_no_se_elimina(): void
    {
        $equipoId = $this->crearEquipo();
        $this->crearTicket($equipoId);
        $this->crearMantenimiento($equipoId);

        $this->actingAs($this->admin)
            ->delete("/equipos/{$equipoId}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('equipos', ['id' => $equipoId]);
        $this->assertDatabaseHas('soportes', ['equipo_id' => $equipoId]);
        $this->assertDatabaseHas('mantenimientos', ['equipo_id' => $equipoId]);
    }

    public function test_equipamento_sin_historia_si_puede_retirarse(): void
    {
        $equipoId = $this->crearEquipo();

        $this->actingAs($this->admin)
            ->delete("/equipos/{$equipoId}")
            ->assertRedirect('/equipos');

        // soft delete: presente aún, propelado a la papelera (restorable ahí).
        $this->assertNotNull(DB::table('equipos')->where('id', $equipoId)->value('deleted_at'));
    }
}
