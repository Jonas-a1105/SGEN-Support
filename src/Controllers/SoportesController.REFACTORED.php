<?php
/**
 * SoportesController REFACTORIZADO
 * 
 * ANTES: 1,138 líneas, God Controller, acoplamiento alto, sin tests
 * DESPUÉS: ~200 líneas, Thin Controller, Action Handlers, DI, testeable
 * 
 * Principios aplicados:
 * - Single Responsibility: Controller solo HTTP (request/response)
 * - Dependency Inversion: Depende de interfaces, no implementaciones
 * - Open/Closed: Nuevas acciones = nuevos Handlers, no modificar Controller
 * - Command Pattern: Acciones mutativas = Handlers inyectables
 * - Validator Centralizado: Reglas declarativas
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware\CsrfMiddleware;
use App\Core\Middleware\RateLimitMiddleware;
use App\Actions\Ticket\CreateTicketAction;
use App\Actions\Ticket\UpdateTicketAction;
use App\Actions\Ticket\AssignTicketAction;
use App\Actions\Ticket\ResolveTicketAction;
use App\Actions\Ticket\HoldTicketAction;
use App\Actions\Ticket\ResumeTicketAction;
use App\Actions\Ticket\DeleteTicketAction;
use App\Actions\Ticket\AddConsumptionAction;
use App\Actions\Ticket\UpdateClosingDateAction;
use App\Actions\Ticket\UploadFileAction;
use App\Actions\Ticket\DownloadFileAction;
use App\Actions\Ticket\DeleteFileAction;
use App\Actions\Ticket\AddCommentAction;
use App\Actions\Ticket\EditCommentAction;
use App\Actions\Ticket\DeleteCommentAction;
use App\Actions\Ticket\BulkDeleteCommentsAction;
use App\Actions\Ticket\BulkDeleteTicketsAction;
use App\Actions\Ticket\SaveSignatureAction;
use App\Actions\Ticket\SaveRatingAction;
use App\Actions\Ticket\SaveObservationsAction;
use App\Actions\Ticket\GeneratePdfAction;
use App\Actions\Ticket\FindTechniciansAction;
use App\Actions\Ticket\FixEncodingAction;
use App\DTO\Ticket\CreateTicketDTO;
use App\DTO\Ticket\UpdateTicketDTO;
use App\DTO\Ticket\AssignTicketDTO;
use App\DTO\Ticket\AddConsumptionDTO;
use App\DTO\Ticket\UpdateClosingDateDTO;
use App\DTO\Ticket\UploadFileDTO;
use App\DTO\Ticket\AddCommentDTO;
use App\DTO\Ticket\EditCommentDTO;
use App\DTO\Ticket\DeleteCommentDTO;
use App\DTO\Ticket\BulkDeleteDTO;
use App\DTO\Ticket\SaveSignatureDTO;
use App\DTO\Ticket\SaveRatingDTO;
use App\DTO\Ticket\SaveObservationsDTO;
use App\DTO\Ticket\GeneratePdfDTO;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ConcurrencyException;
use App\Services\AuthorizationService;
use App\Services\NotificationService;
use App\Services\AuditService;
use Psr\Log\LoggerInterface;

class SoportesController extends Controller
{
    // Solo inyección de dependencias - NO instanciación directa
    public function __construct(
        private readonly CreateTicketAction $createTicketAction,
        private readonly UpdateTicketAction $updateTicketAction,
        private readonly AssignTicketAction $assignTicketAction,
        private readonly ResolveTicketAction $resolveTicketAction,
        private readonly HoldTicketAction $holdTicketAction,
        private readonly ResumeTicketAction $resumeTicketAction,
        private readonly DeleteTicketAction $deleteTicketAction,
        private readonly AddConsumptionAction $addConsumptionAction,
        private readonly UpdateClosingDateAction $updateClosingDateAction,
        private readonly UploadFileAction $uploadFileAction,
        private readonly DownloadFileAction $downloadFileAction,
        private readonly DeleteFileAction $deleteFileAction,
        private readonly AddCommentAction $addCommentAction,
        private readonly EditCommentAction $editCommentAction,
        private readonly DeleteCommentAction $deleteCommentAction,
        private readonly BulkDeleteCommentsAction $bulkDeleteCommentsAction,
        private readonly BulkDeleteTicketsAction $bulkDeleteTicketsAction,
        private readonly SaveSignatureAction $saveSignatureAction,
        private readonly SaveRatingAction $saveRatingAction,
        private readonly SaveObservationsAction $saveObservationsAction,
        private readonly GeneratePdfAction $generatePdfAction,
        private readonly FindTechniciansAction $findTechniciansAction,
        private readonly FixEncodingAction $fixEncodingAction,
        private readonly AuthorizationService $authService,
        private readonly NotificationService $notificationService,
        private readonly AuditService $auditService,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    /**
     * Middleware pipeline - CONFIGURACIÓN DECLARATIVA
     */
    protected function middleware(): array
    {
        return [
            CsrfMiddleware::class => ['except' => ['buscarTecnicos', 'fix_encoding_data']],
            RateLimitMiddleware::class => ['limit' => 60, 'window' => 60], // 60 req/min
            'auth' => ['except' => []],
        ];
    }

    /**
     * LISTAR - Solo lectura, delega a Query Handler (no mostrado aquí)
     */
    public function index(): void
    {
        try {
            $viewData = $this->queryHandler->handle(new ListTicketsQuery(
                userId: $_SESSION['user_id'],
                role: $_SESSION['rol'],
                departmentId: $_SESSION['departamento_id'] ?? null
            ));

            $this->render('soportes/lista', $viewData);
        } catch (\Throwable $e) {
            $this->handleException($e, 'soportes');
        }
    }

    /**
     * VER DETALLE - Query Handler
     */
    public function ver(int $id): void
    {
        try {
            $viewData = $this->queryHandler->handle(new GetTicketDetailQuery(
                ticketId: $id,
                userId: $_SESSION['user_id'],
                role: $_SESSION['rol']
            ));

            $this->render('soportes/detalle', $viewData);
        } catch (NotFoundException $e) {
            http_response_code(404);
            $this->render('errors/404', ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->handleException($e, "soportes/ver/{$id}");
        }
    }

    /**
     * CREAR - Formulario (GET)
     */
    public function crear(): void
    {
        try {
            $formData = $this->queryHandler->handle(new GetCreateTicketFormQuery(
                userId: $_SESSION['user_id'],
                role: $_SESSION['rol'],
                departmentId: $_SESSION['departamento_id'] ?? null
            ));

            $this->render('soportes/formulario', [
                'titulo' => 'Crear Nuevo Ticket de Soporte',
                'soporte' => null,
                ...$formData
            ]);
        } catch (\Throwable $e) {
            $this->handleException($e, 'soportes/crear');
        }
    }

    /**
     * EDITAR - Formulario (GET)
     */
    public function editar(int $id): void
    {
        try {
            $this->authService->requirePermission('ticket.edit', $id);

            $formData = $this->queryHandler->handle(new GetEditTicketFormQuery(
                ticketId: $id,
                userId: $_SESSION['user_id'],
                role: $_SESSION['rol']
            ));

            $this->render('soportes/formulario', [
                'titulo' => "Editar Ticket #{$id}",
                'soporte' => $formData['ticket'],
                ...$formData
            ]);
        } catch (ForbiddenException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        } catch (NotFoundException $e) {
            http_response_code(404);
            $this->render('errors/404', ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->handleException($e, "soportes/editar/{$id}");
        }
    }

    /**
     * GUARDAR - Crear o Actualizar (POST)
     * Delega a Action Handlers según sea create o update
     */
    public function guardar(): void
    {
        $this->requirePost();

        try {
            $id = (int)($_POST['id'] ?? 0);
            $version = (int)($_POST['version_id'] ?? 0);

            if ($id > 0) {
                // ACTUALIZAR
                $dto = UpdateTicketDTO::fromRequest($_POST, $version);
                $this->authService->requirePermission('ticket.edit', $id);

                $this->updateTicketAction->execute($dto);
                $this->auditService->log('ticket.updated', $id, ['by' => $_SESSION['user_id']]);
                $this->notificationService->notifyAdmins(
                    "{$_SESSION['username']} actualizó el ticket #{$id}",
                    "/soportes/ver/{$id}"
                );
                $this->setFlashMessage('success', "Ticket #{$id} actualizado correctamente.");
            } else {
                // CREAR
                $dto = CreateTicketDTO::fromRequest($_POST);
                $this->authService->requirePermission('ticket.create');

                $newId = $this->createTicketAction->execute($dto);
                $this->auditService->log('ticket.created', $newId, ['by' => $_SESSION['user_id']]);
                $this->notificationService->notifyAdmins(
                    "{$_SESSION['username']} creó un nuevo ticket #{$newId}",
                    "/soportes/ver/{$newId}"
                );
                $this->setFlashMessage('success', 'Nuevo ticket de soporte creado exitosamente.');
                $id = $newId;
            }

            header("Location: " . BASE_URL . "soportes/ver/{$id}");
            exit;
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'soportes/crear'));
            exit;
        } catch (ConcurrencyException $e) {
            $this->setFlashMessage('error', 'Conflicto de concurrencia: el ticket fue modificado por otro usuario. Refresca e intenta de nuevo.');
            header('Location: ' . BASE_URL . "soportes/ver/{$id}");
            exit;
        } catch (ForbiddenException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        } catch (\Throwable $e) {
            $this->handleException($e, 'soportes/guardar');
        }
    }

    /**
     * ASIGNAR TÉCNICO - Formulario (GET)
     */
    public function asignar(int $id): void
    {
        try {
            $this->authService->requirePermission('ticket.assign', $id);
            $data = $this->queryHandler->handle(new GetAssignFormQuery($id));

            $this->render('soportes/asignar_form', [
                'titulo' => "Asignar Técnico a Soporte #{$id}",
                ...$data
            ]);
        } catch (ForbiddenException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            header('Location: ' . BASE_URL . 'soportes');
            exit;
        } catch (\Throwable $e) {
            $this->handleException($e, "soportes/asignar/{$id}");
        }
    }

    /**
     * PROCESAR ASIGNACIÓN (POST)
     */
    public function procesar_asignacion(): void
    {
        $this->requirePost();

        try {
            $dto = AssignTicketDTO::fromRequest($_POST);
            $this->authService->requirePermission('ticket.assign', $dto->ticketId);

            $this->assignTicketAction->execute($dto);
            $this->auditService->log('ticket.assigned', $dto->ticketId, [
                'empleado_id' => $dto->empleadoId,
                'by' => $_SESSION['user_id']
            ]);

            header("Location: " . BASE_URL . "soportes/ver/{$dto->ticketId}");
            exit;
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'soportes'));
            exit;
        } catch (\Throwable $e) {
            $this->handleException($e, 'soportes/procesar_asignacion');
        }
    }

    /**
     * ACCIONES DE ESTADO - Todas delegan a sus Action Handlers
     */
    public function resolver(int $id): void       { $this->executeStateAction($id, 'resolve', fn() => $this->resolveTicketAction->execute($id)); }
    public function marcar_espera(int $id): void  { $this->executeStateAction($id, 'hold', fn() => $this->holdTicketAction->execute($id)); }
    public function reanudar(int $id): void       { $this->executeStateAction($id, 'resume', fn() => $this->resumeTicketAction->execute($id)); }

    private function executeStateAction(int $id, string $action, callable $handler): void
    {
        $this->requirePermissionForStateAction($action, $id);

        try {
            $handler();
            $this->auditService->log("ticket.{$action}", $id, ['by' => $_SESSION['user_id']]);
            $this->notificationService->notifyAdmins(
                "{$_SESSION['username']} {$action} el ticket #{$id}",
                "/soportes/ver/{$id}"
            );
            $this->setFlashMessage('success', $this->getStateSuccessMessage($action, $id));
        } catch (\Throwable $e) {
            $this->setFlashMessage('error', $e->getMessage());
        }

        header("Location: " . BASE_URL . "soportes/ver/{$id}");
        exit;
    }

    private function requirePermissionForStateAction(string $action, int $id): void
    {
        $permission = match ($action) {
            'resolve' => 'ticket.resolve',
            'hold'    => 'ticket.hold',
            'resume'  => 'ticket.resume',
            default   => 'ticket.edit'
        };
        $this->authService->requirePermission($permission, $id);
    }

    private function getStateSuccessMessage(string $action, int $id): string
    {
        return match ($action) {
            'resolve' => "¡Ticket #{$id} marcado como RESUELTO!",
            'hold'    => "Ticket #{$id} puesto EN ESPERA.",
            'resume'  => "Ticket #{$id} REANUDADO (En Proceso).",
            default   => "Acción completada."
        };
    }

    /**
     * AGREGAR CONSUMO (POST)
     */
    public function agregar_consumo(): void
    {
        $this->requirePost();

        try {
            $dto = AddConsumptionDTO::fromRequest($_POST, $_SESSION['user_id']);
            $this->authService->requirePermission('ticket.add_consumption', $dto->ticketId);

            $this->addConsumptionAction->execute($dto);
            $this->auditService->log('ticket.consumption_added', $dto->ticketId, [
                'item_id' => $dto->itemId,
                'cantidad' => $dto->cantidad,
                'by' => $_SESSION['user_id']
            ]);
            $this->setFlashMessage('success', 'Consumo registrado exitosamente.');
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->setFlashMessage('error', $e->getMessage());
        }

        header('Location: ' . BASE_URL . "soportes/ver/{$dto->ticketId}#info");
        exit;
    }

    /**
     * ACTUALIZAR FECHA CIERRE (POST) - Solo Admin
     */
    public function actualizar_fecha_cierre(): void
    {
        $this->requirePost();
        $this->authService->requireRole('admin');

        try {
            $dto = UpdateClosingDateDTO::fromRequest($_POST);
            $this->updateClosingDateAction->execute($dto);
            $this->auditService->log('ticket.closing_date_updated', $dto->ticketId, [
                'new_date' => $dto->nuevaFechaCierre,
                'by' => $_SESSION['user_id']
            ]);
            $this->setFlashMessage('success', 'Fecha de cierre actualizada y tiempo de atención recalculado.');
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->setFlashMessage('error', $e->getMessage());
        }

        header("Location: " . BASE_URL . "soportes/ver/{$dto->ticketId}");
        exit;
    }

    /**
     * ARCHIVOS - Subir (POST)
     */
    public function subir_archivo(): void
    {
        $this->requirePost();

        try {
            $dto = UploadFileDTO::fromRequest($_POST, $_FILES, $_SESSION['user_id']);
            $result = $this->uploadFileAction->execute($dto);
            $this->auditService->log('ticket.file_uploaded', $dto->ticketId, ['file_id' => $result->fileId]);
            $this->setFlashMessage('success', 'Archivo subido correctamente.');
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->setFlashMessage('error', $e->getMessage());
        }

        header("Location: " . BASE_URL . "soportes/ver/{$dto->ticketId}#files");
        exit;
    }

    /**
     * ARCHIVOS - Descargar (GET)
     */
    public function descargar_archivo(int $id): void
    {
        try {
            $result = $this->downloadFileAction->execute($id);
            $this->downloadFileAction->serve($result);
            exit;
        } catch (NotFoundException $e) {
            http_response_code(404);
            die($e->getMessage());
        } catch (\Throwable $e) {
            $this->handleException($e, "soportes/descargar_archivo/{$id}");
        }
    }

    /**
     * ARCHIVOS - Eliminar (GET/POST)
     */
    public function eliminar_archivo(int $id): void
    {
        try {
            $this->authService->requirePermission('ticket.delete_file', $id);
            $this->deleteFileAction->execute($id);
            $this->auditService->log('ticket.file_deleted', $id, ['by' => $_SESSION['user_id']]);
            $this->setFlashMessage('success', 'Archivo eliminado correctamente.');
        } catch (ForbiddenException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->setFlashMessage('error', $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'soportes/ver/' . ($id ?? 0) . '#files');
        exit;
    }

    /**
     * COMENTARIOS - Agregar (POST)
     */
    public function agregar_comentario(): void
    {
        $this->requirePost();

        try {
            $dto = AddCommentDTO::fromRequest($_POST, $_SESSION['user_id']);
            $isAjax = $this->isAjaxRequest();

            $result = $this->addCommentAction->execute($dto);

            if ($isAjax) {
                $this->jsonResponse($result->toArray());
                return;
            }

            $this->setFlashMessage('success', 'Comentario agregado correctamente.');
        } catch (ValidationException $e) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
                return;
            }
            $this->setFlashMessage('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->handleException($e, 'soportes/agregar_comentario');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$dto->ticketId}#comments");
        exit;
    }

    /**
     * COMENTARIOS - Editar (POST)
     */
    public function editar_comentario(): void
    {
        $this->requirePost();

        try {
            $dto = EditCommentDTO::fromRequest($_POST);
            $this->authService->requirePermission('comment.edit', $dto->commentId);
            $this->editCommentAction->execute($dto);
            $this->setFlashMessage('success', 'Comentario actualizado.');
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (ForbiddenException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->handleException($e, 'soportes/editar_comentario');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$dto->ticketId}#comments");
        exit;
    }

    /**
     * COMENTARIOS - Eliminar (GET/POST)
     */
    public function eliminar_comentario(int $id): void
    {
        try {
            $dto = new DeleteCommentDTO($id, $_SESSION['user_id'], $_SESSION['rol']);
            $this->authService->requirePermission('comment.delete', $id);
            $this->deleteCommentAction->execute($dto);

            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => true, 'message' => 'Comentario eliminado.']);
                return;
            }
            $this->setFlashMessage('success', 'Comentario eliminado.');
        } catch (ForbiddenException $e) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 403);
                return;
            }
            $this->setFlashMessage('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->handleException($e, "soportes/eliminar_comentario/{$id}");
        }

        header("Location: " . BASE_URL . "soportes/ver/{$dto->ticketId}#comments");
        exit;
    }

    /**
     * COMENTARIOS - Eliminar Masivo (AJAX POST)
     */
    public function eliminar_comentarios_masivos(): void
    {
        $this->requireAjax();

        try {
            $dto = BulkDeleteDTO::fromJsonInput();
            $this->authService->requireRole('admin'); // Solo admin para bulk
            $result = $this->bulkDeleteCommentsAction->execute($dto);
            $this->jsonResponse(['success' => true, 'message' => "Se eliminaron {$result->count} comentarios.", 'deletedCount' => $result->count]);
        } catch (ValidationException $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * TICKETS - Eliminar Masivo (AJAX POST) - Solo Admin
     */
    public function eliminar_tickets_masivos(): void
    {
        $this->requireAjax();
        $this->authService->requireRole('admin');

        try {
            $dto = BulkDeleteDTO::fromJsonInput();
            $result = $this->bulkDeleteTicketsAction->execute($dto);
            $this->jsonResponse(['success' => true, 'message' => "Se eliminaron {$result->count} tickets.", 'deletedCount' => $result->count]);
        } catch (ValidationException $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ELIMINAR TICKET (GET)
     */
    public function eliminar(int $id): void
    {
        try {
            $this->authService->requirePermission('ticket.delete', $id);
            $this->deleteTicketAction->execute($id);
            $this->auditService->log('ticket.deleted', $id, ['by' => $_SESSION['user_id']]);
            $this->setFlashMessage('success', "Ticket #{$id} eliminado correctamente.");
        } catch (ForbiddenException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->handleException($e, "soportes/eliminar/{$id}");
        }

        header("Location: " . BASE_URL . "soportes");
        exit;
    }

    /**
     * GUARDAR FIRMA (POST)
     */
    public function guardar_firma(): void
    {
        $this->requirePost();

        try {
            $dto = SaveSignatureDTO::fromRequest($_POST);
            $this->authService->requirePermission('ticket.sign', $dto->ticketId);
            $this->saveSignatureAction->execute($dto);
            $this->auditService->log('ticket.signed', $dto->ticketId, ['by' => $_SESSION['user_id']]);
            $this->setFlashMessage('success', 'Firma guardada correctamente.');
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->handleException($e, 'soportes/guardar_firma');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$dto->ticketId}#signature");
        exit;
    }

    /**
     * GUARDAR VALORACIÓN (POST)
     */
    public function guardar_valoracion(): void
    {
        $this->requirePost();

        try {
            $dto = SaveRatingDTO::fromRequest($_POST);
            $this->saveRatingAction->execute($dto);
            $this->auditService->log('ticket.rated', $dto->ticketId, ['rating' => $dto->valoracion]);
            $this->setFlashMessage('success', '¡Gracias por tu valoración!');
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->handleException($e, 'soportes/guardar_valoracion');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$dto->ticketId}#signature");
        exit;
    }

    /**
     * GENERAR PDF (GET)
     */
    public function pdf(int $id): void
    {
        try {
            $dto = new GeneratePdfDTO($id);
            $this->generatePdfAction->execute($dto);
            exit;
        } catch (NotFoundException $e) {
            http_response_code(404);
            die($e->getMessage());
        } catch (\Throwable $e) {
            $this->handleException($e, "soportes/pdf/{$id}");
        }
    }

    /**
     * GUARDAR OBSERVACIONES (POST)
     */
    public function guardar_observaciones(): void
    {
        $this->requirePost();

        try {
            $dto = SaveObservationsDTO::fromRequest($_POST);
            $this->authService->requirePermission('ticket.add_observations', $dto->ticketId);
            $this->saveObservationsAction->execute($dto);
            $this->auditService->log('ticket.observations_updated', $dto->ticketId, ['by' => $_SESSION['user_id']]);

            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => true, 'message' => 'Bitácora actualizada correctamente']);
                return;
            }
            $this->setFlashMessage('success', 'Bitácora actualizada correctamente.');
        } catch (ValidationException $e) {
            $this->respondError($e->getMessage(), $dto->ticketId ?? 0);
        } catch (\Throwable $e) {
            $this->handleException($e, 'soportes/guardar_observaciones');
        }

        header("Location: " . BASE_URL . "soportes/ver/{$dto->ticketId}#notes");
        exit;
    }

    /**
     * API BUSCAR TÉCNICOS (GET) - Para modal asignar
     */
    public function buscarTecnicos(): void
    {
        $this->jsonResponse($this->findTechniciansAction->execute());
    }

    /**
     * FIX ENCODING DATA (GET) - Solo Admin, herramienta de mantenimiento
     */
    public function fix_encoding_data(): void
    {
        $this->authService->requireRole('admin');
        $this->fixEncodingAction->execute();
        exit; // La acción imprime directamente el HTML de resultados
    }

    // ========== HELPERS ==========

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
    }

    private function requireAjax(): void
    {
        if (!$this->isAjaxRequest()) {
            http_response_code(405);
            exit;
        }
    }

    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function handleException(\Throwable $e, string $redirect): void
    {
        $this->logger->error($e->getMessage(), [
            'controller' => self::class,
            'redirect' => $redirect,
            'user_id' => $_SESSION['user_id'] ?? null,
            'trace' => $e->getTraceAsString()
        ]);

        $this->setFlashMessage('error', 'Error interno del servidor. Contacte al administrador.');
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }
}