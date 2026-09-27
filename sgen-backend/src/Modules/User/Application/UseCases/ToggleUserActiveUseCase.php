<?php

declare(strict_types=1);

namespace Modules\User\Application\UseCases;

use Modules\User\Domain\Exceptions\LastAdminProtectionException;
use Modules\User\Domain\Exceptions\SelfAccountActionException;
use Modules\User\Domain\Exceptions\UserHasActiveTicketsException;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Ports\UserRepositoryInterface;

/**
 * Activa o desactiva una cuenta de usuario (alternancia en un paso).
 *
 * Desactivar preserva identidad e historial —la vía correcta para quien
 * tiene trazabilidad operativa— pero aplica las mismas protecciones que
 * el borrado: ni el último administrador ni un técnico con carga activa
 * pueden quedar fuera de servicio.
 *
 * @return bool nuevo estado de la cuenta (true = activa)
 */
final readonly class ToggleUserActiveUseCase
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}

    public function execute(int $id, int $actingUserId): bool
    {
        // Nadie puede dejarse fuera del sistema desde su propia sesión.
        if ($id === $actingUserId) {
            throw SelfAccountActionException::deactivating();
        }

        $user = $this->repository->findById($id);
        if ($user === null) {
            throw UserNotFoundException::withId($id);
        }

        $nuevoEstado = ! $user->isActive();

        if (! $nuevoEstado) {
            // La administración nunca puede quedar sin acceso (solo cuentan
            // administradores HABILITADOS: uno inactivo no rescata).
            if ($user->role()->value === 'admin' && $this->repository->countActiveAdmins() <= 1) {
                throw LastAdminProtectionException::deactivating();
            }

            // Regla #26: no se desactiva a quien tiene tickets activos sin reasignar.
            $activeTicketIds = $this->repository->activeTicketIdsAssignedTo($id);
            if ($activeTicketIds !== []) {
                throw UserHasActiveTicketsException::forUser($user->username(), $activeTicketIds);
            }
        }

        $this->repository->setActive($id, $nuevoEstado);

        return $nuevoEstado;
    }
}
