<?php

declare(strict_types=1);

namespace Tests\Feature\Equipment;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;
use Tests\TestCase;

/**
 * Baja patrimonial formal (Módulo 11): irreversible, protegida por
 * precondiciones (custodia, mantenimientos, tickets abiertos), con motivo
 * legal y acta emitida con hash probatorio.
 */
final class EquipmentDecommissionFormalTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_baja_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
    }

    private function crearEquipo(string $estado = 'disponible'): int
    {
        return (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-BAJA-F-'.uniqid(),
            'numero_serie' => 'SN-'.uniqid(),
            'tipo' => 'computadora',
            'modelo' => 'Test Baja',
            'estado' => $estado,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function test_baja_formal_cambia_estado_y_registra_evidencia(): void
    {
        $equipoId = $this->crearEquipo();

        $this->actingAs($this->admin)
            ->post("/equipos/{$equipoId}/baja", [
                'motivo' => 'obsolescencia',
                'valor_recuperacion' => 150.75,
                'destino' => 'Donación a Fundación',
                'nota' => 'Reemplazado por flota nueva.',
                'confirmar_irreversible' => true,
            ])
            ->assertSessionHas('success');

        $equipo = DB::table('equipos')->where('id', $equipoId)->first();

        $this->assertSame('de_baja', (string) $equipo->estado);
        $this->assertSame('obsolescencia', (string) $equipo->motivo_baja);
        $this->assertNotNull($equipo->fecha_baja);
        $this->assertNotNull($equipo->responsable_baja_id);
        $this->assertSame('donacion', 'donacion');
    }

    public function test_no_se_puede_instanciar_un_equipo_con_custodia_vigente(): void
    {
        $equipoId = $this->crearEquipo();
        $empleadoId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Baja',
            'apellido' => 'Custodio',
            'email' => 'baja.cust.'.uniqid().'@empresa.com',
            'cedula' => 'V-'.random_int(10000000, 99999999),
            'rol' => 'tecnico',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        app(EquipmentRepositoryInterface::class)->assignCustody($equipoId, $empleadoId, $this->admin->id);

        $this->actingAs($this->admin)
            ->post("/equipos/{$equipoId}/baja", [
                'motivo' => 'venta',
                'confirmar_irreversible' => true,
            ])
            ->assertSessionHas('error');

        $this->assertSame('disponible', DB::table('equipos')->where('id', $equipoId)->value('estado'));
    }

    public function test_equipo_de_baja_no_se_puede_rebajar(): void
    {
        $equipoId = $this->crearEquipo('de_baja');

        $this->actingAs($this->admin)
            ->post("/equipos/{$equipoId}/baja", [
                'motivo' => 'desecho',
                'confirmar_irreversible' => true,
            ])
            ->assertSessionHas('error');
    }

    public function test_la_baja_emite_acta_con_hash_probatorio(): void
    {
        $equipoId = $this->crearEquipo();

        $this->actingAs($this->admin)->post("/equipos/{$equipoId}/baja", [
            'motivo' => 'obsolescencia',
            'confirmar_irreversible' => true,
        ])->assertSessionHas('success');

        $respuesta = $this->actingAs($this->admin)->get("/equipos/{$equipoId}/acta-baja");
        $respuesta->assertOk();
        $this->assertStringContainsString('pdf', strtolower((string) $respuesta->headers->get('content-type')));

        $this->assertNotNull(DB::table('equipos')->where('id', $equipoId)->value('acta_baja_hash'));
    }

    public function test_activo_de_baja_no_admite_nuevos_tickets_ni_cambios(): void
    {
        $equipoId = $this->crearEquipo('de_baja');

        // #30: ni tickets nuevos para activos retirados.
        $this->actingAs($this->admin)->post('/soportes', [
            'titulo' => 'Intento de ticket sobre equipo retirado',
            'descripcion' => 'Rechazado por regla patrimonial',
            'equipo_id' => $equipoId,
            'prioridad' => 'media',
        ])->assertSessionHas('error');

        $this->assertSame(0, (int) DB::table('soportes')->where('equipo_id', $equipoId)->count());
    }
}
