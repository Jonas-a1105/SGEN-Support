<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class GetTicketDetailUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository
    ) {}

    /**
     * @return array{
     *     detail: array<string, mixed>,
     *     options: array<string, mixed>
     * }|null
     */
    public function execute(int $id, ?int $forUserId = null): ?array
    {
        // Alcance por fila: un operador solo puede ver sus propios tickets.
        // Se devuelve null (404) para no confirmar la existencia del recurso.
        if ($forUserId !== null && $this->esOperador($forUserId) && ! $this->esPropietario($id, $forUserId)) {
            return null;
        }

        $dto = $this->repository->findById($id);
        if ($dto === null) {
            return null;
        }

        $options = $this->repository->getFormOptions();

        return array_merge($dto->toArray(), [
            'options' => $options,
        ]);
    }

    private function esOperador(int $userId): bool
    {
        return DB::table('usuarios')->where('id', $userId)->value('rol') === 'operador';
    }

    private function esPropietario(int $ticketId, int $userId): bool
    {
        return (int) DB::table('soportes')->where('id', $ticketId)->value('usuario_creacion_id') === $userId;
    }
}
