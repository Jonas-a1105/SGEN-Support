<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class DashboardControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_dash_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($user);
    }

    public function test_can_render_dashboard_index_page_with_inertia(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->has('metrics')
            ->has('metrics.kpis')
            ->has('metrics.ticket_volume')
            ->has('metrics.top_category')
            ->has('metrics.inventory_health')
            ->has('metrics.technicians')
            ->has('metrics.recent_activity')
            ->has('metrics.tickets_by_status')
        );
    }

    public function test_can_filter_ticket_volume_by_year(): void
    {
        $response = $this->get('/dashboard?year=2024');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('metrics.ticket_volume.year', 2024)
        );
    }
}
