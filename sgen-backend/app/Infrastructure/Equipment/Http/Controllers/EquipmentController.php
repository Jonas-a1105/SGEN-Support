<?php

declare(strict_types=1);

namespace App\Infrastructure\Equipment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Equipment\Http\Requests\RegisterEquipmentRequest;
use App\Infrastructure\Equipment\Http\Requests\StoreEquipmentRequest;
use App\Infrastructure\Equipment\Http\Requests\TransferEquipmentRequest;
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
use Modules\Equipment\Application\UseCases\ExportEquipmentExcelUseCase;
use Modules\Equipment\Application\UseCases\GenerateCustodyActPdfUseCase;
use Modules\Equipment\Application\UseCases\GetEquipmentDashboardDataUseCase;
use Modules\Equipment\Application\UseCases\GetEquipmentDetailUseCase;
use Modules\Equipment\Application\UseCases\RegisterEquipmentUseCase;
use Modules\Equipment\Application\UseCases\TransferEquipmentUseCase;
use Modules\Equipment\Application\UseCases\UpdateEquipmentUseCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class EquipmentController extends Controller
{
    public function index(Request $request, GetEquipmentDashboardDataUseCase $useCase): Response
    {
        $filters = $request->only(['estado', 'search', 'departamento_id', 'tipo']);

        return Inertia::render('Equipment/Index', $useCase->execute($filters));
    }

    public function exportExcel(Request $request, ExportEquipmentExcelUseCase $useCase): StreamedResponse
    {
        $filters = $request->only(['estado', 'search', 'departamento_id', 'tipo']);

        return $useCase->execute($filters);
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
        try {
            $useCase->execute(CreateEquipmentDTO::fromArray($request->validated()));

            return redirect()->route('equipos.index')->with('success', 'Equipo registrado exitosamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
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

    public function transfer(TransferEquipmentRequest $request, TransferEquipmentUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($request->toDTO());

            return back()->with('success', 'Equipo trasladado correctamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function register(RegisterEquipmentRequest $request, RegisterEquipmentUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($request->toDTO());

            return redirect()->route('equipos.index')->with('success', 'Equipo registrado exitosamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function generateCustodyPdf(int $id, GenerateCustodyActPdfUseCase $useCase): \Illuminate\Http\Response
    {
        return $useCase->execute($id);
    }

    public function reassign(Request $request, UpdateEquipmentUseCase $useCase): RedirectResponse
    {
        $id = (int) ($request->input('equipo_id') ?? $request->input('id'));
        if ($id <= 0) {
            return back()->with('error', 'Identificador de equipo inválido.');
        }

        $validated = $request->validate([
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'ubicacion_fisica' => ['nullable', 'string', 'max:255'],
        ]);

        $useCase->execute($id, UpdateEquipmentDTO::fromArray($validated));

        return back()->with('success', 'Ubicación y responsable de equipo reasignados correctamente.');
    }
}
