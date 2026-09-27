<?php

declare(strict_types=1);

namespace Modules\User\Application\UseCases;

use Modules\User\Domain\Exceptions\LastAdminProtectionException;
use Modules\User\Domain\Exceptions\SelfAccountActionException;
use Modules\User\Domain\Exceptions\UserHasActiveTicketsException;
use Modules\User\Domain\Exceptions\UserHasOperationalHistoryException;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Ports\UserRepositoryInterface;

final readonly class DeleteUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}

    public function execute(int $id, ?int $actingUserId = null): void
    {
        // Nadie puede eliminar su propia cuenta en sesión.
        if ($actingUserId !== null && $id === $actingUserId) {
            throw SelfAccountActionException::deleting();
        }

        $user = $this->repository->findById($id);
        if ($user === null) {
            throw UserNotFoundException::withId($id);
        }

        // Protección estructural: jamás puede desaparecer el último
        // administrador HABILITADO (uno inactivo no rescata el sistema).
        if ($user->role()->value === 'admin' && $this->repository->countActiveAdmins() <= 1) {
            throw LastAdminProtectionException::deleting();
        }

        // Un técnico con tickets activos no se elimina: primero se reasigna su carga.
        $activeTicketIds = $this->repository->activeTicketIdsAssignedTo($id);
        if ($activeTicketIds !== []) {
            throw UserHasActiveTicketsException::forUser($user->username(), $activeTicketIds);
        }

        // Quien tiene historial operativo (movimientos, comentarios) es parte
        // de la trazabilidad: el registro se preserva, no se destruye.
        $referencias = $this->repository->operationalReferenceCounts($id);
        if ($referencias !== []) {
            throw UserHasOperationalHistoryException::forUser($user->username(), $referencias);
        }

        $this->repository->delete($id);
    }
}
