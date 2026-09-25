<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SLA · Horas de resolución por prioridad
    |--------------------------------------------------------------------------
    |
    | Fuente única de verdad para el cálculo de fecha_vencimiento de tickets.
    | Consumido por SlaPolicy (dominio de Soporte).
    |
    */

    'hours_by_priority' => [
        'critica' => 4,
        'alta' => 8,
        'media' => 24,
        'baja' => 48,
    ],
];
