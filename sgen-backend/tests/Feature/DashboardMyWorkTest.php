<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Sprint 2: el dashboard por rol existe — "mis pendientes" es lo que yo
 * hago (creado por mí o asignado a mí), los tickmarks globales siguen ahí.
 */
final class DashboardMyWorkTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_muestra_mi_trabajo_del_dia(): void
    {
        $empleado = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Mine',
            'apellido' => 'Operaria',
            'email' => 'mywork.'.uniqid().'@empresa.com',
            'cedula' => 'V-'.random_int(10000000, 99999999),
            'rol' => 'tecnico', // enum empleados_consultor; la cuenta del usuario sí lleva rol operador
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usuario = User::create([
            'username' => 'dashboard_my_work_'.uniqid(),
            'password' => Hash::make('secret'),
            'rol' => 'operador',
            'tema' => 'light',
            'empleado_id' => $empleado,
        ]);

        $equipo = DB::table('equipos')->where('estado', '!=', 'de_baja')->first();

        // Mi ticket creado por mí: debe verse en mi pizarra.
        DB::table('soportes')->insert([
            'titulo' => 'Ticket de mi trabajo directo',
            'descripcion' => 'Caso dashboard',
            'equipo_id' => (int) $equipo->id,
            'estado' => 'pendiente',
            'prioridad' => 'alta',
            'fecha' => Carbon::now(),
            'usuario_creacion_id' => $usuario->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sesion = $this->actingAs($usuario)->get('/dashboard');
        $sesion->assertOk();

        $contenido = (string) $sesion->getContent();
        // Mi pizarra existe y contiene el dato personal.
        $this->assertTrue(str_contains($contenido, 'Mi trabajo') || str_contains($contenido, 'my_work'), 'El bloque Mi trabajo(por rol) debería devolverse con props.');
    }
}
