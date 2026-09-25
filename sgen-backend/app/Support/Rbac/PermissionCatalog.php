<?php

declare(strict_types=1);

namespace App\Support\Rbac;

/**
 * Catálogo único de permisos de la aplicación y su matriz permiso → roles.
 *
 * Fuente de verdad del RBAC: el seeder provisiona Spatie a partir de aquí
 * y el modelo User sincroniza su rol Spatie desde la columna `rol`.
 *
 * Convención: "<módulo>.<acción>" con acciones view / create / manage / delete.
 * Las rutas GET no sensibles solo exigen autenticación; las mutaciones
 * exigen el permiso de gestión correspondiente.
 */
final class PermissionCatalog
{
    /**
     * Mapa permiso → roles del sistema que lo poseen.
     *
     * @var array<string, list<string>>
     */
    private const MATRIX = [
        // Soporte (tickets): ciclo de vida operativo vs borrado destructivo
        'soportes.manage' => ['admin', 'tecnico'],
        'soportes.delete' => ['admin'],

        // Equipos e inventario (ITAM)
        'equipos.manage' => ['admin', 'tecnico'],
        'inventario.manage' => ['admin', 'tecnico'],

        // Mantenimientos
        'mantenimientos.manage' => ['admin', 'tecnico'],

        // Organización: catálogos y personal — solo administración
        'personal.manage' => ['admin'],
        'departamentos.manage' => ['admin'],
        'categorias.manage' => ['admin'],

        // Administración del sistema
        'usuarios.manage' => ['admin'],
        'auditoria.view' => ['admin'],
        'configuracion.manage' => ['admin'],

        // Reportes: lectura analítica, inaccesible para operadores
        'reportes.view' => ['admin', 'tecnico', 'consultor'],
    ];

    /** @var list<string> */
    public const ROLES = ['admin', 'tecnico', 'consultor', 'operador'];

    /**
     * @return list<string>
     */
    public static function permissions(): array
    {
        return array_keys(self::MATRIX);
    }

    /**
     * @return list<string>
     */
    public static function rolesFor(string $permission): array
    {
        return self::MATRIX[$permission] ?? [];
    }

    /**
     * @return list<string>
     */
    public static function permissionsForRole(string $role): array
    {
        return array_keys(array_filter(
            self::MATRIX,
            static fn (array $roles): bool => in_array($role, $roles, true)
        ));
    }
}
