<?php

declare(strict_types=1);

namespace App\Infrastructure\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Employee\Http\Requests\StoreEmployeeRequest;
use App\Infrastructure\Employee\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Employee\Application\DTOs\CreateEmployeeDTO;
use Modules\Employee\Application\DTOs\UpdateEmployeeDTO;
use Modules\Employee\Application\UseCases\CreateEmployeeUseCase;
use Modules\Employee\Application\UseCases\DeleteEmployeeUseCase;
use Modules\Employee\Application\UseCases\GetEmployeeDetailUseCase;
use Modules\Employee\Application\UseCases\GetEmployeeDirectoryUseCase;
use Modules\Employee\Application\UseCases\UpdateEmployeeUseCase;

final class EmployeeController extends Controller
{
    public function index(Request $request, GetEmployeeDirectoryUseCase $useCase): Response
    {
        $filters = $request->only(['departamento', 'departamento_id', 'search', 'kpi']);

        return Inertia::render('Employee/Index', $useCase->execute($filters));
    }

    public function show(int $id, GetEmployeeDetailUseCase $useCase): JsonResponse
    {
        $employee = $useCase->execute($id);
        abort_if($employee === null, 404, 'Empleado no encontrado.');

        return response()->json($employee->toArray());
    }

    public function store(StoreEmployeeRequest $request, CreateEmployeeUseCase $useCase): RedirectResponse
    {
        $useCase->execute(CreateEmployeeDTO::fromArray($request->validated()));

        return redirect()->route('personal.index')->with('success', 'Empleado registrado exitosamente.');
    }

    public function update(int $id, UpdateEmployeeRequest $request, UpdateEmployeeUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id, UpdateEmployeeDTO::fromArray($request->validated()));

        return back()->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(int $id, DeleteEmployeeUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id);

        return redirect()->route('personal.index')->with('success', 'Empleado retirado satisfactoriamente.');
    }
}
