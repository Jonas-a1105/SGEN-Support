<?php

declare(strict_types=1);

namespace Tests\Feature\Equipment;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regla #14: coherencia temporal en los maestros —
 * garantía jamás anterior a la compra, orden de mantenimiento con
 * próxima ejecución posterior a la fecha actual.
 */
final class DateCoherenceValidationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private int $equipoId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_fechas_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
        $equipo = DB::table('equipos')->where('estado', '!=', 'de_baja')->first();
        $this->equipoId = (int) $equipo->id;
    }

    public function test_garantia_anterior_a_la_compra_es_rechazada(): void
    {
        $this->actingAs($this->admin)
            ->put("/equipos/{$this->equipoId}", [
                'fecha_compra' => '2026-05-10',
                'garantia' => '2026-01-01',
            ])
            ->assertSessionHasErrors('garantia');
    }

    public function test_garantia_posterior_a_la_compra_es_aceptada(): void
    {
        $this->actingAs($this->admin)
            ->put("/equipos/{$this->equipoId}", [
                'fecha_compra' => '2026-05-10',
                'garantia' => '2028-05-10',
            ])
            ->assertSessionMissing('error');
    }

    public function test_mantenimiento_con_proxima_fecha_anterior_es_rechazado(): void
    {
        $this->actingAs($this->admin)
            ->post('/mantenimientos', [
                'equipo_id' => $this->equipoId,
                'tipo_mantenimiento' => 'preventivo',
                'descripcion' => 'Orden de prueba coherencia temporal',
                'fecha' => '2026-09-26',
                'proxima_fecha' => '2026-01-01',
            ])
            ->assertSessionHasErrors('proxima_fecha');
    }
}
