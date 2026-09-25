<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Alcance por fila (row-level ownership): el rol operador actúa como
 * solicitante y solo puede ver/descargar sus propios tickets. El resto
 * de roles operan sobre la totalidad de la cola.
 */
final class TicketOwnershipScopeTest extends TestCase
{
    use DatabaseTransactions;

    private User $operador;

    private User $tecnico;

    private int $propioTicketId;

    private int $ajenoTicketId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operador = User::firstOrCreate(
            ['username' => 'operador_scope_'.uniqid()],
            ['password' => bcrypt('secret'), 'rol' => 'operador', 'tema' => 'light']
        );
        $this->tecnico = User::firstOrCreate(
            ['username' => 'tecnico_scope_'.uniqid()],
            ['password' => bcrypt('secret'), 'rol' => 'tecnico', 'tema' => 'light']
        );

        $equipmentId = (int) (DB::table('equipos')->value('id') ?? DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-SCOPE-'.uniqid(),
            'numero_serie' => 'SN-'.uniqid(),
            'tipo' => 'computadora',
            'modelo' => 'Dell Latitude',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]));

        $this->propioTicketId = $this->crearTicket('Mío', $this->operador->id, $equipmentId);
        $this->ajenoTicketId = $this->crearTicket('Ajeno', $this->tecnico->id, $equipmentId);
    }

    private function crearTicket(string $titulo, int $creadorId, int $equipmentId): int
    {
        return (int) DB::table('soportes')->insertGetId([
            'titulo' => "Ticket {$titulo} de alcance",
            'descripcion' => 'Caso de prueba de alcance por fila',
            'equipo_id' => $equipmentId,
            'estado' => 'pendiente',
            'prioridad' => 'media',
            'fecha' => Carbon::now(),
            'usuario_creacion_id' => $creadorId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function test_el_operador_solo_lista_sus_propios_tickets(): void
    {
        $response = $this->actingAs($this->operador)->get('/soportes');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Support/Index')
            // Determinista: cualquier ticket existente en BD fue creado por
            // otros usuarios, por tanto el operador solo debe ver el suyo.
            ->has('tickets', 1)
        );
    }

    public function test_el_operador_no_puede_ver_detalle_ni_pdf_de_ticket_ajeno(): void
    {
        $this->actingAs($this->operador)->get("/soportes/{$this->ajenoTicketId}")->assertNotFound();
        $this->actingAs($this->operador)->get("/soportes/{$this->ajenoTicketId}/pdf")->assertNotFound();
    }

    public function test_el_operador_si_puede_ver_su_propio_ticket(): void
    {
        $this->actingAs($this->operador)->get("/soportes/{$this->propioTicketId}")->assertOk();
    }

    public function test_el_tecnico_puede_ver_cualquier_ticket(): void
    {
        $this->actingAs($this->tecnico)->get("/soportes/{$this->ajenoTicketId}")->assertOk();
        $this->actingAs($this->tecnico)->get("/soportes/{$this->propioTicketId}")->assertOk();
    }

    public function test_el_operador_no_descarga_adjuntos_de_tickets_ajenos(): void
    {
        $adjuntoAjeno = (int) DB::table('ticket_archivos')->insertGetId([
            'ticket_id' => $this->ajenoTicketId,
            'nombre_archivo' => 'evidencia-ajena.pdf',
            'nombre_original' => 'evidencia-ajena.pdf',
            'ruta' => 'ticket_attachments/inexistente.pdf',
            'tipo_mime' => 'application/pdf',
            'tamano_bytes' => 1234,
            'subido_por' => $this->tecnico->id,
            'fecha_subida' => Carbon::now(),
        ]);

        $this->actingAs($this->operador)
            ->get("/soportes/archivos/{$adjuntoAjeno}/descargar")
            ->assertNotFound();
    }
}
