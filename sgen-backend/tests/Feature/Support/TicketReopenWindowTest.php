<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regla #31: la reapertura solo es posible dentro de la ventana de días
 * configurada (`tickets.ventana_reapertura_dias`) y por el solicitante
 * (o técnico/administrador actuando en su nombre). Fuera de ventana, la
 * vía correcta es un ticket nuevo.
 */
final class TicketReopenWindowTest extends TestCase
{
    use DatabaseTransactions;

    private User $solicitante;

    private User $tercero;

    private int $equipoId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->solicitante = User::firstOrCreate(
            ['username' => 'reopen_solicitante_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'consultor', 'tema' => 'light']
        );
        $this->tercero = User::firstOrCreate(
            ['username' => 'reopen_tercero_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'operador', 'tema' => 'light']
        );

        $equipo = DB::table('equipos')->first();
        $this->equipoId = (int) $equipo->id;
    }

    private function crearTicketResuelto(?Carbon $fechaResolucion): int
    {
        return (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket para ventana de reapertura',
            'descripcion' => 'Caso de prueba de la regla #31',
            'equipo_id' => $this->equipoId,
            'estado' => 'resuelto',
            'prioridad' => 'media',
            'fecha' => Carbon::now()->subDays(20),
            'fecha_resolucion' => $fechaResolucion,
            'usuario_creacion_id' => $this->solicitante->id,
            'created_at' => Carbon::now()->subDays(20),
            'updated_at' => Carbon::now()->subDays(3),
        ]);
    }

    public function test_dentro_de_la_ventana_el_solicitante_reabre(): void
    {
        $ticketId = $this->crearTicketResuelto(Carbon::now()->subDays(2));

        $this->actingAs($this->solicitante)
            ->post("/soportes/{$ticketId}/reabrir", ['motivo' => 'La falla regresó ayer.'])
            ->assertSessionHas('success');

        $this->assertSame('en_proceso', DB::table('soportes')->where('id', $ticketId)->value('estado'));
    }

    public function test_fuera_de_la_ventana_la_reapertura_se_rechaza(): void
    {
        $ticketId = $this->crearTicketResuelto(Carbon::now()->subDays(10));

        $this->actingAs($this->solicitante)
            ->post("/soportes/{$ticketId}/reabrir", ['motivo' => 'Tarde: ya pasó la ventana.'])
            ->assertSessionHas('error', fn (string $m) => str_contains($m, 'ventana de reapertura'));

        $this->assertSame('resuelto', DB::table('soportes')->where('id', $ticketId)->value('estado'));
    }

    public function test_un_tercero_no_operativo_no_puede_reabrir(): void
    {
        $ticketId = $this->crearTicketResuelto(Carbon::now()->subDay());

        // Autorización (403), no error de negocio: terceros no ven ni tocan
        // tickets ajenos; la ventana aplica solo al solicitante.
        $this->actingAs($this->tercero)
            ->post("/soportes/{$ticketId}/reabrir", ['motivo' => 'No soy quien lo solicitó.'])
            ->assertForbidden();

        $this->assertSame('resuelto', DB::table('soportes')->where('id', $ticketId)->value('estado'));
    }

    public function test_un_tecnico_puede_reabrir_si_actua_por_el_solicitante(): void
    {
        $tecnico = User::firstOrCreate(
            ['username' => 'reopen_tecnico_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'tecnico', 'tema' => 'light']
        );
        $ticketId = $this->crearTicketResuelto(Carbon::now()->subDay());

        $this->actingAs($tecnico)
            ->post("/soportes/{$ticketId}/reabrir", ['motivo' => 'El usuario llamó por teléfono.'])
            ->assertSessionHas('success');
    }
}
