<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    private int $targetUserId;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::firstOrCreate(
            ['username' => 'super_admin_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($admin);

        $this->targetUserId = (int) DB::table('usuarios')->insertGetId([
            'username' => 'test_subject_user',
            'password' => bcrypt('password123'),
            'rol' => 'consultor',
            'tema' => 'dark',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_can_render_user_index_page_with_inertia(): void
    {
        $response = $this->get('/usuarios');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('User/Index')
            ->has('users')
            ->has('kpis')
            ->has('departamentos')
            ->has('empleados')
        );
    }

    public function test_can_create_user(): void
    {
        $payload = [
            'username' => 'tech_new_guy',
            'password' => 'secret1234',
            'rol' => 'tecnico',
        ];

        $response = $this->post('/usuarios', $payload);

        $response->assertRedirect('/usuarios');
        $this->assertDatabaseHas('usuarios', [
            'username' => 'tech_new_guy',
            'rol' => 'tecnico',
        ]);
    }

    public function test_can_update_user(): void
    {
        $payload = [
            'username' => 'test_subject_updated',
            'rol' => 'tecnico',
        ];

        $response = $this->put("/usuarios/{$this->targetUserId}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('usuarios', [
            'id' => $this->targetUserId,
            'username' => 'test_subject_updated',
            'rol' => 'tecnico',
        ]);
    }

    public function test_can_delete_user(): void
    {
        $response = $this->delete("/usuarios/{$this->targetUserId}");

        $response->assertRedirect('/usuarios');
        $this->assertDatabaseMissing('usuarios', [
            'id' => $this->targetUserId,
        ]);
    }
}
