<?php
/**
 * Authorization Service - Centraliza toda la lógica de permisos
 * Reemplaza los restrictTo() dispersos y duplicados
 */

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;

class AuthorizationService
{
    /** @var array<string, array<string, string>> */
    private const PERMISSION_MAP = [
        // ticket permissions
        'ticket.create'           => ['admin', 'tecnico', 'consultor'],
        'ticket.edit'             => ['admin', 'tecnico', 'consultor'], // propio o asignado
        'ticket.delete'           => ['admin'],
        'ticket.assign'           => ['admin', 'tecnico'],
        'ticket.resolve'          => ['admin', 'tecnico'],
        'ticket.hold'             => ['admin', 'tecnico'],
        'ticket.resume'           => ['admin', 'tecnico'],
        'ticket.add_consumption'  => ['admin', 'tecnico'],
        'ticket.update_closing'   => ['admin'],
        'ticket.sign'             => ['admin', 'tecnico', 'consultor'],
        'ticket.rate'             => ['admin', 'tecnico', 'consultor'],
        'ticket.add_observations' => ['admin', 'tecnico'],
        'ticket.upload_file'      => ['admin', 'tecnico', 'consultor'],
        'ticket.delete_file'      => ['admin', 'tecnico'],
        // comment permissions
        'comment.edit'            => ['admin'], // own or admin
        'comment.delete'          => ['admin'], // own or admin
        // bulk
        'bulk.delete_comments'    => ['admin'],
        'bulk.delete_tickets'     => ['admin'],
    ];

    public function __construct(
        private readonly TicketService $ticketService
    ) {}

    /**
     * Verifica permiso por rol + ownership (si aplica)
     */
    public function requirePermission(string $permission, int $resourceId = 0): void
    {
        $userRole = $_SESSION['rol'] ?? '';
        $userId = $_SESSION['user_id'] ?? 0;

        // 1. Check role permission
        $allowedRoles = self::PERMISSION_MAP[$permission] ?? ['admin'];
        if (!in_array($userRole, $allowedRoles, true)) {
            throw new ForbiddenException("Rol '{$userRole}' no tiene permiso '{$permission}'.");
        }

        // 2. Check ownership for specific permissions
        if ($resourceId > 0 && in_array($permission, ['ticket.edit', 'ticket.delete', 'comment.edit', 'comment.delete'], true)) {
            $this->checkOwnership($permission, $resourceId, $userId, $userRole);
        }
    }

    /**
     * Verifica ownership delegando al service correspondiente
     */
    private function checkOwnership(string $permission, int $resourceId, int $userId, string $role): void
    {
        $can = match ($permission) {
            'ticket.edit', 'ticket.delete' => $this->ticketService->puedeEditar($resourceId, $userId, $role),
            'comment.edit', 'comment.delete' => $this->ticketService->puedeEditarComentario($resourceId, $userId, $role),
            default => true
        };

        if (!$can) {
            throw new ForbiddenException("No tienes permiso para {$permission} en el recurso #{$resourceId}.");
        }
    }

    /**
     * Shortcut para requireRole
     */
    public function requireRole(string $role): void
    {
        if (($_SESSION['rol'] ?? '') !== $role) {
            throw new ForbiddenException("Se requiere rol '{$role}'.");
        }
    }

    /**
     * Verifica si usuario tiene permiso (bool, sin exception)
     */
    public function can(string $permission, int $resourceId = 0): bool
    {
        try {
            $this->requirePermission($permission, $resourceId);
            return true;
        } catch (ForbiddenException) {
            return false;
        }
    }
}