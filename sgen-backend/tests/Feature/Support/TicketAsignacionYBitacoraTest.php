<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;
use Tests\TestCase;

/**
 * La bandeja marca por fila las asignaciones propias (empleado vinculado al
 * usuario) y el detalle expone la Bitácora Técnica real (notas internas y
 * hitos del ciclo de vida), no el placeholder vacío original.
 */
final class TicketAsignacionYBitacoraTest extends TestCase
{
    use DatabaseTransactions;

    public function test_es_mia_asignacion_marca_los_tickets_del_tecnico_vinculado(): void
    {
        $empleadoId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Tec', 'apellido' => 'Mio', 'email' => 'mia.'.uniqid().'@empresa.test',
            'cedula' => 'V-'.rand(10000000, 99999999), 'rol' => 'tecnico',
            'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
        ]);

        $tecnico = User::firstOrCreate(
            ['username' => 'tec_mia_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'tecnico', 'tema' => 'light', 'empleado_id' => $empleadoId]
        );

        $equipoId = (int) DB::table('equipos')->value('id');

        $propioId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Asignado a mí', 'descripcion' => 'caso', 'equipo_id' => $equipoId,
            'estado' => 'en_proceso', 'prioridad' => 'media', 'fecha' => Carbon::now(),
            'empleado_id' => $empleadoId, 'usuario_creacion_id' => $tecnico->id,
            'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
        ]);
        $ajenoId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'De otro técnico', 'descripcion' => 'caso', 'equipo_id' => $equipoId,
            'estado' => 'pendiente', 'prioridad' => 'media', 'fecha' => Carbon::now(),
            'usuario_creacion_id' => $tecnico->id,
            'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
        ]);

        $tickets = collect(app(SupportRepositoryInterface::class)->listTickets(['user_id' => $tecnico->id]));

        $propio = $tickets->firstWhere('raw_id', $propioId);
        $ajeno = $tickets->firstWhere('raw_id', $ajenoId);

        $this->assertTrue((bool) $propio['es_mia_asignacion']);
        $this->assertFalse((bool) ($ajeno['es_mia_asignacion'] ?? false));
    }

    public function test_la_bitacora_tecnica_expone_notas_internas_e_hitos(): void
    {
        $user = User::firstOrCreate(['username' => 'autor_bit_'.uniqid()], ['password' => Hash::make('secret'), 'rol' => 'tecnico', 'tema' => 'light']);
        $equipoId = (int) DB::table('equipos')->value('id');

        $ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket con bitácora', 'descripcion' => 'caso', 'equipo_id' => $equipoId,
            'estado' => 'en_proceso', 'prioridad' => 'baja', 'fecha' => Carbon::now(),
            'usuario_creacion_id' => $user->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
        ]);

        DB::table('ticket_comentarios')->insert([
            ['ticket_id' => $ticketId, 'usuario_id' => $user->id, 'comentario' => 'Nota interna: revisé la fuente de poder.', 'es_interno' => true, 'fecha' => Carbon::now()],
            ['ticket_id' => $ticketId, 'usuario_id' => $user->id, 'comentario' => 'Comentario público visible para el solicitante.', 'es_interno' => false, 'fecha' => Carbon::now()],
        ]);

        // El visor es un técnico: las notas internas forman parte de la
        // bitácora técnica y deben verlas.
        $detail = app(SupportRepositoryInterface::class)->findById($ticketId, $user->id);
        $logEntries = $detail->logEntries;

        $this->assertNotEmpty($logEntries);
        $textos = collect($logEntries)->pluck('content')->implode(' ');
        $this->assertStringContainsString('Nota interna', $textos);
        $this->assertStringNotContainsString('Comentario público', $textos);
    }

    public function test_el_solicitante_no_ve_las_notas_internas_en_el_detalle(): void
    {
        $solicitante = User::firstOrCreate(
            ['username' => 'sol_bit_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'operador', 'tema' => 'light']
        );
        $tecnico = User::firstOrCreate(
            ['username' => 'tec_bit_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'tecnico', 'tema' => 'light']
        );
        $equipoId = (int) DB::table('equipos')->value('id');

        $ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket con notas', 'descripcion' => 'caso', 'equipo_id' => $equipoId,
            'estado' => 'en_proceso', 'prioridad' => 'baja', 'fecha' => Carbon::now(),
            'usuario_creacion_id' => $solicitante->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
        ]);

        DB::table('ticket_comentarios')->insert([
            ['ticket_id' => $ticketId, 'usuario_id' => $tecnico->id, 'comentario' => 'Nota interna del técnico.', 'es_interno' => true, 'fecha' => Carbon::now()],
            ['ticket_id' => $ticketId, 'usuario_id' => $tecnico->id, 'comentario' => 'Comentario público para el solicitante.', 'es_interno' => false, 'fecha' => Carbon::now()],
        ]);

        $detail = app(SupportRepositoryInterface::class)->findById($ticketId, $solicitante->id);
        $comentarios = collect($detail->comments)->pluck('comment')->implode(' ');

        $this->assertStringContainsString('Comentario público', $comentarios);
        $this->assertStringNotContainsString('Nota interna', $comentarios);
        $this->assertEmpty($detail->logEntries, 'El solicitante no debe recibir la bitácora técnica con notas internas.');
    }
}
