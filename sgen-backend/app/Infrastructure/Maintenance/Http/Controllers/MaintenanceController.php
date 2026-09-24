<?php

declare(strict_types=1);

namespace App\Infrastructure\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Maintenance\Http\Requests\CancelMaintenanceRequest;
use App\Infrastructure\Maintenance\Http\Requests\CompleteMaintenanceRequest;
use App\Infrastructure\Maintenance\Http\Requests\PostponeMaintenanceRequest;
use App\Infrastructure\Maintenance\Http\Requests\StoreMaintenanceRequest;
use App\Infrastructure\Maintenance\Http\Requests\UpdateMaintenanceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
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

    public function show(
        int $id,
        Request $request,
        GetMaintenanceDetailUseCase $useCase,
        MaintenanceRepositoryInterface $repository
    ): Response|JsonResponse {
        $maintenance = $useCase->execute($id);
        abort_if($maintenance === null, 404, 'Mantenimiento no encontrado.');

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json($maintenance);
        }

        return Inertia::render('Maintenance/Show', [
            'maintenance' => (array) $maintenance,
            'options' => $repository->getFormOptions(),
        ]);
    }

    public function create(MaintenanceRepositoryInterface $repository): Response
    {
        return Inertia::render('Maintenance/Create', $repository->getFormOptions());
    }

    public function store(StoreMaintenanceRequest $request, CreateMaintenanceUseCase $useCase): RedirectResponse
    {
        $useCase->execute($request->toDTO(), $request->user()?->id);

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento registrado exitosamente.');
    }

    public function update(int $id, UpdateMaintenanceRequest $request, UpdateMaintenanceUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id, $request->toDTO());

        return back()->with('success', 'Mantenimiento actualizado correctamente.');
    }

    public function destroy(int $id, DeleteMaintenanceUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id);

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento eliminado satisfactoriamente.');
    }

    public function complete(int $id, CompleteMaintenanceRequest $request, CompleteMaintenanceUseCase $useCase): RedirectResponse
    {
        $useCase->execute(
            $id,
            $request->input('observaciones'),
            $request->filled('costo') ? (float) $request->input('costo') : null
        );

        return back()->with('success', 'Mantenimiento marcado como completado.');
    }

    public function postpone(int $id, PostponeMaintenanceRequest $request, MaintenanceRepositoryInterface $repository): RedirectResponse
    {
        $repository->postpone($id, (string) $request->input('nueva_fecha'));

        return back()->with('success', 'Mantenimiento pospuesto exitosamente.');
    }

    public function cancel(int $id, CancelMaintenanceRequest $request, MaintenanceRepositoryInterface $repository): RedirectResponse
    {
        $repository->cancel($id, (string) $request->input('motivo'));

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
