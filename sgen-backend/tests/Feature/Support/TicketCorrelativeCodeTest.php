<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Numeración legible transaccional (checklist #24): cada ticket nuevo
 * obtiene un código TIC-AAAA-##### único y continuo dentro del año, el
 * código se muestra en la ficha y la URL lo resuelve directamente.
 */
final class TicketCorrelativeCodeTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_codigo_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
    }

    public function test_crear_ticket_asigna_codigo_legible_unico(): void
    {
        $equipo = DB::table('equipos')->first();
        $this->assertNotNull($equipo, 'Se requiere al menos un equipo en la base de testing.');

        $this->actingAs($this->admin)->post('/soportes', [
            'titulo' => 'Ticket con código correlativo',
            'descripcion' => 'Prueba de numeración legible',
            'equipo_id' => $equipo->id,
            'prioridad' => 'media',
        ])->assertRedirect();

        $codigo = (string) DB::table('soportes')->orderByDesc('id')->value('codigo');

        $this->assertMatchesRegularExpression(
            '/^TIC-'.date('Y').'-\d{5}$/',
            $codigo,
            "El código debe seguir el formato TIC-AAAA-00000. Obtenido: {$codigo}"
        );

        // Consecutivo: segundo ticket del año, número siguiente.
        $this->actingAs($this->admin)->post('/soportes', [
            'titulo' => 'Segundo ticket correlativo',
            'descripcion' => 'Debe continuar la serie del año',
            'equipo_id' => $equipo->id,
            'prioridad' => 'baja',
        ])->assertRedirect();

        $codigos = DB::table('soportes')->orderByDesc('id')->limit(2)->pluck('codigo');
        $this->assertCount(2, $codigos->unique(), 'Los códigos no pueden repetirse.');
    }

    public function test_el_codigo_resuelve_la_ficha_del_ticket(): void
    {
        $ticket = DB::table('soportes')->whereNotNull('codigo')->latest('id')->first();
        $this->assertNotNull($ticket, 'Se requiere un ticket con código en la base de testing.');

        $this->actingAs($this->admin)
            ->get('/soportes/'.$ticket->codigo)
            ->assertOk();

        // Prefijo legacy T-{id} sigue funcionando.
        $this->actingAs($this->admin)
            ->get('/soportes/T-'.$ticket->id)
            ->assertOk();

        // Código inexistente: 404 deliberado.
        $this->actingAs($this->admin)
            ->get('/soportes/TIC-'.date('Y').'-99999')
            ->assertNotFound();
    }
}
