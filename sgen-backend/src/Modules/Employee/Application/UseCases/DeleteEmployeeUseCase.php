<?php

declare(strict_types=1);

namespace Modules\Employee\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Domain\Exceptions\EmployeeHasActiveCustodiesException;
use Modules\Employee\Domain\Ports\EmployeeRepositoryInterface;

/**
 * Desvinculación de personal (soft delete) con regla dura de offboarding:
 * nadie se retira del padrón con equipos bajo su custodia pendientes.
 */
final class DeleteEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $repository
    ) {}

    public function execute(int $id): void
    {
        // Regla #27: el offboarding exige recuperar primero TODA la custodia
        // vigente. La consulta directa evita acoplar el puerto de Employee
        // al módulo de Equipment (la guía de custodia es la fuente de verdad).
        $custodiasActivas = DB::table('custodias')
            ->where('empleado_id', $id)
            ->whereNull('fecha_fin')
            ->pluck('id')
            ->map(static fn ($cid): int => (int) $cid)
            ->all();

        if ($custodiasActivas !== []) {
            $nombre = trim((string) (DB::table('empleados')->where('id', $id)->value('nombre') ?? '')
                .' '.(string) (DB::table('empleados')->where('id', $id)->value('apellido') ?? ''));

            throw EmployeeHasActiveCustodiesException::forEmployee($nombre !== '' ? $nombre : "empleado #{$id}", $custodiasActivas);
        }

        $this->repository->delete($id);
    }
}
