<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class ReportsControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_reports_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($user);
    }

    public function test_can_render_reports_index_page_with_inertia(): void
    {
        $response = $this->get('/reportes');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Index')
            ->has('categories')
            ->has('departments')
        );
    }

    public function test_can_generate_tickets_pdf(): void
    {
        $response = $this->get('/reportes/tickets/pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_can_generate_inventory_pdf(): void
    {
        $response = $this->get('/reportes/inventario/pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_can_generate_maintenance_pdf(): void
    {
        $response = $this->get('/reportes/mantenimientos/pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_can_generate_performance_pdf(): void
    {
        $response = $this->get('/reportes/rendimiento/pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_can_export_equipment_excel_from_reports(): void
    {
        $response = $this->get('/reportes/equipos/excel');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }
}

