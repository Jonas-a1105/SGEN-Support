<?php

declare(strict_types=1);

namespace Tests\Feature\EndToEnd;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Prueba de Ciclo de Vida Completo E2E: Módulo de Reportes y Generación PDF
 *
 * Flujo:
 * 1. Visualizar catálogo central de reportes analíticos.
 * 2. Generar y exportar reporte PDF de Tickets con filtros de fecha.
 * 3. Generar y exportar reporte PDF de Inventario y valoración patrimonial.
 * 4. Generar y exportar reporte PDF de Mantenimientos y costos acumulados.
 * 5. Generar y exportar reporte PDF de Rendimiento / Desempeño del personal técnico.
 */
final class ReportsLifecycleE2ETest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['username' => 'admin_e2e_reports'],
            [
                'password' => bcrypt('secret1234'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($this->adminUser);
    }

    public function test_complete_reports_lifecycle(): void
    {
        // 1. Visualizar catálogo central de reportes
        $indexResponse = $this->get('/reportes');
        $indexResponse->assertStatus(200);
        $indexResponse->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Index')
            ->has('categories')
            ->has('departments')
        );

        // 2. Generar reporte PDF de Tickets con filtros de fecha
        $ticketsPdfResponse = $this->get('/reportes/tickets/pdf?fecha_inicio=' . now()->subDays(30)->toDateString() . '&fecha_fin=' . now()->toDateString());
        $ticketsPdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $ticketsPdfResponse->headers->get('Content-Type'));

        // 3. Generar reporte PDF de Inventario
        $inventoryPdfResponse = $this->get('/reportes/inventario/pdf');
        $inventoryPdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $inventoryPdfResponse->headers->get('Content-Type'));

        // 4. Generar reporte PDF de Mantenimientos
        $maintenancePdfResponse = $this->get('/reportes/mantenimientos/pdf');
        $maintenancePdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $maintenancePdfResponse->headers->get('Content-Type'));

        // 5. Generar reporte PDF de Rendimiento de Técnicos
        $performancePdfResponse = $this->get('/reportes/rendimiento/pdf');
        $performancePdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $performancePdfResponse->headers->get('Content-Type'));
    }
}
