<?php

declare(strict_types=1);

namespace Tests\Feature\EndToEnd;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Prueba de Ciclo de Vida Completo E2E: Módulo de Soporte y Tickets
 *
 * Flujo:
 * 1. Crear ticket con equipo y categoría asociada.
 * 2. Asignar técnico al ticket.
 * 3. Pausar ticket registrando motivo.
 * 4. Reanudar ticket y validar cómputo de tiempo.
 * 5. Agregar material de inventario (descontando stock automáticamente).
 * 6. Resolver ticket con diagnóstico y solución técnica.
 * 7. Calificar la atención recibida por el usuario.
 * 8. Generar y exportar la Orden de Servicio en PDF.
 */
final class TicketLifecycleE2ETest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;
    private int $equipmentId;
    private int $employeeId;
    private int $technicianId;
    private int $categoryId;
    private int $productId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['username' => 'admin_e2e_ticket'],
            [
                'password' => bcrypt('secret123'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($this->adminUser);

        // Departamento base
        $deptId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Operaciones E2E',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Empleado solicitante
        $this->employeeId = (int) DB::table('empleados')->insertGetId([
            'cedula' => 'V-' . rand(10000000, 99999999),
            'nombre' => 'Mariana',
            'apellido' => 'Pérez',
            'email' => 'mariana.e2e@sgen.com',
            'rol' => 'consultor',
            'departamento_id' => $deptId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Empleado técnico
        $this->technicianId = (int) DB::table('empleados')->insertGetId([
            'cedula' => 'V-' . rand(10000000, 99999999),
            'nombre' => 'Roberto',
            'apellido' => 'Técnico',
            'email' => 'roberto.tec@sgen.com',
            'rol' => 'tecnico',
            'departamento_id' => $deptId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Equipo informático
        $this->equipmentId = (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-E2E-' . rand(1000, 9999),
            'numero_serie' => 'SN-E2E-' . uniqid(),
            'tipo' => 'Computadora',
            'marca' => 'Lenovo',
            'modelo' => 'ThinkCentre M720',
            'estado' => 'en_uso',
            'departamento_id' => $deptId,
            'empleado_id' => $this->employeeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Categoría de soporte
        $this->categoryId = (int) DB::table('categorias')->insertGetId([
            'nombre' => 'Hardware E2E',
            'icono' => 'cpu',
            'color' => '#3B82F6',
            'activo' => true,
        ]);

        // Insumo de inventario con stock
        $this->productId = (int) DB::table('inventario_items')->insertGetId([
            'codigo' => 'PROD-E2E-' . rand(1000, 9999),
            'nombre' => 'Memoria RAM 8GB DDR4',
            'categoria' => 'Hardware',
            'stock_actual' => 10,
            'stock_minimo' => 2,
            'valor_compra' => 35.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_complete_ticket_lifecycle(): void
    {
        // 1. Crear el Ticket
        $ticketPayload = [
            'titulo' => 'Equipo se congela tras 10 minutos de uso',
            'descripcion' => 'La computadora emite pitidos y reinicia sola constantemente.',
            'equipo_id' => $this->equipmentId,
            'prioridad' => 'alta',
            'categoria_id' => $this->categoryId,
        ];

        $createResponse = $this->post('/soportes', $ticketPayload);
        $createResponse->assertRedirect();

        $ticket = DB::table('soportes')
            ->where('titulo', 'Equipo se congela tras 10 minutos de uso')
            ->first();

        $this->assertNotNull($ticket, 'El ticket debe haberse creado en la base de datos.');
        $ticketId = (int) $ticket->id;
        $this->assertSame('pendiente', $ticket->estado);

        // 2. Asignar técnico al ticket
        $assignResponse = $this->put("/soportes/{$ticketId}/asignar-tecnico", [
            'empleado_id' => $this->technicianId,
        ]);
        $assignResponse->assertRedirect();

        $this->assertDatabaseHas('soportes', [
            'id' => $ticketId,
            'empleado_id' => $this->technicianId,
        ]);

        // 3. Pausar ticket (ej: en espera de repuesto)
        $pauseResponse = $this->post("/soportes/{$ticketId}/pausar", [
            'motivo' => 'En espera de módulo de memoria RAM de reemplazo',
        ]);
        $pauseResponse->assertRedirect();

        $pausedTicket = DB::table('soportes')->where('id', $ticketId)->first();
        $this->assertSame('en_espera', $pausedTicket->estado);
        $this->assertSame('En espera de módulo de memoria RAM de reemplazo', $pausedTicket->motivo_pausa);

        // 4. Reanudar ticket
        $resumeResponse = $this->post("/soportes/{$ticketId}/reanudar");
        $resumeResponse->assertRedirect();

        $resumedTicket = DB::table('soportes')->where('id', $ticketId)->first();
        $this->assertSame('en_proceso', $resumedTicket->estado);
        $this->assertNull($resumedTicket->motivo_pausa);

        // 5. Agregar material de inventario (descuenta automáticamente del stock)
        $materialResponse = $this->post("/soportes/{$ticketId}/materiales", [
            'item_id' => $this->productId,
            'cantidad' => 2,
        ]);
        $materialResponse->assertRedirect();

        // Validar que se registró el consumo en inventario_consumos
        $this->assertDatabaseHas('inventario_consumos', [
            'soporte_id' => $ticketId,
            'item_id' => $this->productId,
            'cantidad' => 2,
        ]);

        // Validar que el stock del producto disminuyó de 10 a 8
        $updatedProduct = DB::table('inventario_items')->where('id', $this->productId)->first();
        $this->assertSame(8, (int) $updatedProduct->stock_actual);

        // Validar que se registró el movimiento de inventario
        $this->assertDatabaseHas('inventario_movimientos', [
            'item_id' => $this->productId,
            'tipo_movimiento' => 'CONSUMO',
            'cantidad' => 2,
        ]);

        // 6. Resolver ticket
        $resolveResponse = $this->put("/soportes/{$ticketId}", [
            'estado' => 'resuelto',
            'solucion' => 'Se cambiaron módulos de memoria RAM defectuosos y se ejecutó memtest con 0 errores.',
        ]);
        $resolveResponse->assertRedirect();

        $resolvedTicket = DB::table('soportes')->where('id', $ticketId)->first();
        $this->assertSame('resuelto', $resolvedTicket->estado);
        $this->assertSame('Se cambiaron módulos de memoria RAM defectuosos y se ejecutó memtest con 0 errores.', $resolvedTicket->solucion);

        // 7. Calificar atención
        $rateResponse = $this->post("/soportes/{$ticketId}/calificar", [
            'calificacion' => 5,
            'comentario' => 'Servicio excelente y equipo quedó 100% operativo.',
        ]);
        $rateResponse->assertRedirect();

        $ratedTicket = DB::table('soportes')->where('id', $ticketId)->first();
        $this->assertSame('excelente', $ratedTicket->valoracion);

        // 8. Generar PDF de Orden de Servicio
        $pdfResponse = $this->get("/soportes/{$ticketId}/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $pdfResponse->headers->get('Content-Type'));
    }
}
