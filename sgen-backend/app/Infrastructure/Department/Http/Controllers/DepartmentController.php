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
use Modules\Department\Application\UseCases\CreateDepartmentUseCase;
use Modules\Department\Application\UseCases\DeleteDepartmentUseCase;
use Modules\Department\Application\UseCases\GetDepartmentDetailUseCase;
use Modules\Department\Application\UseCases\GetDepartmentListUseCase;
use Modules\Department\Application\UseCases\UpdateDepartmentUseCase;

final class DepartmentController extends Controller
{
    public function index(Request $request, GetDepartmentListUseCase $useCase): Response
    {
        $filters = $request->only(['search']);

        return Inertia::render('Department/Index', $useCase->execute($filters));
    }

    public function show(int $id, GetDepartmentDetailUseCase $useCase): JsonResponse
    {
        $department = $useCase->execute($id);
        abort_if($department === null, 404, 'Departamento no encontrado.');

        return response()->json($department->toArray());
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
}
