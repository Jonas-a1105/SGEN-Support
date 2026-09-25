<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Support\Domain\Enums\TicketPriority;
use Modules\Support\Domain\Services\PriorityMatrixService;
use Tests\TestCase;

final class SupportPriorityAndAttachmentTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private int $equipmentId;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->user = User::firstOrCreate(
            ['username' => 'admin_priority_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($this->user);

        $equipment = DB::table('equipos')->first();
        if ($equipment) {
            $this->equipmentId = $equipment->id;
        } else {
            $this->equipmentId = (int) DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'EQ-TEST-ATT-' . uniqid(),
                'numero_serie' => 'SN-ATT-' . uniqid(),
                'tipo' => 'laptop',
                'marca' => 'Dell',
                'modelo' => 'Latitude 5420',
                'estado' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function test_priority_matrix_service_derives_correct_itil_priorities(): void
    {
        $this->assertSame(TicketPriority::CRITICA, PriorityMatrixService::derive('alto', 'alta'));
        $this->assertSame(TicketPriority::ALTA, PriorityMatrixService::derive('alto', 'media'));
        $this->assertSame(TicketPriority::ALTA, PriorityMatrixService::derive('medio', 'alta'));
        $this->assertSame(TicketPriority::MEDIA, PriorityMatrixService::derive('medio', 'media'));
        $this->assertSame(TicketPriority::MEDIA, PriorityMatrixService::derive('alto', 'baja'));
        $this->assertSame(TicketPriority::BAJA, PriorityMatrixService::derive('bajo', 'baja'));

        // Test VIP escalation: lowest urgency becomes media (bajo x media -> baja, medio x media -> media, alto x media -> alta)
        $this->assertSame(TicketPriority::BAJA, PriorityMatrixService::derive('bajo', 'baja', isVip: true));
        $this->assertSame(TicketPriority::MEDIA, PriorityMatrixService::derive('medio', 'baja', isVip: true));
        $this->assertSame(TicketPriority::ALTA, PriorityMatrixService::derive('alto', 'baja', isVip: true));
    }

    public function test_can_upload_attachment_with_sha256_checksum(): void
    {
        $ticketId = DB::table('soportes')->insertGetId([
            'titulo' => 'Falla de pantalla con evidencia fotográfica',
            'descripcion' => 'Adjunto captura del kernel panic',
            'equipo_id' => $this->equipmentId,
            'estado' => 'en_proceso',
            'prioridad' => 'alta',
            'fecha' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $file = UploadedFile::fake()->create('pantalla_azul.png', 1024, 'image/png');

        $response = $this->actingAs($this->user)
            ->post("/soportes/{$ticketId}/archivos", [
                'file' => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('ticket_archivos', [
            'ticket_id' => $ticketId,
            'nombre_original' => 'pantalla_azul.png',
        ]);

        $record = DB::table('ticket_archivos')->where('ticket_id', $ticketId)->first();
        $this->assertNotNull($record);
        $this->assertNotEmpty($record->checksum_sha256);
        $this->assertSame(64, strlen($record->checksum_sha256));
        $this->assertTrue(Storage::exists($record->ruta));

        // Verificar registro en bitácora
        $this->assertDatabaseHas('bitacora_acciones', [
            'entidad' => 'soporte',
            'accion' => 'subir_archivo',
            'entidad_id' => $ticketId,
        ]);
    }

    public function test_upload_attachment_rejects_invalid_mime_and_oversized_files(): void
    {
        $ticketId = DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket de prueba de seguridad',
            'descripcion' => 'Intentando subir script malicioso',
            'equipo_id' => $this->equipmentId,
            'estado' => 'pendiente',
            'prioridad' => 'media',
            'fecha' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Archivo ejecutable no permitido
        $badFile = UploadedFile::fake()->create('malware.exe', 500, 'application/x-msdownload');
        $responseBad = $this->actingAs($this->user)
            ->post("/soportes/{$ticketId}/archivos", ['file' => $badFile]);
        $responseBad->assertSessionHasErrors('file');

        // Archivo que excede los 25MB (26MB = 26624 KB)
        $hugeFile = UploadedFile::fake()->create('dump_pesado.zip', 27000, 'application/zip');
        $responseHuge = $this->actingAs($this->user)
            ->post("/soportes/{$ticketId}/archivos", ['file' => $hugeFile]);
        $responseHuge->assertSessionHasErrors('file');
    }

    public function test_can_download_and_delete_attachment(): void
    {
        $ticketId = DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket para descarga y borrado',
            'descripcion' => 'Probando flujo completo de archivo',
            'equipo_id' => $this->equipmentId,
            'estado' => 'en_proceso',
            'prioridad' => 'media',
            'fecha' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $file = UploadedFile::fake()->create('manual_servicio.pdf', 300, 'application/pdf');
        $this->actingAs($this->user)->post("/soportes/{$ticketId}/archivos", ['file' => $file]);

        $attachment = DB::table('ticket_archivos')->where('ticket_id', $ticketId)->first();
        $this->assertNotNull($attachment);

        // Descarga
        $downloadRes = $this->actingAs($this->user)
            ->get("/soportes/archivos/{$attachment->id}/descargar");
        $downloadRes->assertOk();
        $downloadRes->assertHeader('content-disposition');

        // Borrado
        $deleteRes = $this->actingAs($this->user)
            ->delete("/soportes/archivos/{$attachment->id}");
        $deleteRes->assertRedirect();

        $this->assertDatabaseMissing('ticket_archivos', ['id' => $attachment->id]);
        $this->assertFalse(Storage::exists($attachment->ruta));
    }

    public function test_autoclose_command_closes_resolved_tickets_past_7_days(): void
    {
        $oldResolvedTicketId = DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket resuelto hace 10 días',
            'descripcion' => 'Debe ser cerrado automáticamente',
            'equipo_id' => $this->equipmentId,
            'estado' => 'resuelto',
            'prioridad' => 'media',
            'fecha' => now()->subDays(15),
            'fecha_resolucion' => now()->subDays(10),
            'created_at' => now()->subDays(15),
            'updated_at' => now()->subDays(10),
        ]);

        $recentResolvedTicketId = DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket resuelto ayer',
            'descripcion' => 'No debe ser cerrado todavía',
            'equipo_id' => $this->equipmentId,
            'estado' => 'resuelto',
            'prioridad' => 'media',
            'fecha' => now()->subDays(2),
            'fecha_resolucion' => now()->subDays(1),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(1),
        ]);

        $inProcessTicketId = DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket en proceso activo',
            'descripcion' => 'Nunca debe cerrarse por este comando',
            'equipo_id' => $this->equipmentId,
            'estado' => 'en_proceso',
            'prioridad' => 'alta',
            'fecha' => now()->subDays(20),
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);

        $this->artisan('sgen:autocerrar-tickets --dias=7')
            ->assertExitCode(0);

        // El ticket antiguo debe estar cerrado
        $oldTicket = DB::table('soportes')->where('id', $oldResolvedTicketId)->first();
        $this->assertSame('cerrado', $oldTicket->estado);
        $this->assertNotNull($oldTicket->fecha_cierre);

        // El ticket reciente debe permanecer resuelto
        $recentTicket = DB::table('soportes')->where('id', $recentResolvedTicketId)->first();
        $this->assertSame('resuelto', $recentTicket->estado);

        // El ticket en proceso debe seguir en proceso
        $inProcessTicket = DB::table('soportes')->where('id', $inProcessTicketId)->first();
        $this->assertSame('en_proceso', $inProcessTicket->estado);

        // Comentario de sistema generado
        $this->assertDatabaseHas('ticket_comentarios', [
            'ticket_id' => $oldResolvedTicketId,
            'es_interno' => false,
        ]);

        // Bitácora registrada
        $this->assertDatabaseHas('bitacora_acciones', [
            'entidad' => 'soporte',
            'accion' => 'autocierre_ticket',
            'entidad_id' => $oldResolvedTicketId,
        ]);
    }
}
