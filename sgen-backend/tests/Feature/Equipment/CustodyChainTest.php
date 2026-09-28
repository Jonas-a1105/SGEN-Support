<?php

declare(strict_types=1);

namespace Tests\Feature\Equipment;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;
use Tests\TestCase;

/**
 * Módulo 12 · Cadena custodial: una sola custodia activa por equipo (índice
 * parcial en BD), cada reasignación cierra el eslabón anterior y abre el
 * nuevo, la firma del custodio es probatoria e inmutable, y el offboarding
 * del empleado se bloquea mientras conserve custodia activa (regla #27).
 */
final class CustodyChainTest extends TestCase
{
    use DatabaseTransactions;

    private int $empleadoA;

    private int $empleadoB;

    private int $equipoId;

    private EquipmentRepositoryInterface $equipos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empleadoA = $this->crearEmpleado('CustodioA');
        $this->empleadoB = $this->crearEmpleado('CustodioB');
        $this->equipoId = $this->crearEquipo();
        $this->equipos = app(EquipmentRepositoryInterface::class);
    }

    private function crearEmpleado(string $tag): int
    {
        return (int) DB::table('empleados')->insertGetId([
            'nombre' => $tag,
            'apellido' => 'Cadena',
            'email' => strtolower($tag.'.'.uniqid()).'@empresa.com',
            'cedula' => 'V-'.random_int(10000000, 99999999),
            'rol' => 'tecnico',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function crearEquipo(): int
    {
        return (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-CUST-'.uniqid(),
            'numero_serie' => 'SN-'.uniqid(),
            'tipo' => 'computadora',
            'modelo' => 'Testbench',
            'estado' => 'disponible',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function test_reasignar_cierra_el_eslabon_y_abre_el_siguiente(): void
    {
        $this->equipos->assignCustody($this->equipoId, $this->empleadoA, null);
        $this->equipos->assignCustody($this->equipoId, $this->empleadoB, null);

        $activas = DB::table('custodias')
            ->where('equipo_id', $this->equipoId)
            ->whereNull('fecha_fin')
            ->pluck('empleado_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $this->assertSame([$this->empleadoB], $activas, 'Debe quedar exactamente UNA custodia activa: la del nuevo custodio.');

        $historial = $this->equipos->custodyHistory($this->equipoId);
        $this->assertCount(2, $historial);
        $this->assertFalse($historial[1]['vigente']);
        $this->assertNotNull($historial[1]['fecha_fin']);
    }

    public function test_el_motor_garantiza_una_sola_custodia_activa(): void
    {
        $this->equipos->assignCustody($this->equipoId, $this->empleadoA, null);

        $this->expectException(QueryException::class);
        DB::table('custodias')->insert([
            'equipo_id' => $this->equipoId,
            'empleado_id' => $this->empleadoB,
            'fecha_inicio' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function test_equipo_de_baja_no_admite_custodia(): void
    {
        DB::table('equipos')->where('id', $this->equipoId)->update(['estado' => 'de_baja']);

        try {
            $this->equipos->assignCustody($this->equipoId, $this->empleadoA, null);
            $this->fail('Se esperaba rechazo por equipo de baja.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('de baja', $e->getMessage());
        }
    }

    public function test_firma_probatoria_de_custodia_e_inmutabilidad(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin_custodia_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $this->equipos->assignCustody($this->equipoId, $this->empleadoA, null);

        $firma = 'data:image/png;base64,'.base64_encode(str_repeat('trazo-custodia-', 9).uniqid());

        $this->actingAs($admin)->post("/equipos/{$this->equipoId}/custodia/firmar", [
            'firma_base64' => $firma,
        ])->assertSessionHas('success');

        $custodia = DB::table('custodias')->where('equipo_id', $this->equipoId)->whereNull('fecha_fin')->first();
        $this->assertSame(hash('sha256', $firma), (string) $custodia->firma_hash_sha256);
        $this->assertNotNull($custodia->firma_ip);
        $this->assertNotNull($custodia->firmado_en);

        // Segunda firma: rechazada e inmutable.
        $this->actingAs($admin)->post("/equipos/{$this->equipoId}/custodia/firmar", [
            'firma_base64' => 'data:image/png;base64,'.base64_encode(str_repeat('otra-firma-', 12).uniqid()),
        ])->assertSessionHas('error');

        $this->assertSame(hash('sha256', $firma), (string) DB::table('custodias')->where('id', $custodia->id)->value('firma_hash_sha256'));
    }

    public function test_empleado_con_custodia_activa_no_se_desvincula(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin_offboard_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $this->equipos->assignCustody($this->equipoId, $this->empleadoA, null);

        $this->actingAs($admin)
            ->delete("/personal/{$this->empleadoA}")
            ->assertSessionHas('error');

        $this->assertNull(DB::table('empleados')->where('id', $this->empleadoA)->value('deleted_at'));

        // Tras cerrar la custodia, la desvinculación procede.
        $this->equipos->assignCustody($this->equipoId, null, null);

        $this->actingAs($admin)
            ->delete("/personal/{$this->empleadoA}")
            ->assertSessionMissing('error');
    }
}
