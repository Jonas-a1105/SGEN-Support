<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\User\Application\UseCases\DeleteUserUseCase;
use Modules\User\Domain\Exceptions\LastAdminProtectionException;
use Tests\TestCase;

/**
 * El sistema jamás puede quedar sin administración (protección estructural):
 * eliminar o degradar al último admin es una excepción de dominio.
 */
final class LastAdminProtectionTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'last_admin_test'],
            ['password' => bcrypt('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
    }

    private function contarAdmins(): int
    {
        return (int) DB::table('usuarios')->where('rol', 'admin')->count();
    }

    public function test_no_se_puede_eliminar_al_ultimo_administrador(): void
    {
        // Reducir a un solo admin en este escenario transaccional
        DB::table('usuarios')->where('rol', 'admin')->where('id', '!=', $this->admin->id)->delete();
        $this->assertSame(1, $this->contarAdmins());

        $response = $this->actingAs($this->admin)->delete("/usuarios/{$this->admin->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('usuarios', ['id' => $this->admin->id, 'rol' => 'admin']);
    }

    public function test_no_se_puede_degradar_al_ultimo_administrador(): void
    {
        DB::table('usuarios')->where('rol', 'admin')->where('id', '!=', $this->admin->id)->delete();

        $response = $this->actingAs($this->admin)->put("/usuarios/{$this->admin->id}", [
            'username' => 'last_admin_test',
            'rol' => 'tecnico',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('usuarios', ['id' => $this->admin->id, 'rol' => 'admin']);
    }

    public function test_con_dos_administradores_si_se_puede_eliminar_uno(): void
    {
        $segundo = User::create([
            'username' => 'admin_rescate_'.uniqid(),
            'password' => bcrypt('Temporal.Segura2026#'),
            'rol' => 'admin',
            'tema' => 'light',
        ]);

        $this->assertGreaterThanOrEqual(2, $this->contarAdmins());

        $this->actingAs($this->admin)->delete("/usuarios/{$segundo->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('usuarios', ['id' => $segundo->id]);
    }

    public function test_la_guarda_es_de_dominio_no_solo_de_controlador(): void
    {
        DB::table('usuarios')->where('rol', 'admin')->where('id', '!=', $this->admin->id)->delete();

        $this->expectException(LastAdminProtectionException::class);
        app(DeleteUserUseCase::class)->execute($this->admin->id);
    }
}
