<?php

declare(strict_types=1);

namespace App\Infrastructure\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Support\Http\Requests\AddCommentRequest;
use App\Infrastructure\Support\Http\Requests\AddMaterialRequest;
use App\Infrastructure\Support\Http\Requests\RateTicketRequest;
use App\Infrastructure\Support\Http\Requests\ReassignTechnicianRequest;
use App\Infrastructure\Support\Http\Requests\ReopenTicketRequest;
use App\Infrastructure\Support\Http\Requests\StoreTicketRequest;
use App\Infrastructure\Support\Http\Requests\UpdateTicketRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Support\Application\DTOs\CreateTicketDTO;
use Modules\Support\Application\DTOs\UpdateTicketDTO;
use Modules\Support\Application\UseCases\AddTicketCommentUseCase;
use Modules\Support\Application\UseCases\AddTicketMaterialUseCase;
use Modules\Support\Application\UseCases\BulkDeleteTicketsUseCase;
use Modules\Support\Application\UseCases\CreateTicketUseCase;
use Modules\Support\Application\UseCases\DeleteTicketUseCase;
use Modules\Support\Application\UseCases\GenerateTicketPdfUseCase;
use Modules\Support\Application\UseCases\GetCreateTicketDataUseCase;
use Modules\Support\Application\UseCases\GetTicketDetailUseCase;
use Modules\Support\Application\UseCases\ListTicketsUseCase;
use Modules\Support\Application\UseCases\PauseTicketUseCase;
use Modules\Support\Application\UseCases\RateTicketUseCase;
use Modules\Support\Application\UseCases\ReassignTechnicianUseCase;
use Modules\Support\Application\UseCases\ReopenTicketUseCase;
use Modules\Support\Application\UseCases\ResumeTicketUseCase;
use Modules\Support\Application\UseCases\SaveTicketSignatureUseCase;
use Modules\Support\Application\UseCases\UpdateTicketUseCase;
use Modules\Support\Application\UseCases\UploadTicketAttachmentUseCase;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class SupportController extends Controller
{
    /**
     * Errores de negocio (DomainException) llevan mensajes deliberados y
     * seguros para el usuario final. Cualquier otro fallo se reporta al
     * log y se responde con un mensaje genérico: jamás se expone el
     * detalle interno (SQL, trazas, rutas) al cliente.
     */
    public function index(Request $request, ListTicketsUseCase $useCase): Response
    {
        try {
            $filters = $request->only(['estado', 'search', 'prioridad']);
            $userId = $request->user()?->id;

            $data = $useCase->execute($filters, $userId);

            return Inertia::render('Support/Index', $data);
        } catch (\Throwable $e) {
            report($e);

            return Inertia::render('Error', ['message' => 'No se pudo cargar la bandeja de tickets. Inténtelo de nuevo.']);
        }
    }

    public function create(GetCreateTicketDataUseCase $useCase): Response
    {
        try {
            $data = $useCase->execute();

            return Inertia::render('Support/Create', $data);
        } catch (\Throwable $e) {
            report($e);

            return Inertia::render('Error', ['message' => 'No se pudo cargar el formulario de creación. Inténtelo de nuevo.']);
        }
    }

    private function parseTicketId(int|string $id): int
    {
        return (int) str_ireplace('T-', '', (string) $id);
    }

    public function show(int|string $id, Request $request, GetTicketDetailUseCase $useCase): Response
    {
        try {
            $data = $useCase->execute($this->parseTicketId($id), $request->user()?->id);
            abort_if($data === null, 404, 'Ticket no encontrado.');

            return Inertia::render('Support/Show', $data);
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return Inertia::render('Error', ['message' => 'No se pudo cargar el detalle del ticket. Inténtelo de nuevo.']);
        }
    }

    public function store(StoreTicketRequest $request, CreateTicketUseCase $useCase): RedirectResponse
    {
        try {
            $dto = CreateTicketDTO::fromArray($request->validated());
            $ticketId = $useCase->execute($dto, $request->user()?->id);

            return redirect()->route('soportes.show', $ticketId)->with('success', 'Ticket creado correctamente.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al crear el ticket. Inténtelo de nuevo.');
        }
    }

    public function update(int|string $id, UpdateTicketRequest $request, UpdateTicketUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id), UpdateTicketDTO::fromArray($request->validated()));

            return back()->with('success', 'Ticket actualizado correctamente.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al actualizar el ticket. Inténtelo de nuevo.');
        }
    }

    public function destroy(int|string $id, DeleteTicketUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id));

            return redirect()->route('soportes.index')->with('success', 'Ticket eliminado satisfactoriamente.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al eliminar el ticket. Inténtelo de nuevo.');
        }
    }

    public function reassign(int|string $id, ReassignTechnicianRequest $request, ReassignTechnicianUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id), (int) $request->validated('empleado_id'));

            return back()->with('success', 'Técnico reasignado correctamente.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al reasignar técnico. Inténtelo de nuevo.');
        }
    }

    public function addComment(int|string $id, AddCommentRequest $request, AddTicketCommentUseCase $useCase): RedirectResponse
    {
        try {
            $userId = (int) $request->user()->id;
            $useCase->execute($this->parseTicketId($id), $userId, (string) $request->validated('comentario'), (bool) $request->validated('es_interno', false));

            return back()->with('success', 'Comentario añadido.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al añadir el comentario. Inténtelo de nuevo.');
        }
    }

    public function addMaterial(int|string $id, AddMaterialRequest $request, AddTicketMaterialUseCase $useCase): RedirectResponse
    {
        try {
            $userId = (int) $request->user()->id;
            $useCase->execute($this->parseTicketId($id), (int) $request->validated('item_id'), (int) $request->validated('cantidad'), $userId);

            return back()->with('success', 'Material registrado en el ticket.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al registrar el material. Inténtelo de nuevo.');
        }
    }

    public function pause(int|string $id, Request $request, PauseTicketUseCase $useCase): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'motivo' => ['required', 'string', 'max:500'],
            ]);

            $useCase->execute($this->parseTicketId($id), (string) $validated['motivo']);

            return back()->with('success', 'Ticket pausado. El tiempo de atención se detendrá.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'El motivo de pausa es obligatorio.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al pausar el ticket. Inténtelo de nuevo.');
        }
    }

    public function resume(int|string $id, ResumeTicketUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id));

            return back()->with('success', 'Ticket reanudado. El tiempo de atención ha continuado.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al reanudar el ticket. Inténtelo de nuevo.');
        }
    }

    public function reopen(int|string $id, ReopenTicketRequest $request, ReopenTicketUseCase $useCase): RedirectResponse
    {
        try {
            $ticketId = $this->parseTicketId($id);
            $userId = $request->user()?->id;
            $useCase->execute($ticketId, (string) $request->validated('motivo'), $userId);

            return back()->with('success', 'Ticket reabierto satisfactoriamente.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al reabrir el ticket. Inténtelo de nuevo.');
        }
    }

    public function updateCloseDate(int|string $id, Request $request, UpdateTicketUseCase $useCase): RedirectResponse
    {
        try {
            $validated = $request->validate(['fecha_cierre' => 'required|date']);
            $useCase->execute($this->parseTicketId($id), new UpdateTicketDTO(fechaCierre: $validated['fecha_cierre']));

            return back()->with('success', 'Fecha de cierre actualizada.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'La fecha de cierre no es válida.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al actualizar la fecha de cierre. Inténtelo de nuevo.');
        }
    }

    public function bulkDelete(Request $request, BulkDeleteTicketsUseCase $useCase): RedirectResponse
    {
        $validated = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $count = $useCase->execute($validated['ids']);

        return back()->with('success', "Se eliminaron {$count} tickets.");
    }

    public function uploadAttachment(int|string $id, Request $request, UploadTicketAttachmentUseCase $useCase): RedirectResponse
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:25600',
                'mimes:pdf,jpg,jpeg,png,webp,zip,doc,docx,xls,xlsx,txt',
            ],
        ], [
            'file.required' => 'Debe seleccionar un archivo.',
            'file.max' => 'El archivo no puede exceder los 25 MB.',
            'file.mimes' => 'Formato no permitido. Solo se aceptan PDF, JPG, PNG, WEBP, ZIP, DOC, DOCX, XLS, XLSX, TXT.',
        ]);

        $file = $request->file('file');
        $ticketId = $this->parseTicketId($id);

        $checksum = hash_file('sha256', $file->getRealPath());
        $path = $file->store('ticket_attachments');
        $userId = (int) $request->user()->id;

        $attachmentId = $useCase->execute(
            $ticketId,
            $path,
            $file->getClientOriginalName(),
            $file->getMimeType() ?: 'application/octet-stream',
            $file->getSize(),
            $userId,
            $checksum
        );

        try {
            DB::table('bitacora_acciones')->insert([
                'usuario_id' => $userId,
                'username' => $request->user()?->username ?? 'sistema',
                'accion' => 'subir_archivo',
                'entidad' => 'soporte',
                'entidad_id' => $ticketId,
                'enlace_tipo' => 'soporte',
                'enlace_id' => $ticketId,
                'datos_nuevos' => json_encode([
                    'archivo_id' => $attachmentId,
                    'nombre' => $file->getClientOriginalName(),
                    'tamano_bytes' => $file->getSize(),
                    'checksum_sha256' => $checksum,
                ]),
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'created_at' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            // La bitácora es evidencia forense: un fallo jamás pasa en silencio.
            report($e);
        }

        return back()->with('success', 'Archivo adjunto subido correctamente con validación de integridad SHA-256.');
    }

    public function downloadAttachment(int $attachmentId, Request $request, SupportRepositoryInterface $repository): StreamedResponse
    {
        $attachment = $repository->getAttachmentById($attachmentId);
        abort_if($attachment === null, 404, 'Archivo adjunto no encontrado.');

        // Alcance por fila: el operador solo descarga adjuntos de sus tickets.
        $user = $request->user();
        if ($user !== null && $user->rol === 'operador') {
            $ownerId = (int) DB::table('soportes')->where('id', $attachment->ticket_id)->value('usuario_creacion_id');
            abort_if($ownerId !== (int) $user->id, 404, 'Archivo adjunto no encontrado.');
        }

        if (! Storage::exists($attachment->ruta)) {
            abort(404, 'El archivo físico no se encuentra en el almacenamiento.');
        }

        return Storage::download(
            $attachment->ruta,
            $attachment->nombre_original,
            [
                'Content-Type' => $attachment->tipo_mime ?: 'application/octet-stream',
            ]
        );
    }

    public function deleteAttachment(int $attachmentId, Request $request, SupportRepositoryInterface $repository): RedirectResponse
    {
        $attachment = $repository->getAttachmentById($attachmentId);
        if ($attachment !== null) {
            if (Storage::exists($attachment->ruta)) {
                Storage::delete($attachment->ruta);
            }

            $repository->deleteAttachment($attachmentId);

            try {
                DB::table('bitacora_acciones')->insert([
                    'usuario_id' => $request->user()?->id,
                    'username' => $request->user()?->username ?? 'sistema',
                    'accion' => 'eliminar_archivo',
                    'entidad' => 'soporte',
                    'entidad_id' => $attachment->ticket_id,
                    'enlace_tipo' => 'soporte',
                    'enlace_id' => $attachment->ticket_id,
                    'datos_anteriores' => json_encode([
                        'archivo_id' => $attachmentId,
                        'nombre' => $attachment->nombre_original,
                    ]),
                    'ip_address' => $request->ip() ?: '127.0.0.1',
                    'created_at' => Carbon::now(),
                ]);
            } catch (\Throwable $e) {
                // La bitácora es evidencia forense: un fallo jamás pasa en silencio.
                report($e);
            }
        }

        return back()->with('success', 'Archivo eliminado correctamente.');
    }

    public function rate(int|string $id, RateTicketRequest $request, RateTicketUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute($this->parseTicketId($id), (string) $request->validated('rating'), $request->validated('comentario'));

            return back()->with('success', 'Valoración guardada. ¡Gracias por tu opinión!');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al guardar la valoración. Inténtelo de nuevo.');
        }
    }

    public function saveSignature(int|string $id, Request $request, SaveTicketSignatureUseCase $useCase): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'firma_base64' => ['required', 'string', 'max:1000000'],
            ]);

            $useCase->execute($this->parseTicketId($id), (string) $validated['firma_base64']);

            return back()->with('success', 'Firma registrada exitosamente.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'La firma no es válida.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Error al registrar la firma. Inténtelo de nuevo.');
        }
    }

    public function generatePdf(int|string $id, Request $request, GetTicketDetailUseCase $detailUseCase, GenerateTicketPdfUseCase $useCase): StreamedResponse|\Illuminate\Http\Response
    {
        $ticketId = $this->parseTicketId($id);
        abort_if($detailUseCase->execute($ticketId, $request->user()?->id) === null, 404, 'Ticket no encontrado.');

        return $useCase->execute($ticketId);
    }
}
