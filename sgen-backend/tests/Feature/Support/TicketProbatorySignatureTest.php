<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Firma de conformidad con valor probatorio: el trazo se almacena junto a
 * su huella SHA-256, la IP y el agente del firmante y la fecha de captura,
 * y una vez registrada es única e inmutable.
 */
final class TicketProbatorySignatureTest extends TestCase
{
    use DatabaseTransactions;

    private User $usuario;

    private int $ticketId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::firstOrCreate(
            ['username' => 'firma_probatoria_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'tecnico', 'tema' => 'light']
        );

        $equipo = DB::table('equipos')->first();
        $equipoId = $equipo
            ? (int) $equipo->id
            : (int) DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'EQ-FIRMA-'.uniqid(),
                'numero_serie' => 'SN-'.uniqid(),
                'tipo' => 'computadora',
                'modelo' => 'Dell Latitude',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        $this->ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket para firma probatoria',
            'descripcion' => 'Caso de prueba de evidencia de conformidad',
            'equipo_id' => $equipoId,
            'estado' => 'resuelto',
            'prioridad' => 'media',
            'fecha' => Carbon::now()->subDay(),
            'usuario_creacion_id' => $this->usuario->id,
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now()->subDay(),
        ]);
    }

    private function firmaCanvas(string $contenido = 'trazo-de-prueba'): string
    {
        // Mismo formato que emite BaseSignaturePad: data URL de imagen PNG
        // con un trazo real (una firma de canvas jamás mide menos de 100 chars).
        return 'data:image/png;base64,'.base64_encode(str_repeat($contenido.'-', 8).uniqid());
    }

    public function test_la_firma_guarda_su_evidencia_probatoria(): void
    {
        $firma = $this->firmaCanvas();

        $this->actingAs($this->usuario)
            ->post("/soportes/{$this->ticketId}/firma", ['firma_base64' => $firma])
            ->assertSessionHas('success');

        $row = DB::table('soportes')->where('id', $this->ticketId)->first();

        $this->assertSame($firma, (string) $row->firma);
        $this->assertSame(hash('sha256', $firma), (string) $row->firma_hash_sha256);
        $this->assertNotNull($row->firma_ip, 'La IP del firmante debe conservarse.');
        $this->assertNotNull($row->firmado_en, 'La fecha/hora de la firma debe conservarse.');
    }

    public function test_la_firma_no_puede_sobrescribirse(): void
    {
        $primera = $this->firmaCanvas('primera');

        $this->actingAs($this->usuario)
            ->post("/soportes/{$this->ticketId}/firma", ['firma_base64' => $primera])
            ->assertSessionHas('success');

        $this->actingAs($this->usuario)
            ->post("/soportes/{$this->ticketId}/firma", ['firma_base64' => $this->firmaCanvas('segunda')])
            ->assertSessionHas('error');

        // La evidencia original sigue intacta.
        $row = DB::table('soportes')->where('id', $this->ticketId)->first();
        $this->assertSame($primera, (string) $row->firma);
        $this->assertSame(hash('sha256', $primera), (string) $row->firma_hash_sha256);
    }

    public function test_la_firma_vacia_o_invalida_es_rechazada(): void
    {
        $this->actingAs($this->usuario)
            ->post("/soportes/{$this->ticketId}/firma", ['firma_base64' => 'no-es-un-data-url'])
            ->assertSessionHasErrors('firma_base64');

        $this->actingAs($this->usuario)
            ->post("/soportes/{$this->ticketId}/firma", ['firma_base64' => 'data:image/png;base64,'])
            ->assertSessionHasErrors('firma_base64');

        $this->assertDatabaseHas('soportes', ['id' => $this->ticketId, 'firma' => null]);
    }
}
