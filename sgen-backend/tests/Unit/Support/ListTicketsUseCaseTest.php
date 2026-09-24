<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Modules\Support\Application\DTOs\SupportKpisDTO;
use Modules\Support\Application\UseCases\ListTicketsUseCase;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class ListTicketsUseCaseTest extends TestCase
{
    public function test_can_execute_list_tickets_use_case_and_return_data(): void
    {
        $mockRepo = $this->createMock(SupportRepositoryInterface::class);

        $mockRepo->expects($this->once())
            ->method('getKpis')
            ->willReturn(new SupportKpisDTO(
                criticalPending: 1,
                generalQueue: 34,
                inProcess: 2,
                myAssignments: 4
            ));

        $mockRepo->expects($this->once())
            ->method('listTickets')
            ->with(['estado' => 'all'])
            ->willReturn([
                [
                    'id' => 96,
                    'title' => 'SOPORTE EN SERVIDORES Y SISTEMAS PRODUMIL',
                    'requester' => 'Herdil Nair Gutierrez',
                    'department' => 'Dpto. de Recursos Humanos',
                    'tech_name' => 'Alexis Datica',
                    'tech_dept' => 'Dpto. de Informática',
                    'status' => 'resuelto',
                    'status_label' => 'Resuelto',
                    'status_variant' => 'resolved',
                    'category' => 'SERVIDORES',
                    'date' => '19/12/2025',
                    'comments_count' => 1,
                    'is_mine' => true,
                    'description' => 'SOPORTE EN SERVIDORES',
                ],
            ]);

        $mockRepo->expects($this->once())
            ->method('getFormOptions')
            ->willReturn(['technicians' => [], 'equipments' => []]);

        $useCase = new ListTicketsUseCase($mockRepo);
        $result = $useCase->execute(['estado' => 'all'], null);

        $this->assertArrayHasKey('kpis', $result);
        $this->assertIsArray($result['kpis']);
        $this->assertEquals(1, $result['kpis']['critical_pending']);
        $this->assertEquals(34, $result['kpis']['general_queue']);
        $this->assertCount(1, $result['tickets']);
        $this->assertEquals(96, $result['tickets'][0]['id']);
        $this->assertEquals('resolved', $result['tickets'][0]['status_variant']);
        $this->assertArrayHasKey('options', $result);
    }
}
