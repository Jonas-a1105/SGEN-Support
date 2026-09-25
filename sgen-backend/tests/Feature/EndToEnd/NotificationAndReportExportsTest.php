<?php

declare(strict_types=1);

namespace Tests\Feature\EndToEnd;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class NotificationAndReportExportsTest extends TestCase
{
    use DatabaseTransactions;

    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'notif_tester'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->userId = (int) $user->id;
        $this->actingAs($user);
    }

    public function test_can_query_and_count_user_notifications(): void
    {
        // Insert test notifications
        $n1 = DB::table('notificaciones')->insertGetId([
            'usuario_id' => $this->userId,
            'tipo' => 'ticket_asignado',
            'titulo' => 'Nuevo Ticket #101',
            'mensaje' => 'Se te ha asignado el ticket #101',
            'enlace' => '/soportes/101',
            'leido' => false,
            'created_at' => now(),
        ]);

        $n2 = DB::table('notificaciones')->insertGetId([
            'usuario_id' => $this->userId,
            'tipo' => 'inventario_bajo_stock',
            'titulo' => 'Alerta de Stock',
            'mensaje' => 'El ítem RAM-DDR4 está en stock crítico',
            'enlace' => '/inventario',
            'leido' => false,
            'created_at' => now(),
        ]);

        // 1. Count unread
        $countResponse = $this->getJson('/notificaciones/count');
        $countResponse->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, $countResponse->json('count'));

        // 2. Query index
        $indexResponse = $this->getJson('/notificaciones');
        $indexResponse->assertStatus(200);
        $indexResponse->assertJsonFragment(['id' => $n1, 'titulo' => 'Nuevo Ticket #101']);
        $indexResponse->assertJsonFragment(['id' => $n2, 'titulo' => 'Alerta de Stock']);

        // 3. Mark single as read
        $readResponse = $this->patchJson("/notificaciones/{$n1}/read");
        $readResponse->assertStatus(200);
        $readResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('notificaciones', [
            'id' => $n1,
            'leido' => true,
        ]);

        // 4. Mark all as read
        $allReadResponse = $this->postJson('/notificaciones/mark-all-read');
        $allReadResponse->assertStatus(200);
        $allReadResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('notificaciones', [
            'id' => $n2,
            'leido' => true,
        ]);

        // 5. Final count is 0
        $finalCount = $this->getJson('/notificaciones/count');
        $finalCount->assertStatus(200);
        $finalCount->assertJson(['count' => 0]);
    }

    public function test_can_export_inventory_excel_csv(): void
    {
        // Insert dummy inventory item
        $itemId = DB::table('inventario_items')->insertGetId([
            'codigo' => 'TEST-EXP-' . strtoupper(uniqid()),
            'nombre' => 'Teclado Mecánico RGB',
            'categoria' => 'Periféricos',
            'stock_actual' => 12,
            'stock_minimo' => 4,
            'valor_compra' => 45.00,
            'unidad_medida' => 'uds',
            'ubicacion' => 'Estante B2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/reportes/inventario/excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('Reporte_Inventario_', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_can_export_maintenance_excel_csv(): void
    {
        // Insert department & equipment
        $deptId = DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto Export ' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $eqId = DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-EXP-' . strtoupper(uniqid()),
            'tipo' => 'impresora',
            'marca' => 'HP',
            'modelo' => 'LaserJet Pro',
            'numero_serie' => 'SN-HP-' . uniqid(),
            'estado' => 'disponible',
            'departamento_id' => $deptId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mantenimientos')->insertGetId([
            'equipo_id' => $eqId,
            'fecha' => now()->toDateString(),
            'tipo_mantenimiento' => 'preventivo',
            'estado' => 'pendiente',
            'descripcion' => 'Revisión periódica de rodillos',
            'frecuencia' => 'mensual',
            'costo' => 30.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/reportes/mantenimientos/excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('Reporte_Mantenimientos_', (string) $response->headers->get('Content-Disposition'));
    }
}
