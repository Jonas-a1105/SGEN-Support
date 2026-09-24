<?php

declare(strict_types=1);

namespace Modules\Dashboard\Application\DTOs;

final class DashboardMetricsDTO
{
    /**
     * @param array{
     *     total_equipos: int,
     *     tickets_pendientes: int,
     *     tickets_en_proceso: int,
     *     tickets_resueltos: int,
     *     cambio_equipos: string,
     *     cambio_pendientes: string,
     *     cambio_proceso: string,
     *     cambio_resueltos: string
     * } $kpis
     * @param array{
     *     year: int,
     *     months: array<int, string>,
     *     values: array<int, int>,
     *     total: int,
     *     available_years: array<int, int>
     * } $ticketVolume
     * @param array{
     *     nombre: string,
     *     cantidad: int,
     *     porcentaje: int
     * } $topCategory
     * @param array{
     *     used: int,
     *     repair: int,
     *     available: int,
     *     down: int,
     *     operative_percentage: int
     * } $inventoryHealth
     * @param array<int, array{
     *     name: string,
     *     score: int,
     *     percentage: int
     * }> $technicians
     * @param array<int, array{
     *     id: int|string,
     *     title: string,
     *     time_ago: string,
     *     badge: string,
     *     type: string
     * }> $recentActivity
     * @param array{
     *     pending: array<int, array{id: int, titulo: string, prioridad: string, created_at: string}>,
     *     in_process: array<int, array{id: int, titulo: string, prioridad: string, created_at: string}>
     * } $ticketsByStatus
     */
    public function __construct(
        public readonly array $kpis,
        public readonly array $ticketVolume,
        public readonly array $topCategory,
        public readonly array $inventoryHealth,
        public readonly array $technicians,
        public readonly array $recentActivity,
        public readonly array $ticketsByStatus
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'kpis' => $this->kpis,
            'ticket_volume' => $this->ticketVolume,
            'top_category' => $this->topCategory,
            'inventory_health' => $this->inventoryHealth,
            'technicians' => $this->technicians,
            'recent_activity' => $this->recentActivity,
            'tickets_by_status' => $this->ticketsByStatus,
        ];
    }
}
