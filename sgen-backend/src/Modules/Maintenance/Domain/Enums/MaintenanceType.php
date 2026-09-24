<?php

declare(strict_types=1);

namespace Modules\Maintenance\Domain\Enums;

enum MaintenanceType: string
{
    case PREVENTIVO = 'preventivo';
    case CORRECTIVO = 'correctivo';
    case PREDICTIVO = 'predictivo';

    public function label(): string
    {
        return match($this) {
            self::PREVENTIVO => 'Preventivo',
            self::CORRECTIVO => 'Correctivo',
            self::PREDICTIVO => 'Predictivo',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PREVENTIVO => 'Mantenimiento programado para prevenir fallos',
            self::CORRECTIVO => 'Reparación después de un fallo',
            self::PREDICTIVO => 'Basado en análisis predictivo de condición',
        };
    }
}
