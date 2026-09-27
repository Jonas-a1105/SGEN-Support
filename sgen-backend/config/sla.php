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

    /*
    |--------------------------------------------------------------------------
    | SLA laboral: calendario operativo (módulo 09)
    |--------------------------------------------------------------------------
    |
    | Horario de atención en el que el reloj del SLA *corre*. Los fines de
    | semana y los feriados quedan fuera del conteo; una pausa extiende el
    | plazo exactamente sus minutos laborales.
    |
    */

    'operativo' => [
        'dias_laborales' => [1, 2, 3, 4, 5], // Lunes (1) a Viernes (5)
        'hora_inicio' => '08:00',
        'hora_fin' => '18:00',
    ],
];
