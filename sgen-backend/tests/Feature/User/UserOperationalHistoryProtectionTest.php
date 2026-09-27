<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regla #43: un usuario con historial operativo (movimientos de inventario,
 * comentarios en tickets con FK estricta) no puede eliminarse físicamente;
 * la trazabilidad se preserva y el error lo explica en lenguaje de negocio.
 */
final class UserOperationalHistoryProtectionTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_history_guard_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
    }

    private function crearUsuario(string $rol = 'tecnico'): User
    {
        $id = (int) DB::table('usuarios')->insertGetId([
            'username' => 'historial_'.uniqid(),
            'password' => \Illuminate\Support\Facades\Hash::make('secret'),
            'rol' => $rol,
            'tema' => 'light',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return User::findOrFail($id);
    }

    private function crearItemInventario(): int
    {
        return (int) DB::table('inventario_items')->insertGetId([
            'codigo' => 'HIST-'.uniqid(),
            'nombre' => 'Ítem historial usuario',
            'categoria' => 'pruebas',
            'descripcion' => 'Fixture de protección por historial',
            'stock_actual' => 5,
            'stock_minimo' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function test_no_se_puede_eliminar_usuario_con_movimientos_de_inventario(): void
    {
        $usuario = $this->crearUsuario();
        $itemId = $this->crearItemInventario();

        DB::table('inventario_movimientos')->insert([
            'item_id' => $itemId,
            'usuario_id' => $usuario->id,
            'tipo_movimiento' => 'AJUSTE',
            'cantidad' => 1,
            'motivo' => 'Fixture de prueba',
            'fecha' => Carbon::now(),
        ]);

        $this->actingAs($this->admin)
            ->delete("/usuarios/{$usuario->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('usuarios', ['id' => $usuario->id]);
    }

    public function test_el_error_explica_que_hay_historial_operativo(): void
    {
        $usuario = $this->crearUsuario();
        $itemId = $this->crearItemInventario();

        DB::table('inventario_movimientos')->insert([
            'item_id' => $itemId,
            'usuario_id' => $usuario->id,
            'tipo_movimiento' => 'AJUSTE',
            'cantidad' => 1,
            'motivo' => 'Fixture de prueba',
            'fecha' => Carbon::now(),
        ]);

        $this->actingAs($this->admin)
            ->delete("/usuarios/{$usuario->id}")
            ->assertSessionHas('error', fn (string $mensaje) => str_contains($mensaje, 'historial operativo'));
    }

    public function test_usuario_sin_historial_si_puede_eliminarse(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($this->admin)
            ->delete("/usuarios/{$usuario->id}")
            ->assertSessionMissing('error');

        $this->assertDatabaseMissing('usuarios', ['id' => $usuario->id]);
    }
}
