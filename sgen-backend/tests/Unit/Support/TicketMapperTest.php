<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Carbon\Carbon;
use Modules\Support\Application\Mappers\TicketDetailMapper;
use Modules\Support\Application\Mappers\TicketListItemMapper;
use Tests\TestCase;

final class TicketMapperTest extends TestCase
{
    public function test_ticket_list_item_mapper_formats_raw_row_correctly(): void
    {
        $rawRow = (object) [
            'id' => 96,
            'titulo' => 'Fallo en Servidor de Base de Datos',
            'descripcion' => 'Reinicio inesperado',
            'estado' => 'en_proceso',
            'prioridad' => 'critica',
            'fecha' => '2026-09-20 10:00:00',
            'categoria_nombre' => 'Servidores',
            'tech_nombre' => 'Alexis',
            'tech_apellido' => 'Datica',
            'req_nombre' => 'Herdil',
            'req_apellido' => 'Gutierrez',
            'depto_nombre' => 'Dpto. de Sistemas',
            'usuario_creacion_id' => 1,
        ];

        $commentCounts = [96 => 4];
        $currentUserId = 1;

        $mapped = TicketListItemMapper::fromDatabaseRow($rawRow, $commentCounts, $currentUserId);

        $this->assertSame('T-96', $mapped['id']);
        $this->assertSame(96, $mapped['raw_id']);
        $this->assertSame('Fallo en Servidor de Base de Datos', $mapped['title']);
        $this->assertSame('SERVIDORES', $mapped['category']);
        $this->assertSame('Alexis Datica', $mapped['tech']);
        $this->assertSame('A', $mapped['tech_init']);
        $this->assertSame('process', $mapped['status']);
        $this->assertSame('critica', $mapped['priority']);
        $this->assertSame(4, $mapped['comments']);
        $this->assertTrue($mapped['is_mine']);
    }

    public function test_ticket_detail_mapper_transforms_records_into_dto(): void
    {
        $rawTicket = (object) [
            'id' => 96,
            'titulo' => 'Soporte de Switch',
            'descripcion' => 'Reemplazo de modulo SFP',
            'estado' => 'resuelto',
            'prioridad' => 'alta',
            'fecha' => '2026-09-21 08:30:00',
            'fecha_cierre' => '2026-09-21 09:15:00',
            'tiempo_atencion_minutos' => 45,
            'valoracion' => 'excelente',
            'valoracion_comentario' => 'Muy bien resuelto',
            'valoracion_fecha' => '2026-09-21 10:00:00',
            'categoria_nombre' => 'Redes',
            'tech_id' => 38,
            'tech_nombre' => 'Alexis',
            'tech_apellido' => 'Datica',
            'req_nombre' => 'Carlos',
            'req_apellido' => 'Mendoza',
            'depto_nombre' => 'Dpto. de Redes',
            'eq_id' => 101,
            'eq_serial' => 'SW-0042',
            'eq_tipo' => 'Switch',
            'eq_modelo' => 'Cisco Catalyst 2960',
            'creator_username' => 'admin',
        ];

        $comments = collect([
            (object) [
                'id' => 1,
                'comentario' => 'Modulo cambiado',
                'es_interno' => false,
                'fecha' => Carbon::now()->toDateTimeString(),
                'username' => 'alexis',
            ],
        ]);

        $attachments = collect([
            (object) [
                'id' => 1,
                'nombre_original' => 'foto_switch.png',
                'tamano_bytes' => 204800,
                'tipo_mime' => 'image/png',
                'ruta' => 'storage/uploads/foto.png',
                'fecha_subida' => Carbon::now()->toDateTimeString(),
            ],
        ]);

        $materials = collect([
            (object) [
                'id' => 1,
                'item_nombre' => 'Transceiver SFP 1G',
                'item_codigo' => 'SFP-001',
                'cantidad' => 1,
                'fecha' => Carbon::now()->toDateTimeString(),
            ],
        ]);

        $dto = TicketDetailMapper::toDTO($rawTicket, $comments, $attachments, $materials);
        $array = $dto->toArray();

        $this->assertSame(96, $array['ticket']['id']);
        $this->assertSame('#T-96', $array['ticket']['code']);
        $this->assertSame('resolved', $array['ticket']['status']);
        $this->assertSame('SW-0042', $array['asset']['serial']);
        $this->assertCount(1, $array['comments']);
        $this->assertCount(1, $array['attachments']);
        $this->assertCount(1, $array['materials']);
        $this->assertSame(5, $array['rating']['score']);
    }
}
