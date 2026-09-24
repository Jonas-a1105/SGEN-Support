<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Modules\Support\Application\DTOs\TicketDetailDTO;
use Modules\Support\Application\UseCases\GetTicketDetailUseCase;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class GetTicketDetailUseCaseTest extends TestCase
{
    public function test_can_execute_get_ticket_detail_use_case(): void
    {
        $mockRepo = $this->createMock(SupportRepositoryInterface::class);

        $dto = new TicketDetailDTO(
            ticket: [
                'id' => 96,
                'title' => 'SOPORTE EN SERVIDORES Y SISTEMAS PRODUMIL',
                'status' => 'resuelto',
                'status_variant' => 'resolved',
                'status_label' => 'Resuelto',
                'priority' => 'media',
                'priority_label' => 'Prioridad Media',
                'category' => 'General',
                'department' => 'Soporte Técnico',
                'requester' => 'Herdil Nair Gutierrez',
                'requester_dept' => 'Dpto. de Recursos Humanos',
                'tech_id' => 38,
                'tech_name' => 'Alexis Datica',
                'tech_initial' => 'A',
                'report_date' => '19/12/2025 03:17 PM',
                'close_date' => '19/12/2025 03:17 PM',
                'attention_time' => '42 minutos',
                'sla_on_time' => true,
            ],
            asset: [
                'id' => 238,
                'serial' => '00151',
                'type' => 'Computadora',
                'model' => 'I5-3470',
                'department' => 'Dpto. de Recursos Humanos',
                'assigned_to' => 'Herdil Nair Gutierrez',
            ],
            comments: [
                [
                    'id' => 1,
                    'comment' => 'Soporte verificado satisfactoriamente',
                    'is_internal' => false,
                    'time_ago' => 'Hace un momento',
                    'date' => '19/12/2025 03:17',
                    'author' => 'admin',
                    'initial' => 'AD',
                ],
            ],
            attachments: [],
            materials: [],
            rating: null,
            logEntries: []
        );

        $mockRepo->expects($this->once())
            ->method('findById')
            ->with(96)
            ->willReturn($dto);

        $mockRepo->expects($this->once())
            ->method('getFormOptions')
            ->willReturn(['technicians' => [], 'equipments' => []]);

        $useCase = new GetTicketDetailUseCase($mockRepo);
        $result = $useCase->execute(96);

        $this->assertIsArray($result);
        $this->assertEquals(96, $result['ticket']['id']);
        $this->assertEquals('00151', $result['asset']['serial']);
        $this->assertEquals('Alexis Datica', $result['ticket']['tech_name']);
        $this->assertCount(1, $result['comments']);
        $this->assertArrayHasKey('options', $result);
    }
}
