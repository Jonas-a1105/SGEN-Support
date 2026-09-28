<?php

declare(strict_types=1);

namespace Modules\Equipment\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Equipment\Application\DTOs\CreateEquipmentDTO;
use Modules\Equipment\Application\DTOs\UpdateEquipmentDTO;
use Modules\Equipment\Application\UseCases\CreateEquipmentUseCase;
use Modules\Equipment\Application\UseCases\DecommissionEquipmentUseCase;
use Modules\Equipment\Application\UseCases\DeleteEquipmentUseCase;
use Modules\Equipment\Application\UseCases\ExportEquipmentExcelUseCase;
use Modules\Equipment\Application\UseCases\GenerateCustodyActPdfUseCase;
use Modules\Equipment\Application\UseCases\GetEquipmentDashboardDataUseCase;
use Modules\Equipment\Application\UseCases\GetEquipmentDetailUseCase;
use Modules\Equipment\Application\UseCases\TransferEquipmentUseCase;
use Modules\Equipment\Application\UseCases\UpdateEquipmentUseCase;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;
use Modules\Equipment\Infrastructure\Http\Requests\DecommissionEquipmentRequest;
use Modules\Equipment\Infrastructure\Http\Requests\StoreEquipmentRequest;
use Modules\Equipment\Infrastructure\Http\Requests\TransferEquipmentRequest;
use Modules\Equipment\Infrastructure\Http\Requests\UpdateEquipmentRequest;
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
        } catch (\DomainException $e) {
            // Error de negocio con mensaje deliberado y seguro para el usuario.
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al procesar el equipo. Inténtelo de nuevo.');
        }
    }

    public function update(int $id, UpdateEquipmentRequest $request, UpdateEquipmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id, UpdateEquipmentDTO::fromArray($request->validated()));

        return back()->with('success', 'Equipo actualizado correctamente.');
    }

    /**
     * Firma probatoria de la custodia vigente (jornada patrimonial).
     */
    public function signCustody(int $id, Request $request, EquipmentRepositoryInterface $repository): RedirectResponse
    {
        // Mismo contrato de trazo que los tickets: data URL de imagen real.
        $validated = $request->validate([
            'firma_base64' => ['required', 'string', 'starts_with:data:image/', 'min:100', 'max:1000000'],
        ]);

        try {
            $repository->signCustody(
                $id,
                (string) $validated['firma_base64'],
                (string) ($request->ip() ?? '0.0.0.0'),
                $request->userAgent()
            );

            return back()->with('success', 'Custodia firmada con evidencia probatoria.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Baja patrimonial formal (Módulo 11): retiro con motivo legal, custodia
     * y mantenimiento cerrados, y acta emitida con hash SHA-256.
     * Acción irreversible; la historia completa permanece.
     */
    public function decommission(int $id, DecommissionEquipmentRequest $request, DecommissionEquipmentUseCase $useCase): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $useCase->execute(
                $id,
                (string) $validated['motivo'],
                isset($validated['valor_recuperacion']) ? (float) $validated['valor_recuperacion'] : null,
                $validated['destino'] ?? null,
                $validated['nota'] ?? null,
                (int) $request->user()->id,
            );

            return back()->with('success', 'Baja patrimonial registrada: el activo queda fuera del ciclo operativo con acta generada.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Acta formal de baja, probatoria (hash SHA-256 al pie + verificación).
     */
    public function decommissionAct(int $id, GetEquipmentDetailUseCase $detailUseCase): \Illuminate\Http\Response
    {
        $detail = $detailUseCase->execute($id);

        abort_unless($detail && $detail->rawStatus === 'de_baja', 403, 'El acta de baja solo existe para activos oficialmente retirados.');

        // La huella probatoria se compone de los datos del activo + la evidencia
        // de baja: cualquier recómputo posterior debe igualarse byte a byte.
        $dataActa = $detail->toArray();
        $stringCore = json_encode([
            'equipo' => [
                'id' => $dataActa['id'],
                'codigo' => $dataActa['inventoryCode'],
                'marca' => $dataActa['brand'],
                'modelo' => $dataActa['model'],
            ],
            'baja' => [
                'motivo' => $dataActa['motivo_baja'] ?? null,
                'fecha' => $dataActa['fecha_baja'] ?? null,
                'valor' => $dataActa['valor_recuperacion'] ?? null,
                'destino' => $dataActa['destino_baja'] ?? null,
            ],
        ]);
        $hash = hash('sha256', (string) $stringCore);

        // Inmutabilidad probatoria: el hash de emisión se graba una sola vez.
        DB::table('equipos')->where('id', $id)->whereNull('acta_baja_hash')->update(['acta_baja_hash' => $hash]);

        return Pdf::loadView('equipment.decommission-act-pdf', [
            'equipment' => $dataActa,
            'generada_en' => now(),
            'hash_acta' => $hash,
        ])
            ->setPaper('a4', 'portrait')
            ->download("Acta_Baja_{$detail->inventoryCode}.pdf");
    }

    public function destroy(int $id, DeleteEquipmentUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($id);
        } catch (\DomainException $e) {
            // Integridad patrimonial: el motivo llega al usuario, sin 500 crudo.
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('equipos.index')->with('success', 'Equipo eliminado satisfactoriamente.');
    }

    public function transfer(TransferEquipmentRequest $request, TransferEquipmentUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($request->toDTO());

            return back()->with('success', 'Equipo trasladado correctamente.');
        } catch (\DomainException $e) {
            // Error de negocio con mensaje deliberado y seguro para el usuario.
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al procesar el equipo. Inténtelo de nuevo.');
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
