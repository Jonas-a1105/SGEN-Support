<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Exceptions\OptimisticLockException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Support\Application\DTOs\UpdateTicketDTO;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;
use Tests\TestCase;

/**
 * #22 Locking optimista: dos editores con la misma versión no pueden pisar
 * el ticket; el segundo recibe 409 y la fila conserva la primera escritura.
 */
final class TicketOptimisticLockTest extends TestCase
{
    use DatabaseTransactions;

    private int $ticketId;

    protected function setUp(): void
    {
        parent::setUp();

        $equipo = DB::table('equipos')->first();

        $this->adminUser = User::firstOrCreate(
            ['username' => 'admin_lock_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        $this->ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket para locking optimista',
            'descripcion' => 'Caso de conflicto 409',
            'equipo_id' => (int) $equipo->id,
            'estado' => 'en_proceso',
            'prioridad' => 'media',
            'fecha' => Carbon::now(),
            'version' => 1,
            'usuario_creacion_id' => $this->adminUser->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private User $adminUser;

    public function test_dos_ediciones_con_la_misma_version_no_se_pisan(): void
    {
        $repo = app(SupportRepositoryInterface::class);

        $primera = UpdateTicketDTO::fromArray([
            'titulo' => 'Cambio del editor 1',
            'version' => 1,
        ]);

        $segunda = UpdateTicketDTO::fromArray([
            'titulo' => 'Cambio del editor 2',
            'version' => 1,
        ]);

        $repo->updateTicket($this->ticketId, $primera);
        $this->assertDatabaseHas('soportes', ['id' => $this->ticketId, 'titulo' => 'Cambio del editor 1', 'version' => 2]);

        try {
            $repo->updateTicket($this->ticketId, $segunda);
            $this->fail('Se esperaba OptimisticLockException (409).');
        } catch (OptimisticLockException $e) {
            // OK: el segundo editor queda rechazado con conflicto.
        }

        // La fila conserva la primera modificación; nada se pisó.
        $this->assertDatabaseHas('soportes', ['id' => $this->ticketId, 'titulo' => 'Cambio del editor 1', 'version' => 2]);
    }

    public function test_sin_version_el_update_sigue_funcionando(): void
    {
        $repo = app(SupportRepositoryInterface::class);

        $repo->updateTicket($this->ticketId, UpdateTicketDTO::fromArray([
            'descripcion' => 'Actualización sin versión (compatibilidad interna).',
        ]));

        $this->assertDatabaseHas('soportes', ['id' => $this->ticketId, 'version' => 2]);
    }
}
