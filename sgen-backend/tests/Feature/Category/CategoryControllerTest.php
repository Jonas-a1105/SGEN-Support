<?php

declare(strict_types=1);

namespace Tests\Feature\Category;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class CategoryControllerTest extends TestCase
{
    use DatabaseTransactions;

    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_category_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($user);

        $this->categoryId = (int) DB::table('categorias')->insertGetId([
            'nombre' => 'Categoría Test',
            'descripcion' => 'Descripción de prueba',
            'icono' => 'bi-laptop',
            'color' => '#2563eb',
            'activo' => 1,
            'created_at' => now(),
        ]);
    }

    public function test_can_render_category_index_page_with_inertia(): void
    {
        $response = $this->get('/categorias');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Category/Index')
            ->has('categories')
            ->has('kpis')
        );
    }

    public function test_can_create_category(): void
    {
        $payload = [
            'nombre' => 'Nueva Categoría Redes',
            'descripcion' => 'Switches y routers',
            'icono' => 'bi-diagram-3',
            'color' => '#10b981',
            'activo' => true,
        ];

        $response = $this->post('/categorias', $payload);

        $response->assertRedirect('/categorias');
        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Nueva Categoría Redes',
            'color' => '#10b981',
        ]);
    }

    public function test_can_update_category(): void
    {
        $payload = [
            'nombre' => 'Categoría Test Modificada',
            'descripcion' => 'Nueva descripción',
            'icono' => 'bi-hdd',
            'color' => '#8b5cf6',
            'activo' => true,
        ];

        $response = $this->put("/categorias/{$this->categoryId}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('categorias', [
            'id' => $this->categoryId,
            'nombre' => 'Categoría Test Modificada',
            'color' => '#8b5cf6',
        ]);
    }

    public function test_can_delete_category(): void
    {
        $response = $this->delete("/categorias/{$this->categoryId}");

        $response->assertRedirect('/categorias');
        $this->assertDatabaseMissing('categorias', [
            'id' => $this->categoryId,
        ]);
    }
}
