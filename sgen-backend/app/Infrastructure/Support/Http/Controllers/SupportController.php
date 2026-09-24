<?php

declare(strict_types=1);

namespace App\Infrastructure\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Support\Http\Requests\AddCommentRequest;
use App\Infrastructure\Support\Http\Requests\AddMaterialRequest;
use App\Infrastructure\Support\Http\Requests\RateTicketRequest;
use App\Infrastructure\Support\Http\Requests\ReassignTechnicianRequest;
use App\Infrastructure\Support\Http\Requests\StoreTicketRequest;
use App\Infrastructure\Support\Http\Requests\UpdateTicketRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Support\Application\DTOs\CreateTicketDTO;
use Modules\Support\Application\DTOs\UpdateTicketDTO;
use Modules\Support\Application\UseCases\AddTicketCommentUseCase;
use Modules\Support\Application\UseCases\AddTicketMaterialUseCase;
use Modules\Support\Application\UseCases\CreateTicketUseCase;
use Modules\Support\Application\UseCases\DeleteTicketUseCase;
use Modules\Support\Application\UseCases\GetCreateTicketDataUseCase;
use Modules\Support\Application\UseCases\GetTicketDetailUseCase;
use Modules\Support\Application\UseCases\ListTicketsUseCase;
use Modules\Support\Application\UseCases\RateTicketUseCase;
use Modules\Support\Application\UseCases\ReassignTechnicianUseCase;
use Modules\Support\Application\UseCases\UpdateTicketUseCase;

final class SupportController extends Controller
{
    public function index(Request $request, ListTicketsUseCase $useCase): Response
    {
        try {
            $filters = $request->only(['estado', 'search', 'prioridad']);
            $userId = $request->user()?->id;

            $data = $useCase->execute($filters, $userId);
            return Inertia::render('Support/Index', $data);
        } catch (\Exception $e) {
            return Inertia::render('Error', ['message' => $e->getMessage()]);
        }
    }

    public function create(GetCreateTicketDataUseCase $useCase): Response
    {
        try {
            $data = $useCase->execute();
            return Inertia::render('Support/Create', $data);
        } catch (\Exception $e) {
            return Inertia::render('Error', ['message' => $e->getMessage()]);
        }
    }

    private function parseTicketId(int|string $id): int
    {
        return (int) str_ireplace('T-', '', (string) $id);
    }

    public function show(int|string $id, GetTicketDetailUseCase $useCase): Response
    {
        try {
            $data = $useCase->execute($this->parseTicketId($id));
            abort_if($data === null, 404, 'Ticket no encontrado.');

            return Inertia::render('Support/Show', $data);
        } catch (\Exception $e) {
            return Inertia::render('Error', ['message' => $e->getMessage()]);
        }
    }

    public function store(StoreTicketRequest $request, CreateTicketUseCase $useCase): RedirectResponse
    {
        try {
            $dto = CreateTicketDTO::fromArray($request->validated());
            $ticketId = $useCase->execute($dto, $request->user()?->id);

            return redirect()->route('soportes.show', $ticketId)->with('success', 'Ticket creado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear el ticket: ' . $e->getMessage());
        }
    }

    public function update(int|string $id, UpdateTicketRequest $request, UpdateTicketUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id), UpdateTicketDTO::fromArray($request->validated()));

            return back()->with('success', 'Ticket actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el ticket: ' . $e->getMessage());
        }
    }

    public function destroy(int|string $id, DeleteTicketUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id));

            return redirect()->route('soportes.index')->with('success', 'Ticket eliminado satisfactoriamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el ticket: ' . $e->getMessage());
        }
    }

    public function reassign(int|string $id, ReassignTechnicianRequest $request, ReassignTechnicianUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id), (int) $request->validated('empleado_id'));

            return back()->with('success', 'Técnico reasignado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al reasignar técnico: ' . $e->getMessage());
        }
    }

    public function addComment(int|string $id, AddCommentRequest $request, AddTicketCommentUseCase $useCase): RedirectResponse
    {
        try {
            $userId = $request->user()?->id ?? 1;
            $useCase->execute($this->parseTicketId($id), $userId, (string) $request->validated('comentario'), (bool) $request->validated('es_interno', false));

            return back()->with('success', 'Comentario añadido.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al añadir comentario: ' . $e->getMessage());
        }
    }

    public function addMaterial(int|string $id, AddMaterialRequest $request, AddTicketMaterialUseCase $useCase): RedirectResponse
    {
        try {
            $userId = $request->user()?->id ?? 1;
            $useCase->execute($this->parseTicketId($id), (int) $request->validated('item_id'), (int) $request->validated('cantidad'), $userId);

            return back()->with('success', 'Material registrado en el ticket.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar material: ' . $e->getMessage());
        }
    }

    

    public function pause(int|string $id, PauseTicketUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id));

            return back()->with('success', 'Ticket pausado. El tiempo de atención se detendrá.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al pausar el ticket: ' . $e->getMessage());
        }
    }

    public function resume(int|string $id, ResumeTicketUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id));

            return back()->with('success', 'Ticket reanudado. El tiempo de atención ha continuado.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al reanudar el ticket: ' . $e->getMessage());
        }
    }

    public function updateCloseDate(int|string $id, Request $request, UpdateTicketUseCase $useCase): RedirectResponse
    {
        try {
            $validated = $request->validate(['fecha_cierre' => 'required|date']);
            $useCase->execute($this->parseTicketId($id), new UpdateTicketDTO(fechaCierre: $validated['fecha_cierre']));

            return back()->with('success', 'Fecha de cierre actualizada.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar la fecha de cierre: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request, BulkDeleteTicketsUseCase $useCase): RedirectResponse
    {
        $validated = $request->validate(['ids' => 'required|array']);
        $count = $useCase->execute($validated['ids']);

        return back()->with('success', "Se eliminaron {$count} tickets.");
    }

    public function uploadAttachment(int|string $id, Request $request, UploadTicketAttachmentUseCase $useCase): RedirectResponse
    {
        $validated = $request->validate(['file' => 'required|file|max:10240']);
        $file = $request->file('file');
        $path = $file->store('ticket_attachments');

        $attachmentId = $useCase->execute(
            $this->parseTicketId($id),
            $path,
            $file->getClientOriginalName(),
            $file->getMimeType(),
            $file->getSize(),
            $request->user()?->id ?? 1
        );

        return back()->with('success', 'Archivo adjunto subido correctamente.');
    }

    public function deleteAttachment(int $attachmentId, Request $request, SupportRepositoryInterface $repository): RedirectResponse
    {
        $repository->deleteAttachment($attachmentId);

        return back()->with('success', 'Archivo eliminado correctamente.');
    }

    public function rate(int|string $id, RateTicketRequest $request, RateTicketUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id), (string) $request->validated('rating'), $request->validated('comentario'));

            return back()->with('success', 'Valoración guardada. ¡Gracias por tu opinión!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al guardar valoración: ' . $e->getMessage());
        }
    }
}

    
