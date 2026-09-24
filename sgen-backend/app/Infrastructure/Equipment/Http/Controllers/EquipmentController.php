<?php

declare(strict_types=1);

namespace App\Infrastructure\Equipment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Equipment\Http\Requests\StoreEquipmentRequest;
use App\Infrastructure\Equipment\Http\Requests\UpdateEquipmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Equipment\Application\DTOs\CreateEquipmentDTO;
use Modules\Equipment\Application\DTOs\UpdateEquipmentDTO;
use Modules\Equipment\Application\UseCases\CreateEquipmentUseCase;
use Modules\Equipment\Application\UseCases\DeleteEquipmentUseCase;
use Modules\Equipment\Application\UseCases\GetEquipmentDashboardDataUseCase;
use Modules\Equipment\Application\UseCases\GetEquipmentDetailUseCase;
use Modules\Equipment\Application\UseCases\UpdateEquipmentUseCase;

final class EquipmentController extends Controller
{
    public function index(Request $request, GetEquipmentDashboardDataUseCase $useCase): Response
    {
        $filters = $request->only(['estado', 'search', 'departamento_id', 'tipo']);

        return Inertia::render('Equipment/Index', $useCase->execute($filters));
    }

    public function show(int $id, Request $request, GetEquipmentDetailUseCase $useCase): JsonResponse|Response
    {
        $equipment = $useCase->execute($id);
        abort_if($equipment === null, 404, 'Equipo no encontrado.');

        if ($request->wantsJson()) {
            return response()->json($equipment->toArray());
        }

        return Inertia::render('Equipment/Show', [
            'equipment' => $equipment->toArray(),
        ]);
    }

    public function store(StoreEquipmentRequest $request, CreateEquipmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute(CreateEquipmentDTO::fromArray($request->validated()));

        return redirect()->route('equipos.index')->with('success', 'Equipo registrado exitosamente.');
    }

    public function update(int $id, UpdateEquipmentRequest $request, UpdateEquipmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id, UpdateEquipmentDTO::fromArray($request->validated()));

        return back()->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy(int $id, DeleteEquipmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id);

        return redirect()->route('equipos.index')->with('success', 'Equipo eliminado satisfactoriamente.');
    }
}
