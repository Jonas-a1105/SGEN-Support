<?php

declare(strict_types=1);

namespace Modules\Notification\Domain\Enums\NotificationType;

enum NotificationType: string
{
    case TICKET_ASIGNADO = 'ticket_asignado';
    case TICKET_VENCIMIENTO = 'ticket_vencimiento';
    case TICKET_ESTADO_CAMBIADO = 'ticket_estado_cambiado';
    case MANTENIMIENTO_PROXIMO = 'mantenimiento_proximo';
    case INVENTARIO_BAJO_STOCK = 'inventario_bajo_stock';
    case SISTEMA_GENERAL = 'sistema_general';

    public function label(): string
    {
        return match($this) {
            self::TICKET_ASIGNADO => 'Ticket Asignado',
            self::TICKET_VENCIMIENTO => 'Ticket por Vencer',
            self::TICKET_ESTADO_CAMBIADO => 'Cambio de Estado',
            self::MANTENIMIENTO_PROXIMO => 'Mantenimiento Próximo',
            self::INVENTARIO_BAJO_STOCK => 'Inventario Bajo Stock',
            self::SISTEMA_GENERAL => 'Notificación del Sistema',
        };
    }
}
