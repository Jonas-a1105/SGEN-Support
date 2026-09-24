<?php
/**
 * Container Configuration - PHP-DI
 * Registra todas las dependencias con autowiring
 */

declare(strict_types=1);

return [
    // ===== CORE =====
    \Psr\Log\LoggerInterface::class => \DI\factory(function () {
        $logger = new \Monolog\Logger('sgen-support');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(
            __DIR__ . '/../logs/app.log',
            \Monolog\Level::Debug
        ));
        return $logger;
    }),

    \App\Core\Database::class => \DI\factory(fn() => \App\Core\Database::getInstance()),

    // ===== MODELS =====
    \App\Models\Soporte::class => \DI\autowire(),
    \App\Models\Equipo::class => \DI\autowire(),
    \App\Models\Departamento::class => \DI\autowire(),
    \App\Models\Usuario::class => \DI\autowire(),
    \App\Models\Inventario::class => \DI\autowire(),
    \App\Models\Empleado::class => \DI\autowire(),
    \App\Models\Categoria::class => \DI\autowire(),
    \App\Models\TicketArchivo::class => \DI\autowire(),
    \App\Models\TicketComentario::class => \DI\autowire(),
    \App\Models\Notificacion::class => \DI\autowire(),

    // ===== SERVICES =====
    \App\Services\TicketService::class => \DI\autowire(),
    \App\Services\InventarioService::class => \DI\autowire(),
    \App\Services\FileUploadService::class => \DI\autowire(),
    \App\Services\AuthorizationService::class => \DI\autowire(),
    \App\Services\NotificationService::class => \DI\autowire(),
    \App\Services\AuditService::class => \DI\autowire(),

    // ===== ACTION HANDLERS (Commands) =====
    \App\Actions\Ticket\CreateTicketAction::class => \DI\autowire(),
    \App\Actions\Ticket\UpdateTicketAction::class => \DI\autowire(),
    \App\Actions\Ticket\AssignTicketAction::class => \DI\autowire(),
    \App\Actions\Ticket\ResolveTicketAction::class => \DI\autowire(),
    \App\Actions\Ticket\HoldTicketAction::class => \DI\autowire(),
    \App\Actions\Ticket\ResumeTicketAction::class => \DI\autowire(),
    \App\Actions\Ticket\DeleteTicketAction::class => \DI\autowire(),
    \App\Actions\Ticket\AddConsumptionAction::class => \DI\autowire(),
    \App\Actions\Ticket\UpdateClosingDateAction::class => \DI\autowire(),
    \App\Actions\Ticket\UploadFileAction::class => \DI\autowire(),
    \App\Actions\Ticket\DownloadFileAction::class => \DI\autowire(),
    \App\Actions\Ticket\DeleteFileAction::class => \DI\autowire(),
    \App\Actions\Ticket\AddCommentAction::class => \DI\autowire(),
    \App\Actions\Ticket\EditCommentAction::class => \DI\autowire(),
    \App\Actions\Ticket\DeleteCommentAction::class => \DI\autowire(),
    \App\Actions\Ticket\BulkDeleteCommentsAction::class => \DI\autowire(),
    \App\Actions\Ticket\BulkDeleteTicketsAction::class => \DI\autowire(),
    \App\Actions\Ticket\SaveSignatureAction::class => \DI\autowire(),
    \App\Actions\Ticket\SaveRatingAction::class => \DI\autowire(),
    \App\Actions\Ticket\SaveObservationsAction::class => \DI\autowire(),
    \App\Actions\Ticket\GeneratePdfAction::class => \DI\autowire(),
    \App\Actions\Ticket\FindTechniciansAction::class => \DI\autowire(),
    \App\Actions\Ticket\FixEncodingAction::class => \DI\autowire(),

    // ===== QUERY HANDLERS (opcional, para CQRS) =====
    // \App\Queries\Ticket\ListTicketsQueryHandler::class => \DI\autowire(),
    // \App\Queries\Ticket\GetTicketDetailQueryHandler::class => \DI\autowire(),
    // \App\Queries\Ticket\GetCreateTicketFormQueryHandler::class => \DI\autowire(),
    // \App\Queries\Ticket\GetEditTicketFormQueryHandler::class => \DI\autowire(),
    // \App\Queries\Ticket\GetAssignFormQueryHandler::class => \DI\autowire(),

    // ===== CONTROLLERS =====
    \App\Controllers\SoportesController::class => \DI\autowire()
        ->constructorParameter('queryHandler', \DI\get(\App\Queries\Ticket\QueryHandlerAggregate::class)),

    // Otros controllers...
    \App\Controllers\AuthController::class => \DI\autowire(),
    \App\Controllers\HomeController::class => \DI\autowire(),
    \App\Controllers\EquiposController::class => \DI\autowire(),
    \App\Controllers\InventarioController::class => \DI\autowire(),
    \App\Controllers\MantenimientosController::class => \DI\autowire(),
    \App\Controllers\EmpleadosController::class => \DI\autowire(),
    \App\Controllers\DepartamentosController::class => \DI\autowire(),
    \App\Controllers\UsuariosController::class => \DI\autowire(),
    \App\Controllers\ReportesController::class => \DI\autowire(),
    \App\Controllers\ConfiguracionController::class => \DI\autowire(),
];