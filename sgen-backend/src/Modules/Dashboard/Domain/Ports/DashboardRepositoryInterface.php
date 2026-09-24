<?php

declare(strict_types=1);

namespace Modules\Dashboard\Domain\Ports;

interface DashboardRepositoryInterface
{
    /**
     * @return array{
     *     total_equipos: int,
     *     tickets_pendientes: int,
     *     tickets_en_proceso: int,
     *     tickets_resueltos: int,
     *     cambio_equipos: string,
     *     cambio_pendientes: string,
     *     cambio_proceso: string,
     *     cambio_resueltos: string
     * }
     */
    public function getKpiMetrics(): array;

    /**
     * @return array{
     *     year: int,
     *     months: array<int, string>,
     *     values: array<int, int>,
     *     total: int,
     *     available_years: array<int, int>
     * }
     */
    public function getTicketVolumeByYear(int $year): array;

    /**
     * @return array{
     *     nombre: string,
     *     cantidad: int,
     *     porcentaje: int
     * }
     */
    public function getTopCategory(): array;

    /**
     * @return array{
     *     used: int,
     *     repair: int,
     *     available: int,
     *     down: int,
     *     operative_percentage: int
     * }
     */
    public function getInventoryHealth(): array;

    /**
     * @return array<int, array{
     *     name: string,
     *     score: int,
     *     percentage: int
     * }>
     */
    public function getTechnicianPerformance(): array;

    /**
     * @return array<int, array{
     *     id: int|string,
     *     title: string,
     *     time_ago: string,
     *     badge: string,
     *     type: string
     * }>
     */
    public function getRecentActivity(int $limit = 5): array;

    /**
     * @return array{
     *     pending: array<int, array{id: int, titulo: string, prioridad: string, created_at: string}>,
     *     in_process: array<int, array{id: int, titulo: string, prioridad: string, created_at: string}>
     * }
     */
    public function getTicketsByStatus(): array;
}
