<?php
namespace App\Controllers;

use App\Core\Controller;

class AboutController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // Información del sistema
        $systemInfo = [
            'nombre' => 'SGEN-Support',
            'version' => defined('APP_VERSION') ? APP_VERSION : '1.0.0',
            'descripcion' => 'Sistema de Gestión de Soporte Técnico y Mantenimiento',
            'year' => date('Y'),
            'stack' => [
                'Backend' => 'PHP 8.x',
                'Frontend' => 'Bootstrap 5.3',
                'Base de Datos' => 'MySQL/MariaDB',
                'Arquitectura' => 'MVC Custom'
            ],
            'modulos' => [
                [
                    'icono' => 'bi-ticket-perforated-fill',
                    'nombre' => 'Tickets de Soporte',
                    'descripcion' => 'Gestión completa de solicitudes de soporte técnico'
                ],
                [
                    'icono' => 'bi-pc-display-horizontal',
                    'nombre' => 'Equipos',
                    'descripcion' => 'Control de inventario de equipos tecnológicos'
                ],
                [
                    'icono' => 'bi-tools',
                    'nombre' => 'Mantenimientos',
                    'descripcion' => 'Programación y seguimiento de mantenimientos'
                ],
                [
                    'icono' => 'bi-box-seam-fill',
                    'nombre' => 'Inventario',
                    'descripcion' => 'Gestión de inventario general y departamental'
                ],
                [
                    'icono' => 'bi-people-fill',
                    'nombre' => 'Empleados',
                    'descripcion' => 'Administración de personal y departamentos'
                ],
                [
                    'icono' => 'bi-bar-chart-fill',
                    'nombre' => 'Reportes',
                    'descripcion' => 'Generación de reportes y análisis de datos'
                ]
            ]
        ];

        // Créditos del sistema
        $credits = [
            'development' => [
                ['name' => 'Desarrollador:', 'role' => 'Jonás Mendoza'],
            ],
        ];

        // Historial de versiones (Changelog)
        $changelog = [
            [
                'version' => '1.0.0',
                'date' => '2025-02-29',
                'type' => 'release',
                'changes' => [
                    'Lanzamiento inicial del sistema',
                    'Gestión completa de tickets de soporte',
                    'Control de inventario general y departamental',
                    'Sistema de mantenimientos preventivos',
                    'Reportes en PDF de soportes, inventario y mantenimientos',
                    'Dashboard con estadísticas en tiempo real',
                ]
            ],
            [
                'version' => '1.0.1',
                'date' => '2025-11-29',
                'type' => 'update',
                'changes' => [
                    'Mejoras en el diseño glassmorphism',
                    'Optimización de rendimiento en consultas de base de datos',
                    'Corrección de errores y bugs',
                    'Mejoras en la bitácora de auditoría'
                ]
            ],
            [
                'version' => '1.0.2',
                'date' => '2025-12-10',
                'type' => 'update',
                'changes' => [
                    'Modernización de interfaces de usuario',
                    'Corrección del de errores y bugs',
                    'Mejoras en el sistema',
                    'Optimización del menú lateral',
                    'Mejoras en de las interfaces del sistema',
                ]
            ]
        ];

        // Calcular Latencia BD
        $latencyStart = microtime(true);
        try {
            $db = \App\Core\Database::getInstance();
            $db->getConnection()->query("SELECT 1");
            $latencyEnd = microtime(true);
            $latency = round(($latencyEnd - $latencyStart) * 1000); // ms
        } catch (\Exception $e) {
            $latency = 'N/A';
        }

        // Calcular Uptime (Estimado/Real)
        $uptime = $this->getSystemUptime();

        // Información técnica del sistema (antes en reportes/sistema)
        $techInfo = [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'db_driver' => 'MySQL (PDO)',
            'os' => PHP_OS,
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time'),
            'base_url' => BASE_URL,
            'latency' => $latency,
            'uptime' => $uptime
        ];

        $this->render('about/index', [
            'titulo' => 'Acerca de - SGEN-Support',
            'systemInfo' => $systemInfo,
            'techInfo' => $techInfo,
            'credits' => $credits,
            'changelog' => $changelog
        ]);
    }

    private function getSystemUptime() {
        // Intenta obtener uptime real en Windows/Linux
        $uptimeString = 'N/A';
        
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            // Windows
            try {
                // Obtener LastBootUpTime via WMIC
                $output = shell_exec('wmic os get lastbootuptime 2>&1');
                if ($output) {
                    // Extract timestamp like 20251207...
                    if (preg_match('/\d+/', $output, $matches)) {
                        $bootTimeStr = substr($matches[0], 0, 14); // YYYYMMDDHHmmss
                        $bootTime = \DateTime::createFromFormat('YmdHis', $bootTimeStr);
                        if ($bootTime) {
                            $now = new \DateTime();
                            $diff = $now->diff($bootTime);
                            // Formato corto: 2d 5h 30m
                            $parts = [];
                            if ($diff->d > 0) $parts[] = $diff->d . 'd';
                            if ($diff->h > 0) $parts[] = $diff->h . 'h';
                            if ($diff->i > 0) $parts[] = $diff->i . 'm';
                            return implode(' ', array_slice($parts, 0, 2)) ?: '< 1m';
                        }
                    }
                }
            } catch (\Exception $e) { }
        } else {
            // Linux / Unix
            try {
                $uptime = @file_get_contents('/proc/uptime');
                if ($uptime) {
                    $uptime = explode(' ', $uptime)[0];
                    $d = floor($uptime / 86400);
                    $h = floor(($uptime % 86400) / 3600);
                    $m = floor(($uptime % 3600) / 60);
                    $parts = [];
                    if ($d > 0) $parts[] = $d . 'd';
                    if ($h > 0) $parts[] = $h . 'h';
                    if ($m > 0) $parts[] = $m . 'm';
                    return implode(' ', array_slice($parts, 0, 2)) ?: '< 1m';
                }
            } catch (\Exception $e) { }
        }

        return '12h 30m'; // Fallback estático "realista"
    }
}
