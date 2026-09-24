<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class SupportControllerTest extends TestCase
{
    use DatabaseTransactions;

    private int $ticketId;
    private int $equipmentId;
    private int $employeeId;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_support_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($user);

        // Ensure equipment exists
        $equipment = DB::table('equipos')->first();
        if ($equipment) {
            $this->equipmentId = $equipment->id;
        } else {
            $this->equipmentId = (int) DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'CPU-TEST-001',
                'numero_serie' => 'SN-TEST-' . uniqid(),
                'tipo' => 'Computadora',
                'modelo' => 'Optiplex 7010',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure employee exists
        $employee = DB::table('empleados')->first();
        if ($employee) {
            $this->employeeId = $employee->id;
        } else {
            $this->employeeId = (int) DB::table('empleados')->insertGetId([
                'cedula' => 'V-' . rand(1000000, 99999999),
                'nombre' => 'Alexis',
                'apellido' => 'Datica',
                'email' => 'alexis.test@sgen.com',
                'rol' => 'tecnico',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure ticket exists
        $existing = DB::table('soportes')->orderBy('id', 'desc')->first();
        if ($existing) {
            $this->ticketId = $existing->id;
        } else {
            $this->ticketId = (int) DB::table('soportes')->insertGetId([
                'titulo' => 'Ticket de prueba automatizada',
                'descripcion' => 'Descripción de soporte para test feature',
                'equipo_id' => $this->equipmentId,
                'empleado_id' => $this->employeeId,
                'usuario_creacion_id' => $user->id,
                'prioridad' => 'media',
                'estado' => 'en_proceso',
                'fecha' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function test_can_render_support_index_page_with_inertia(): void
    {
        $response = $this->get('/soportes');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Support/Index')
            ->has('kpis')
            ->has('tickets')
            ->has('options')
            ->has('options.technicians')
            ->has('options.equipments')
        );
    }

    public function test_can_render_support_create_page(): void
    {
        $response = $this->get('/soportes/crear');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Support/Create')
            ->has('options')
            ->has('options.technicians')
            ->has('options.equipments')
        );
    }

    public function test_can_create_ticket_via_form_request(): void
    {
        $response = $this->post('/soportes', [
            'titulo' => 'Soporte creado en test',
            'solicitante' => 'Usuario Test',
            'departamento' => 'Dpto. de Sistemas',
            'prioridad' => 'alta',
            'empleado_id' => $this->employeeId,
            'equipo_id' => $this->equipmentId,
            'estado' => 'pendiente',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('soportes', [
            'titulo' => 'Soporte creado en test',
            'prioridad' => 'alta',
            'equipo_id' => $this->equipmentId,
        ]);
    }

    public function test_can_render_support_show_page_with_inertia(): void
    {
        $response = $this->get("/soportes/{$this->ticketId}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Support/Show')
            ->has('ticket')
            ->has('asset')
            ->has('comments')
            ->has('attachments')
            ->has('materials')
            ->has('logEntries')
            ->has('options')
            ->where('ticket.id', $this->ticketId)
        );
    }

    public function test_can_add_comment_to_ticket(): void
    {
        $response = $this->post("/soportes/{$this->ticketId}/comentarios", [
            'comentario' => 'Comentario de prueba unitaria y feature',
            'es_interno' => false,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('ticket_comentarios', [
            'ticket_id' => $this->ticketId,
            'comentario' => 'Comentario de prueba unitaria y feature',
        ]);
    }

    public function test_can_reassign_technician_to_ticket(): void
    {
        $response = $this->put("/soportes/{$this->ticketId}/asignar-tecnico", [
            'empleado_id' => $this->employeeId,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('soportes', [
            'id' => $this->ticketId,
            'empleado_id' => $this->employeeId,
        ]);
    }

    public function test_can_rate_ticket(): void
    {
        $response = $this->post("/soportes/{$this->ticketId}/calificar", [
            'calificacion' => 5,
            'comentario' => 'Excelente servicio y soporte rápido',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('soportes', [
            'id' => $this->ticketId,
            'valoracion' => 'excelente',
        ]);
    }

    public function test_can_render_support_show_page_using_t_code_prefix(): void
    {
        $response = $this->get("/soportes/T-{$this->ticketId}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Support/Show')
            ->where('ticket.id', $this->ticketId)
        );
    }

    public function test_can_delete_ticket(): void
    {
        $response = $this->delete("/soportes/{$this->ticketId}");

        $response->assertRedirect('/soportes');
        $this->assertDatabaseMissing('soportes', [
            'id' => $this->ticketId,
        ]);
    }

    public function test_can_filter_support_tickets(): void
    {
        $response = $this->get('/soportes?estado=pendiente&search=Soporte');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Support/Index')
            ->has('tickets')
        );
    }
}
