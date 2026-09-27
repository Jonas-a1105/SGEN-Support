<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regla de negocio #32: la calificación 1–5 de un ticket es única y la
 * registra únicamente el solicitante. El repositorio la hace atómica con
 * transacción + lockForUpdate para que ni una condición de carrera la viole.
 */
final class TicketRatingRulesTest extends TestCase
{
    use DatabaseTransactions;

    private User $solicitante;

    private User $intruso;

    private int $ticketId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->solicitante = User::firstOrCreate(
            ['username' => 'rating_solicitante_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'consultor', 'tema' => 'light']
        );
        $this->intruso = User::firstOrCreate(
            ['username' => 'rating_intruso_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'tecnico', 'tema' => 'light']
        );

        $equipo = DB::table('equipos')->first();
        $equipoId = $equipo
            ? (int) $equipo->id
            : (int) DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'EQ-RATING-'.uniqid(),
                'numero_serie' => 'SN-'.uniqid(),
                'tipo' => 'computadora',
                'modelo' => 'Dell Latitude',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        $this->ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket para reglas de calificación',
            'descripcion' => 'Caso de prueba de valoración única del solicitante',
            'equipo_id' => $equipoId,
            'estado' => 'resuelto',
            'prioridad' => 'media',
            'fecha' => Carbon::now()->subDay(),
            'usuario_creacion_id' => $this->solicitante->id,
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now()->subDay(),
        ]);
    }

    public function test_solo_el_solicitante_puede_calificar(): void
    {
        $this->actingAs($this->intruso)
            ->post("/soportes/{$this->ticketId}/calificar", [
                'calificacion' => 5,
                'comentario' => 'Intento de un tercero',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('soportes', [
            'id' => $this->ticketId,
            'valoracion' => null,
        ]);
    }

    public function test_el_solicitante_califica_exactamente_una_vez(): void
    {
        $this->actingAs($this->solicitante)
            ->post("/soportes/{$this->ticketId}/calificar", [
                'calificacion' => 5,
                'comentario' => 'Excelente atención',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('soportes', [
            'id' => $this->ticketId,
            'valoracion' => 'excelente',
            'valoracion_comentario' => 'Excelente atención',
        ]);

        // Segunda calificación (únicidad): se rechaza y la primera se conserva.
        $this->actingAs($this->solicitante)
            ->post("/soportes/{$this->ticketId}/calificar", [
                'calificacion' => 1,
                'comentario' => 'Cambio de opinión',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('soportes', [
            'id' => $this->ticketId,
            'valoracion' => 'excelente',
            'valoracion_comentario' => 'Excelente atención',
        ]);
    }
}
