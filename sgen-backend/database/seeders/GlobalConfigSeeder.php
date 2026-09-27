<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra las claves de configuración global del negocio. Idempotente:
 * solo crea las que falten; jamás pisa un valor ajustado por un admin.
 */
class GlobalConfigSeeder extends Seeder
{
    public function run(): void
    {
        $claves = [
            // SLA · horas objetivo de resolución por prioridad (espejo de config/sla.php)
            'sla.horas_critica' => ['4', 'Horas de resolución SLA para prioridad crítica'],
            'sla.horas_alta' => ['8', 'Horas de resolución SLA para prioridad alta'],
            'sla.horas_media' => ['24', 'Horas de resolución SLA para prioridad media'],
            'sla.horas_baja' => ['48', 'Horas de resolución SLA para prioridad baja'],
            // Ciclo de vida de tickets
            'tickets.autocierre_dias' => ['7', 'Días en estado resuelto antes del autocierre programado'],
            'tickets.ventana_reapertura_dias' => ['7', 'Días tras la resolución en los que el solicitante puede reabrir el ticket'],
            // Seguridad de autenticación y sesiones
            'seguridad.login_intentos_max' => ['5', 'Intentos fallidos de login antes del bloqueo temporal'],
            'seguridad.idle_minutos' => ['30', 'Minutos de inactividad antes del cierre automático de sesión'],
            'seguridad.sesiones_concurrentes_max' => ['3', 'Sesiones activas simultáneas por usuario (las más recientes ganan)'],
            // Notificaciones por correo
            'notificaciones.email_cooldown_min' => ['30', 'Minutos de cooldown entre correos del mismo tipo por usuario'],
        ];

        // Base de feriados (Venezuela, saneada y verificable). Los móviles de
        // carnaval/lunes feriados quedan para completeo a mano del año operativo.
        $feriados = [
            ['2026-01-01', 'Año Nuevo'],
            ['2026-01-06', 'Reyes Magos'],
            ['2026-02-16', 'Lunes de Carnaval'],
            ['2026-02-17', 'Martes de Carnaval'],
            ['2026-04-02', 'Jueves Santo (Jueves Santo)'],
            ['2026-04-03', 'Viernes Santo'],
            ['2026-04-02', 'Jueves Santo'],
            ['2026-04-19', 'Declaración de la Independencia'],
            ['2026-05-01', 'Día del Trabajador'],
            ['2026-06-24', 'Aniversario de la Batalla de Carabobo'],
            ['2026-07-05', 'Día de la Independencia'],
            ['2026-07-24', 'Natalicio de Simón Bolívar'],
            ['2026-10-12', 'Día de la Resistencia Indígena'],
            ['2026-12-24', 'Nochebuena / Vigilia'],
            ['2026-12-25', 'Navidad'],
            ['2026-12-31', 'Fin de año'],
        ];

        foreach ($feriados as [$fecha, $nombre]) {
            DB::table('feriados')->updateOrInsert(
                ['pais' => 'VE', 'fecha' => $fecha],
                ['nombre' => $nombre, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        foreach ($claves as $clave => [$valor, $descripcion]) {
            DB::table('configuracion_global')->updateOrInsert(
                ['clave' => $clave],
                [
                    // Solo define el valor si la clave aún no existía.
                    'valor' => DB::table('configuracion_global')->where('clave', $clave)->value('valor') ?? $valor,
                    'descripcion' => $descripcion,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
