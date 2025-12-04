<?php
/**
 * Configuración de Tiempos de SLA (Service Level Agreement)
 * 
 * Define los tiempos máximos de respuesta en HORAS según la prioridad del ticket.
 * Puedes modificar estos valores según las necesidades de tu organización.
 */

return [
    'alta' => 4,    // 4 horas para tickets de prioridad alta
    'media' => 24,  // 24 horas (1 día) para tickets de prioridad media
    'baja' => 48    // 48 horas (2 días) para tickets de prioridad baja
];
