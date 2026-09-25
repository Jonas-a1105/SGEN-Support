<?php

declare(strict_types=1);

namespace Modules\About\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\About\Application\DTOs\SystemAboutDTO;

final readonly class GetSystemAboutUseCase
{
    public function execute(): SystemAboutDTO
    {
        $phpVersion = PHP_VERSION;
        $dbDriver = 'MySQL (PDO)';

        try {
            $dbVersion = DB::select('SELECT VERSION() as v')[0]->v ?? null;
            $dbDriver = $dbVersion ?? ucfirst(DB::connection()->getDriverName());
        } catch (\Throwable $e) {
            report($e);
            $dbDriver = ucfirst(DB::connection()->getDriverName());
        }

        $technicalCards = [
            [
                'kicker' => 'BACKEND',
                'title' => "PHP {$phpVersion}",
                'subtitle' => 'Laravel 12 / Inertia',
                'type' => 'backend',
            ],
            [
                'kicker' => 'FRONTEND',
                'title' => 'Vue 3 / TypeScript',
                'subtitle' => 'Modern UI System',
                'type' => 'frontend',
            ],
            [
                'kicker' => 'BASE DE DATOS',
                'title' => 'MySQL',
                'subtitle' => $dbDriver,
                'type' => 'database',
            ],
            [
                'kicker' => 'ARQUITECTURA',
                'title' => 'DDD / Clean Arch',
                'subtitle' => 'Modular & Decoupled',
                'type' => 'architecture',
            ],
        ];

        $modules = [
            [
                'name' => 'Gestión de Soportes',
                'description' => 'Tickets, SLA, bitácora, repuestos y asignación técnica en tiempo real.',
                'icon' => 'support',
            ],
            [
                'name' => 'Inventario General',
                'description' => 'Control de stock, alertas por agotamiento, SKU y transferencias.',
                'icon' => 'inventory',
            ],
            [
                'name' => 'Gestión de Equipos',
                'description' => 'Inventario de activos tecnológicos, asignación por empleado y estado operativo.',
                'icon' => 'equipment',
            ],
            [
                'name' => 'Departamentos',
                'description' => 'Estructura organizativa, líderes asignados y porcentajes de inventario.',
                'icon' => 'department',
            ],
            [
                'name' => 'Directorio de Personal',
                'description' => 'Colaboradores, credenciales digitales y vinculación a cuentas de usuario.',
                'icon' => 'employee',
            ],
            [
                'name' => 'Auditoría del Sistema',
                'description' => 'Monitoreo de seguridad, registro de sesiones y bitácora de actividad.',
                'icon' => 'audit',
            ],
            [
                'name' => 'Gestión de Categorías',
                'description' => 'Tipificación de incidencias, colores distintivos e iconos representativos.',
                'icon' => 'category',
            ],
        ];

        $environment = [
            'os' => PHP_OS,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI / Built-in',
            'php_version' => $phpVersion,
            'database' => $dbDriver,
            'memory_limit' => ini_get('memory_limit') ?: '128M',
            'upload_max_filesize' => ini_get('upload_max_filesize') ?: '40M',
        ];

        $versions = [
            [
                'version' => 'v1.0.28 (Stable)',
                'date' => 'Septiembre 2026',
                'notes' => [
                    'Implementación de módulos Categorías, Usuarios, Auditoría, Configuración y Acerca.',
                    'Estandarización visual: cero estilos en línea, cero sombras y tipografía institucional.',
                    'Arquitectura limpia desacoplada (DDD) con cobertura completa de pruebas automatizadas.',
                ],
            ],
            [
                'version' => 'v1.0.20',
                'date' => 'Agosto 2026',
                'notes' => [
                    'Lanzamiento de módulos de Equipos Tecnológicos y Directorio de Personal.',
                    'Incorporación de Live Preview Card para credenciales de colaboradores.',
                ],
            ],
            [
                'version' => 'v1.0.0',
                'date' => 'Marzo 2026',
                'notes' => [
                    'Migración inicial al motor Laravel e integración con Inertia.js y Vue 3.',
                ],
            ],
        ];

        return new SystemAboutDTO(
            appName: 'SGEN-Support',
            appVersion: 'v1.0.28 (Stable)',
            systemStatus: '100% Operativo',
            technicalCards: $technicalCards,
            modules: $modules,
            environment: $environment,
            versions: $versions
        );
    }
}
