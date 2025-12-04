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
            'version' => '1.0.1',
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
                    'Sistema de notificaciones integrado'
                ]
            ],
            [
                'version' => '1.0.1',
                'date' => '2025-11-29',
                'type' => 'update',
                'changes' => [
                    'Mejoras en el diseño glassmorphism',
                    'Optimización de rendimiento en consultas de base de datos',
                    'Corrección de errores en notificaciones',
                    'Mejoras en la bitácora de auditoría'
                ]
            ]
        ];

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
        ];

        $this->render('about/index', [
            'titulo' => 'Acerca de - SGEN-Support',
            'systemInfo' => $systemInfo,
            'techInfo' => $techInfo,
            'credits' => $credits,
            'changelog' => $changelog
        ]);
    }
}
