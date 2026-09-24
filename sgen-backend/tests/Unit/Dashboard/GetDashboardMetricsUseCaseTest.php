<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard;

use Modules\Dashboard\Application\UseCases\GetDashboardMetricsUseCase;
use Modules\Dashboard\Domain\Ports\DashboardRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class GetDashboardMetricsUseCaseTest extends TestCase
{
    public function test_can_execute_use_case_and_return_metrics_dto(): void
    {
        $mockRepo = $this->createMock(DashboardRepositoryInterface::class);

        $mockRepo->expects($this->once())
            ->method('getKpiMetrics')
            ->willReturn([
                'total_equipos' => 272,
                'tickets_pendientes' => 0,
                'tickets_en_proceso' => 0,
                'tickets_resueltos' => 34,
                'cambio_equipos' => '↗ +2 activos',
                'cambio_pendientes' => '↗ +1 solicitud',
                'cambio_proceso' => '↘ -2 atendidos',
                'cambio_resueltos' => '↗ +5 completados',
            ]);

        $mockRepo->expects($this->once())
            ->method('getTicketVolumeByYear')
            ->with(2025)
            ->willReturn([
                'year' => 2025,
                'months' => ['Ene', 'Feb'],
                'values' => [6, 18],
                'total' => 24,
                'available_years' => [2025, 2024],
            ]);

        $mockRepo->expects($this->once())
            ->method('getTopCategory')
            ->willReturn([
                'nombre' => 'Equipamiento de Canchas',
                'cantidad' => 272,
                'porcentaje' => 100,
            ]);

        $mockRepo->expects($this->once())
            ->method('getInventoryHealth')
            ->willReturn([
                'used' => 265,
                'repair' => 0,
                'available' => 7,
                'down' => 0,
                'operative_percentage' => 97,
            ]);

        $mockRepo->expects($this->once())
            ->method('getTechnicianPerformance')
            ->willReturn([
                ['name' => 'alexisd', 'score' => 34, 'percentage' => 85],
            ]);

        $mockRepo->expects($this->once())
            ->method('getRecentActivity')
            ->with(5)
            ->willReturn([
                ['id' => 1, 'title' => 'Inventario sincronizado', 'time_ago' => 'Hace 18m', 'badge' => 'OK', 'type' => 'success'],
            ]);

        $mockRepo->expects($this->once())
            ->method('getTicketsByStatus')
            ->willReturn([
                'pending' => [],
                'in_process' => [],
            ]);

        $useCase = new GetDashboardMetricsUseCase($mockRepo);
        $dto = $useCase->execute(2025);

        $this->assertEquals(272, $dto->kpis['total_equipos']);
        $this->assertEquals(97, $dto->inventoryHealth['operative_percentage']);
        $this->assertEquals(2025, $dto->ticketVolume['year']);
        $this->assertCount(1, $dto->technicians);
        $this->assertIsArray($dto->toArray());
    }
}
