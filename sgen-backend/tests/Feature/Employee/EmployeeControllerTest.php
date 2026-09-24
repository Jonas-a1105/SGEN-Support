<?php

declare(strict_types=1);

namespace Tests\Feature\Employee;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class EmployeeControllerTest extends TestCase
{
    use DatabaseTransactions;

    private int $employeeId;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_emp_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($user);

        $this->employeeId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Test',
            'apellido' => 'Empleado',
            'email' => 'test.empleado.' . uniqid() . '@empresa.com',
            'cedula' => 'V-99999999',
            'cargo' => 'Analista TI',
            'rol' => 'consultor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_can_render_employee_index_page_with_inertia(): void
    {
        $response = $this->get('/personal');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Employee/Index')
            ->has('kpis')
            ->has('empleados')
            ->has('options')
        );
    }

    public function test_can_render_employee_show_page_with_inertia(): void
    {
        $response = $this->get("/personal/{$this->employeeId}");

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Employee/Show')
            ->has('employee')
            ->where('employee.id', $this->employeeId)
            ->has('employee.equipos')
            ->has('employee.tickets')
            ->has('employee.departamentos')
        );
    }

    public function test_can_show_single_employee_json(): void
    {
        $response = $this->getJson("/personal/{$this->employeeId}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $this->employeeId)
            ->assertJsonPath('nombre', 'Test');
    }

    public function test_can_create_employee(): void
    {
        $email = 'nuevo.emp.' . uniqid() . '@empresa.com';
        $payload = [
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'email' => $email,
            'cedula' => 'V-22334455',
            'cargo' => 'Desarrollador',
            'rol' => 'tecnico',
        ];

        $response = $this->post('/personal', $payload);

        $response->assertRedirect('/personal');
        $this->assertDatabaseHas('empleados', [
            'nombre' => 'Carlos',
            'email' => $email,
        ]);
    }

    public function test_can_update_employee(): void
    {
        $payload = [
            'nombre' => 'Test Updated',
            'cargo' => 'Líder TI',
        ];

        $response = $this->put("/personal/{$this->employeeId}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('empleados', [
            'id' => $this->employeeId,
            'nombre' => 'Test Updated',
            'cargo' => 'Líder TI',
        ]);
    }

    public function test_can_delete_employee(): void
    {
        $response = $this->delete("/personal/{$this->employeeId}");

        $response->assertRedirect('/personal');
        $this->assertDatabaseMissing('empleados', [
            'id' => $this->employeeId,
            'deleted_at' => null,
        ]);
    }
}
