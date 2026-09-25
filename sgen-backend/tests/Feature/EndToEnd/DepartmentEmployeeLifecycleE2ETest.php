<?php

declare(strict_types=1);

namespace Tests\Feature\EndToEnd;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Prueba de Ciclo de Vida Completo E2E: Módulos de Departamentos y Personal
 *
 * Flujo:
 * 1. Crear nuevo departamento organizativo.
 * 2. Crear nuevo empleado con validación de cédula y email único.
 * 3. Asignar empleado al departamento.
 * 4. Asignar activo informático al departamento.
 * 5. Consultar la ficha integral del departamento (empleados, equipos e inventario).
 * 6. Desasignar activo del departamento.
 * 7. Desasignar empleado del departamento.
 * 8. Consultar la ficha de detalle del empleado.
 */
final class DepartmentEmployeeLifecycleE2ETest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['username' => 'admin_e2e_dept_emp'],
            [
                'password' => bcrypt('secret123'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($this->adminUser);
    }

    public function test_complete_department_and_employee_lifecycle(): void
    {
        // 1. Crear nuevo departamento
        $deptPayload = [
            'nombre' => 'Gerencia de Telecomunicaciones E2E',
            'ubicacion' => 'Torre Corporativa, Nivel 7',
            'descripcion' => 'Responsable de la red y enlaces WAN/LAN corporativos',
        ];

        $deptResponse = $this->post('/departamentos', $deptPayload);
        $deptResponse->assertRedirect('/departamentos');

        $dept = DB::table('departamentos')->where('nombre', 'Gerencia de Telecomunicaciones E2E')->first();
        $this->assertNotNull($dept, 'El departamento debe existir en la base de datos.');
        $deptId = (int) $dept->id;

        // 2. Crear nuevo empleado
        $email = 'eduardo.telecom.'.uniqid().'@sgen.com';
        $cedula = 'V-'.rand(10000000, 99999999);

        $empPayload = [
            'nombre' => 'Eduardo',
            'apellido' => 'Garrido',
            'email' => $email,
            'cedula' => $cedula,
            'cargo' => 'Especialista en Redes',
            'rol' => 'tecnico',
        ];

        $empResponse = $this->post('/personal', $empPayload);
        $empResponse->assertRedirect('/personal');

        $emp = DB::table('empleados')->where('email', $email)->first();
        $this->assertNotNull($emp, 'El empleado debe existir en la base de datos.');
        $empId = (int) $emp->id;

        // 3. Asignar empleado al departamento
        $assignEmpResponse = $this->post("/departamentos/{$deptId}/empleados", [
            'empleado_id' => $empId,
        ]);
        $assignEmpResponse->assertRedirect();

        $this->assertDatabaseHas('empleados', [
            'id' => $empId,
            'departamento_id' => $deptId,
        ]);

        // 4. Asignar activo/equipo al departamento
        $eqId = (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'ROUTER-E2E-'.rand(1000, 9999),
            'numero_serie' => 'SN-RTR-'.uniqid(),
            'tipo' => 'Router',
            'marca' => 'MikroTik',
            'modelo' => 'CCR1009-7G-1C-1S+',
            'estado' => 'disponible',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $assignEqResponse = $this->post("/departamentos/{$deptId}/equipos", [
            'equipo_id' => $eqId,
        ]);
        $assignEqResponse->assertRedirect();

        $this->assertDatabaseHas('equipos', [
            'id' => $eqId,
            'departamento_id' => $deptId,
        ]);

        // 5. Consultar ficha integral del departamento
        $deptShowResponse = $this->get("/departamentos/{$deptId}");
        $deptShowResponse->assertStatus(200);
        $deptShowResponse->assertInertia(fn (Assert $page) => $page
            ->component('Department/Show')
            ->has('department')
            ->where('department.id', $deptId)
            ->where('department.nombre', 'Gerencia de Telecomunicaciones E2E')
            ->has('department.empleados')
            ->has('department.equipos')
        );

        // 6. Desasignar activo del departamento
        $removeEqResponse = $this->delete("/departamentos/{$deptId}/equipos/{$eqId}");
        $removeEqResponse->assertRedirect();

        $this->assertDatabaseHas('equipos', [
            'id' => $eqId,
            'departamento_id' => null,
        ]);

        // 7. Desasignar empleado del departamento
        $removeEmpResponse = $this->delete("/departamentos/{$deptId}/empleados/{$empId}");
        $removeEmpResponse->assertRedirect();

        $this->assertDatabaseHas('empleados', [
            'id' => $empId,
            'departamento_id' => null,
        ]);

        // 8. Consultar ficha de detalle del empleado
        $empShowResponse = $this->get("/personal/{$empId}");
        $empShowResponse->assertStatus(200);
        $empShowResponse->assertInertia(fn (Assert $page) => $page
            ->component('Employee/Show')
            ->has('employee')
            ->where('employee.id', $empId)
            ->where('employee.fullName', 'Eduardo Garrido')
            ->has('employee.equipos')
            ->has('employee.tickets')
        );
    }
}
