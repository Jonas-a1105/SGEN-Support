<?php

declare(strict_types=1);

namespace Tests\Feature\Department;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class DepartmentControllerTest extends TestCase
{
    use DatabaseTransactions;

    private int $departmentId;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_dept_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($user);

        $this->departmentId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto. de Pruebas Unitarias',
            'ubicacion' => 'Edificio Test',
            'descripcion' => 'Departamento para pruebas automáticas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_can_render_department_index_page_with_inertia(): void
    {
        $response = $this->get('/departamentos');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Department/Index')
            ->has('departamentos')
            ->has('candidatos')
        );
    }

    public function test_can_show_single_department_json(): void
    {
        $response = $this->getJson("/departamentos/{$this->departmentId}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $this->departmentId)
            ->assertJsonPath('nombre', 'Dpto. de Pruebas Unitarias');
    }

    public function test_can_create_department(): void
    {
        $payload = [
            'nombre' => 'Dpto. de Innovación y Desarrollo',
            'ubicacion' => 'Torre Norte, Piso 5',
            'descripcion' => 'Área de I+D',
        ];

        $response = $this->post('/departamentos', $payload);

        $response->assertRedirect('/departamentos');
        $this->assertDatabaseHas('departamentos', [
            'nombre' => 'Dpto. de Innovación y Desarrollo',
        ]);
    }

    public function test_can_update_department(): void
    {
        $payload = [
            'nombre' => 'Dpto. de Pruebas Actualizado',
            'ubicacion' => 'Nuevo Edificio',
        ];

        $response = $this->put("/departamentos/{$this->departmentId}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('departamentos', [
            'id' => $this->departmentId,
            'nombre' => 'Dpto. de Pruebas Actualizado',
            'ubicacion' => 'Nuevo Edificio',
        ]);
    }

    public function test_can_delete_department(): void
    {
        $response = $this->delete("/departamentos/{$this->departmentId}");

        $response->assertRedirect('/departamentos');
        $this->assertDatabaseMissing('departamentos', [
            'id' => $this->departmentId,
        ]);
    }
}
