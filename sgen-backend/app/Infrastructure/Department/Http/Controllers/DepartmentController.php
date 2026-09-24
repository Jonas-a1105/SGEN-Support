<?php

declare(strict_types=1);

namespace App\Infrastructure\Department\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Department\Http\Requests\StoreDepartmentRequest;
use App\Infrastructure\Department\Http\Requests\UpdateDepartmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Department\Application\DTOs\CreateDepartmentDTO;
use Modules\Department\Application\DTOs\UpdateDepartmentDTO;
use Modules\Department\Application\UseCases\AssignEmployeeToDepartmentUseCase;
use Modules\Department\Application\UseCases\AssignEquipmentToDepartmentUseCase;
use Modules\Department\Application\UseCases\CreateDepartmentUseCase;
use Modules\Department\Application\UseCases\DeleteDepartmentUseCase;
use Modules\Department\Application\UseCases\GetDepartmentDetailUseCase;
use Modules\Department\Application\UseCases\GetDepartmentListUseCase;
use Modules\Department\Application\UseCases\RemoveEmployeeFromDepartmentUseCase;
use Modules\Department\Application\UseCases\RemoveEquipmentFromDepartmentUseCase;
use Modules\Department\Application\UseCases\UpdateDepartmentUseCase;

final class DepartmentController extends Controller
{
    public function index(Request $request, GetDepartmentListUseCase $useCase): Response
    {
        $filters = $request->only(['search']);

        return Inertia::render('Department/Index', $useCase->execute($filters));
    }

    public function show(int $id, Request $request, GetDepartmentDetailUseCase $useCase): JsonResponse|Response
    {
        $department = $useCase->execute($id);
        abort_if($department === null, 404, 'Departamento no encontrado.');

        if ($request->wantsJson()) {
            return response()->json($department->toArray());
        }

        return Inertia::render('Department/Show', [
            'department' => $department->toArray(),
        ]);
    }

    public function store(StoreDepartmentRequest $request, CreateDepartmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute(CreateDepartmentDTO::fromArray($request->validated()));

        return redirect()->route('departamentos.index')->with('success', 'Departamento creado exitosamente.');
    }

    public function update(int $id, UpdateDepartmentRequest $request, UpdateDepartmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id, UpdateDepartmentDTO::fromArray($request->validated()));

        return back()->with('success', 'Departamento actualizado correctamente.');
    }

    public function destroy(int $id, DeleteDepartmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id);

        return redirect()->route('departamentos.index')->with('success', 'Departamento eliminado satisfactoriamente.');
    }

    public function assignEmployee(int $id, Request $request, AssignEmployeeToDepartmentUseCase $useCase): RedirectResponse
    {
        $request->validate(['empleado_id' => ['required', 'integer', 'exists:empleados,id']]);
        $useCase->execute($id, (int) $request->input('empleado_id'));

        return back()->with('success', 'Empleado asignado al departamento.');
    }

    public function removeEmployee(int $id, int $employeeId, RemoveEmployeeFromDepartmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute($employeeId);

        return back()->with('success', 'Empleado desvinculado del departamento.');
    }

    public function assignEquipment(int $id, Request $request, AssignEquipmentToDepartmentUseCase $useCase): RedirectResponse
    {
        $request->validate(['equipo_id' => ['required', 'integer', 'exists:equipos,id']]);
        $useCase->execute($id, (int) $request->input('equipo_id'));

        return back()->with('success', 'Equipo asignado al departamento.');
    }

    public function removeEquipment(int $id, int $equipmentId, RemoveEquipmentFromDepartmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute($equipmentId);

        return back()->with('success', 'Equipo desvinculado del departamento.');
    }
}
