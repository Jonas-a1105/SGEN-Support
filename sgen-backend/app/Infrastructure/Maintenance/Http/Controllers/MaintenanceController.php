<?php

declare(strict_types=1);

namespace App\Infrastructure\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Maintenance\Application\DTOs\CreateMaintenanceDTO;
use Modules\Maintenance\Application\DTOs\UpdateMaintenanceDTO;
use Modules\Maintenance\Application\UseCases\CompleteMaintenanceUseCase;
use Modules\Maintenance\Application\UseCases\CreateMaintenanceUseCase;
use Modules\Maintenance\Application\UseCases\DeleteMaintenanceUseCase;
use Modules\Maintenance\Application\UseCases\GetMaintenanceDetailUseCase;
use Modules\Maintenance\Application\UseCases\GetMaintenanceKpisUseCase;
use Modules\Maintenance\Application\UseCases\GetUpcomingMaintenanceUseCase;
use Modules\Maintenance\Application\UseCases\ListMaintenanceUseCase;
use Modules\Maintenance\Application\UseCases\UpdateMaintenanceUseCase;
use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class MaintenanceController extends Controller
{
    public function index(
        Request $request,
        ListMaintenanceUseCase $listUseCase,
        GetMaintenanceKpisUseCase $kpisUseCase
    ): Response {
        $filters = $request->only(['estado', 'tipo', 'search']);

        return Inertia::render('Maintenance/Index', [
            'mantenimientos' => $listUseCase->execute($filters),
            'kpis' => (array) $kpisUseCase->execute($request->user()?->id),
            'filters' => $filters,
        ]);
    }

    public function show(int $id, GetMaintenanceDetailUseCase $useCase): JsonResponse
    {
        $maintenance = $useCase->execute($id);
        abort_if($maintenance === null, 404, 'Mantenimiento no encontrado.');

        return response()->json($maintenance);
    }

    public function create(MaintenanceRepositoryInterface $repository): Response
    {
        return Inertia::render('Maintenance/Create', $repository->getFormOptions());
    }

    public function store(Request $request, CreateMaintenanceUseCase $useCase): RedirectResponse
    {
        $validated = $request->validate([
            'equipo_id' => 'required|integer|exists:equipos,id',
            'fecha' => 'required|date',
            'tipo_mantenimiento' => 'required|in:preventivo,correctivo,predictivo',
            'descripcion' => 'required|string|max:1000',
            'frecuencia' => 'nullable|in:unica,mensual,trimestral,semestral,anual',
            'proxima_fecha' => 'nullable|date',
            'costo' => 'nullable|numeric|min:0',
            'tecnico_id' => 'nullable|integer',
            'observaciones' => 'nullable|string|max:500',
            'duracion' => 'nullable|integer|min:1',
        ]);

        $dto = new CreateMaintenanceDTO(
            equipoId: (int) $validated['equipo_id'],
            fecha: $validated['fecha'],
            tipoMantenimiento: $validated['tipo_mantenimiento'],
            descripcion: $validated['descripcion'],
            frecuencia: $validated['frecuencia'] ?? 'unica',
            proximaFecha: $validated['proxima_fecha'] ?? null,
            costo: isset($validated['costo']) ? (float) $validated['costo'] : null,
            tecnicoId: $validated['tecnico_id'] ?? null,
            observaciones: $validated['observaciones'] ?? null,
            duracion: $validated['duracion'] ?? null
        );

        $id = $useCase->execute($dto, $request->user()?->id);

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento registrado exitosamente.');
    }

    public function update(int $id, Request $request, UpdateMaintenanceUseCase $useCase): RedirectResponse
    {
        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'tipo_mantenimiento' => 'nullable|in:preventivo,correctivo,predictivo',
            'estado' => 'nullable|in:pendiente,en_proceso,completado,pospuesto,cancelado',
            'descripcion' => 'nullable|string|max:1000',
            'frecuencia' => 'nullable|in:unica,mensual,trimestral,semestral,anual',
            'proxima_fecha' => 'nullable|date',
            'costo' => 'nullable|numeric|min:0',
            'tecnico_id' => 'nullable|integer',
            'observaciones' => 'nullable|string|max:500',
            'duracion' => 'nullable|integer|min:1',
        ]);

        $dto = new UpdateMaintenanceDTO(
            fecha: $validated['fecha'] ?? null,
            tipoMantenimiento: $validated['tipo_mantenimiento'] ?? null,
            estado: $validated['estado'] ?? null,
            descripcion: $validated['descripcion'] ?? null,
            frecuencia: $validated['frecuencia'] ?? null,
            proximaFecha: $validated['proxima_fecha'] ?? null,
            costo: isset($validated['costo']) ? (float) $validated['costo'] : null,
            tecnicoId: $validated['tecnico_id'] ?? null,
            observaciones: $validated['observaciones'] ?? null,
            duracion: $validated['duracion'] ?? null
        );

        $useCase->execute($id, $dto);

        return back()->with('success', 'Mantenimiento actualizado correctamente.');
    }

    public function destroy(int $id, DeleteMaintenanceUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id);

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento eliminado satisfactoriamente.');
    }

    public function complete(int $id, Request $request, CompleteMaintenanceUseCase $useCase): RedirectResponse
    {
        $validated = $request->validate([
            'observaciones' => 'nullable|string|max:500',
            'costo' => 'nullable|numeric|min:0',
        ]);

        $useCase->execute(
            $id,
            $validated['observaciones'] ?? null,
            isset($validated['costo']) ? (float) $validated['costo'] : null
        );

        return back()->with('success', 'Mantenimiento marcado como completado.');
    }

    public function postpone(int $id, Request $request, MaintenanceRepositoryInterface $repository): RedirectResponse
    {
        $validated = $request->validate([
            'nueva_fecha' => 'required|date|after:today',
        ]);

        $repository->postpone($id, $validated['nueva_fecha']);

        return back()->with('success', 'Mantenimiento pospuesto exitosamente.');
    }

    public function cancel(int $id, Request $request, MaintenanceRepositoryInterface $repository): RedirectResponse
    {
        $validated = $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        $repository->cancel($id, $validated['motivo']);

        return back()->with('success', 'Mantenimiento cancelado.');
    }

    public function bulkDelete(Request $request, MaintenanceRepositoryInterface $repository): JsonResponse
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No se proporcionaron IDs válidos.']);
        }

        $deleted = $repository->deleteBulk($ids);

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} mantenimiento(s).",
            'deleted' => $deleted,
        ]);
    }

    public function upcoming(GetUpcomingMaintenanceUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute(30));
    }

    public function dashboard(
        GetMaintenanceKpisUseCase $kpisUseCase,
        GetUpcomingMaintenanceUseCase $upcomingUseCase
    ): Response {
        return Inertia::render('Maintenance/Dashboard', [
            'kpis' => (array) $kpisUseCase->execute(),
            'proximos' => $upcomingUseCase->execute(30),
        ]);
    }
}
