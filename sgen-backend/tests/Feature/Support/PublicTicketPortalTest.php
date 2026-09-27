<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Módulo 03 · PASO 0 multicanal: cada ticket tiene un enlace de seguimiento
 * público; el solicitante lo revisa sin cuenta ni menú lateral. Los internos
 * jamás se filtran a la vista pública.
 */
final class PublicTicketPortalTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private int $equipoId;

    private int $ticketId;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_portal_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $equipo = DB::table('equipos')->where('estado', '!=', 'de_baja')->first();
        $this->equipoId = (int) $equipo->id;

        $this->token = Str::random(40);

        $this->ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket para portal del público',
            'descripcion' => 'Caso de seguimiento sin login',
            'equipo_id' => $this->equipoId,
            'estado' => 'en_proceso',
            'prioridad' => 'media',
            'fecha' => Carbon::now(),
            'usuario_creacion_id' => $this->admin->id,
            'token_publico' => $this->token,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_el_enlace_publico_reconoce_el_token(): void
    {
        Mail::fake();

        $this->get("/portal/ticket/{$this->token}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Portal/EstadoTicket')
                ->where('ticket.titulo', 'Ticket para portal del público')
            );

        Mail::assertNothingSent();
    }

    public function test_token_debasio_o_desconocido_da_404(): void
    {
        $this->get('/portal/ticket/token-que-no-existe')->assertNotFound();
    }

    public function test_solicitud_legible_aun_sin_usuario(): void
    {
        $this->assertDatabaseHas('soportes', ['id' => $this->ticketId, 'token_publico' => $this->token]);
    }
}
