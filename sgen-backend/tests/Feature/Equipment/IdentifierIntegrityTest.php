<?php

declare(strict_types=1);

namespace Tests\Feature\Equipment;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Integridad de identificadores a nivel motor (checklist 6.1):
 * IP vigente única, código patrimonial vigente único, cédula vigente única,
 * vínculo empleado↔usuario biunívoco — con las liberaciones correspondientes
 * al dar de baja / desvincular (índices parciales PostgreSQL).
 */
final class IdentifierIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    private function crearEquipo(string $codigo, ?string $ip, string $estado = 'disponible'): void
    {
        DB::table('equipos')->insert([
            'codigo_inventario' => $codigo,
            'numero_serie' => 'SN-INT-'.uniqid(),
            'tipo' => 'computadora',
            'modelo' => 'Testbench',
            'direccion_ip' => $ip,
            'estado' => $estado,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function test_ip_no_se_repite_entre_equipos_vigentes(): void
    {
        $this->crearEquipo('IPTEST-1-'.uniqid(), '10.10.77.5');

        $this->expectException(QueryException::class);
        $this->crearEquipo('IPTEST-2-'.uniqid(), '10.10.77.5');
    }

    public function test_ip_de_un_equipo_de_baja_queda_libre(): void
    {
        $this->crearEquipo('IPBAJA-1-'.uniqid(), '10.10.77.9', 'de_baja');

        // No debe lanzar excepción: el índice es parcial (solo vigentes).
        $this->crearEquipo('IPBAJA-2-'.uniqid(), '10.10.77.9', 'disponible');

        $this->assertSame(2, (int) DB::table('equipos')->where('direccion_ip', '10.10.77.9')->count());
    }

    public function test_codigo_patrimonial_no_se_repite_entre_vigentes(): void
    {
        $codigo = 'COD-UP-'.uniqid();
        $this->crearEquipo($codigo, null);

        $this->expectException(QueryException::class);
        $this->crearEquipo($codigo, null);
    }

    public function test_vinculo_empleado_usuario_es_biunivoco(): void
    {
        $empleadoId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Vínculo',
            'apellido' => 'Único',
            'email' => 'vinculo.'.uniqid().'@empresa.com',
            'cedula' => 'V-'.random_int(10000000, 99999999),
            'rol' => 'tecnico',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('usuarios')->insert([
            'username' => 'vinculo1_'.uniqid(),
            'password' => Hash::make('x'),
            'rol' => 'tecnico',
            'tema' => 'light',
            'empleado_id' => $empleadoId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->expectException(QueryException::class);
        DB::table('usuarios')->insert([
            'username' => 'vinculo2_'.uniqid(),
            'password' => Hash::make('x'),
            'rol' => 'tecnico',
            'tema' => 'light',
            'empleado_id' => $empleadoId, // ya ocupado por otro usuario
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
