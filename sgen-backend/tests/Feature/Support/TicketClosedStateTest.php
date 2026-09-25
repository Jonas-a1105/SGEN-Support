<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;
use Tests\TestCase;

/**
 * Verifica el estado terminal CERRADO del ciclo de vida del ticket:
 * autocierre desde resuelto, inmutabilidad del estado cerrado y
 * reapertura coherente desde resuelto.
 */
final class TicketClosedStateTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private int $equipmentId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['username' => 'admin_closed_state_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );

        $equipment = DB::table('equipos')->first();
        if ($equipment) {
            $this->equipmentId = (int) $equipment->id;
        } else {
            $this->equipmentId = (int) DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'EQ-TEST-'.uniqid(),
                'numero_serie' => 'SN-'.uniqid(),
                'tipo' => 'computadora',
                'modelo' => 'Dell Latitude',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    private function crearTicket(string $estado, ?Carbon $fechaResolucion = null): int
    {
        return (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket de prueba estado cerrado',
            'descripcion' => 'Caso de prueba para el ciclo de vida terminal',
            'equipo_id' => $this->equipmentId,
            'estado' => $estado,
            'prioridad' => 'media',
            'fecha' => Carbon::now()->subDays(20),
            'fecha_resolucion' => $fechaResolucion,
            'fecha_cierre' => $fechaResolucion,
            'usuario_creacion_id' => $this->user->id,
            'created_at' => Carbon::now()->subDays(20),
            'updated_at' => $fechaResolucion ?? Carbon::now()->subDays(20),
        ]);
    }

    public function test_el_autocierre_solo_convierte_resueltos_vencidos_en_cerrados(): void
    {
        $vencidoId = $this->crearTicket('resuelto', Carbon::now()->subDays(8));
        $yaCerradoId = $this->crearTicket('cerrado', Carbon::now()->subDays(30));
        $enProcesoId = $this->crearTicket('en_proceso');
        $recienteId = $this->crearTicket('resuelto', Carbon::now()->subDays(2));

        $this->artisan('sgen:autocerrar-tickets')->assertSuccessful();

        $this->assertSame('cerrado', DB::table('soportes')->where('id', $vencidoId)->value('estado'));
        $this->assertSame('cerrado', DB::table('soportes')->where('id', $yaCerradoId)->value('estado'));
        $this->assertSame('en_proceso', DB::table('soportes')->where('id', $enProcesoId)->value('estado'));
        $this->assertSame('resuelto', DB::table('soportes')->where('id', $recienteId)->value('estado'));

        // El ticket recién cerrado registra trazabilidad; el ya cerrado no se reprocesa.
        $comentario = DB::table('ticket_comentarios')
            ->where('ticket_id', $vencidoId)
            ->where('comentario', 'like', '%cerrado automáticamente%')
            ->first();
        $this->assertNotNull($comentario);

        $this->assertSame(
            0,
            (int) DB::table('ticket_comentarios')->where('ticket_id', $yaCerradoId)->count()
        );
    }

    public function test_un_ticket_cerrado_es_terminal_y_no_puede_reabrirse(): void
    {
        $ticketId = $this->crearTicket('cerrado', Carbon::now()->subDays(10));

        $response = $this->actingAs($this->user)->post("/soportes/{$ticketId}/reabrir", [
            'motivo' => 'La falla regresó exactamente igual que antes del cierre.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame('cerrado', DB::table('soportes')->where('id', $ticketId)->value('estado'));
    }

    public function test_un_ticket_cerrado_no_acepta_cambios_de_estado_por_edicion(): void
    {
        $ticketId = $this->crearTicket('cerrado', Carbon::now()->subDays(10));

        $response = $this->actingAs($this->user)->put("/soportes/{$ticketId}", [
            'estado' => 'pendiente',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame('cerrado', DB::table('soportes')->where('id', $ticketId)->value('estado'));
    }

    public function test_un_ticket_resuelto_puede_cerrarse_manualmente(): void
    {
        $ticketId = $this->crearTicket('resuelto', Carbon::now()->subDay());

        $response = $this->actingAs($this->user)->put("/soportes/{$ticketId}", [
            'estado' => 'cerrado',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame('cerrado', DB::table('soportes')->where('id', $ticketId)->value('estado'));
        $this->assertNotNull(DB::table('soportes')->where('id', $ticketId)->value('fecha_cierre'));
    }

    public function test_reabrir_un_resuelto_limpia_la_fecha_de_cierre(): void
    {
        $ticketId = $this->crearTicket('resuelto', Carbon::now()->subDays(3));

        $response = $this->actingAs($this->user)->post("/soportes/{$ticketId}/reabrir", [
            'motivo' => 'El problema se volvió a presentar esta mañana con el mismo equipo.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $ticket = DB::table('soportes')->where('id', $ticketId)->first();
        $this->assertSame('en_proceso', $ticket->estado);
        $this->assertNull($ticket->fecha_cierre);
    }

    public function test_los_kpis_no_cuentan_tickets_cerrados_como_activos(): void
    {
        $this->crearTicket('cerrado', Carbon::now()->subDays(5));
        $this->crearTicket('pendiente');

        $repository = app(SupportRepositoryInterface::class);
        $kpis = $repository->getKpis($this->user->id);

        // El KPI de finalizados incluye resueltos + cerrados y la cola
        // general no debe contar el cerrado como pendiente.
        $esperados = (int) DB::table('soportes')->whereIn('estado', ['resuelto', 'cerrado'])->count();
        $this->assertSame($esperados, $kpis->resolvedTickets);
        $this->assertSame(
            (int) DB::table('soportes')->where('estado', 'pendiente')->count(),
            $kpis->generalQueue
        );
    }
}
