<?php

use App\Http\Controllers\ConfiguracionGlobalController;
use App\Http\Controllers\PapeleraController;
use App\Http\Controllers\PublicTicketPortalController;
use App\Http\Controllers\RolesAdminController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\About\Infrastructure\Http\Controllers\AboutController;
use Modules\Audit\Infrastructure\Http\Controllers\AuditController;
use Modules\Auth\Infrastructure\Http\Controllers\AuthController;
use Modules\Auth\Infrastructure\Http\Controllers\PasswordResetController;
use Modules\Category\Infrastructure\Http\Controllers\CategoryController;
use Modules\Dashboard\Infrastructure\Http\Controllers\DashboardController;
use Modules\Department\Infrastructure\Http\Controllers\DepartmentController;
use Modules\Employee\Infrastructure\Http\Controllers\EmployeeController;
use Modules\Equipment\Infrastructure\Http\Controllers\EquipmentController;
use Modules\Inventory\Infrastructure\Http\Controllers\InventoryController;
use Modules\Maintenance\Infrastructure\Http\Controllers\MaintenanceController;
use Modules\Notification\Infrastructure\Http\Controllers\NotificationController;
use Modules\Reports\Infrastructure\Http\Controllers\ReportsController;
use Modules\Settings\Infrastructure\Http\Controllers\SettingsController;
use Modules\Support\Infrastructure\Http\Controllers\SupportController;
use Modules\User\Infrastructure\Http\Controllers\UserController;

// Rutas Públicas (Invitados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Recuperación self-service de contraseña (token firmado, 60 min, 1 uso).
    Route::get('/forgot-password', [PasswordResetController::class, 'showRequest'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Rutas Protegidas (Autenticación Requerida)// Rutas Protegidas (Autenticación Requerida)
//
// Autorización por permisos (Spatie): las rutas GET operativas quedan
// abiertas a todo usuario autenticado; las mutaciones exigen el permiso
// "<módulo>.manage" según la matriz de App\Support\Rbac\PermissionCatalog.
// Las acciones destructivas de tickets son exclusivas del administrador.
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cuenta: cambio de contraseña obligatorio (cuentas con clave temporal).
    Route::get('/cuenta/contrasena', fn () => Inertia::render('Auth/ForcePasswordChange'))->name('cuenta.contrasena');

    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/{id}', [InventoryController::class, 'show'])->name('show');

        Route::middleware('permission:inventario.manage')->group(function () {
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::post('/ajustar', [InventoryController::class, 'adjustStock'])->name('adjust');
            Route::post('/transferir', [InventoryController::class, 'transferStock'])->name('transfer');
            Route::match(['put', 'patch', 'post'], '/{id}', [InventoryController::class, 'update'])->name('update');
        });
    });

    Route::prefix('equipos')->name('equipos.')->group(function () {
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::get('/export/excel', [EquipmentController::class, 'exportExcel'])->name('export.excel');
        Route::get('/{id}', [EquipmentController::class, 'show'])->name('show');
        Route::get('/{id}/acta-pdf', [EquipmentController::class, 'generateCustodyPdf'])->name('acta.pdf');

        Route::middleware('permission:equipos.manage')->group(function () {
            Route::post('/', [EquipmentController::class, 'store'])->name('store');
            Route::post('/trasladar', [EquipmentController::class, 'transfer'])->name('transfer');
            Route::post('/reasignar', [EquipmentController::class, 'reassign'])->name('reassign');
            Route::post('/{id}/custodia/firmar', [EquipmentController::class, 'signCustody'])->name('custodia.firmar')->middleware('idempotency');
            // Baja patrimonial irreversible (motivo legal, acta probatoria).
            Route::post('/{id}/baja', [EquipmentController::class, 'decommission'])->name('baja');
            Route::get('/{id}/acta-baja', [EquipmentController::class, 'decommissionAct'])->name('acta-baja');
            Route::match(['put', 'patch', 'post'], '/{id}', [EquipmentController::class, 'update'])->name('update');
            Route::delete('/{id}', [EquipmentController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('personal')->name('personal.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/{id}', [EmployeeController::class, 'show'])->name('show');

        Route::middleware('permission:personal.manage')->group(function () {
            Route::post('/', [EmployeeController::class, 'store'])->name('store');
            Route::match(['put', 'patch', 'post'], '/{id}', [EmployeeController::class, 'update'])->name('update');
            Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('departamentos')->name('departamentos.')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/{id}', [DepartmentController::class, 'show'])->name('show');

        Route::middleware('permission:departamentos.manage')->group(function () {
            Route::post('/', [DepartmentController::class, 'store'])->name('store');
            Route::match(['put', 'patch', 'post'], '/{id}', [DepartmentController::class, 'update'])->name('update');
            Route::delete('/{id}', [DepartmentController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/empleados', [DepartmentController::class, 'assignEmployee'])->name('assign-employee');
            Route::delete('/{id}/empleados/{employeeId}', [DepartmentController::class, 'removeEmployee'])->name('remove-employee');
            Route::post('/{id}/equipos', [DepartmentController::class, 'assignEquipment'])->name('assign-equipment');
            Route::delete('/{id}/equipos/{equipmentId}', [DepartmentController::class, 'removeEquipment'])->name('remove-equipment');
        });
    });

    Route::prefix('soportes')->name('soportes.')->group(function () {
        // Lectura y acciones del solicitante: cualquier usuario autenticado.
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::get('/crear', [SupportController::class, 'create'])->name('create');
        // #21: crear ticket es la acción destructiva creativa por excelencia.
        Route::post('/', [SupportController::class, 'store'])->name('store')->middleware('idempotency');
        Route::get('/{id}', [SupportController::class, 'show'])->name('show');
        Route::get('/{id}/pdf', [SupportController::class, 'generatePdf'])->name('pdf');
        Route::post('/{id}/comentarios', [SupportController::class, 'addComment'])->name('comment');
        Route::post('/{id}/calificar', [SupportController::class, 'rate'])->name('rate')->middleware('idempotency');
        Route::post('/{id}/firma', [SupportController::class, 'saveSignature'])->name('signature')->middleware('idempotency');
        // Reapertura: el solicitante también la puede pedir sobre su ticket;
        // actor legítimo y ventana de días los valida el dominio (regla #31).
        Route::post('/{id}/reabrir', [SupportController::class, 'reopen'])->name('reopen');
        Route::get('/archivos/{attachmentId}/descargar', [SupportController::class, 'downloadAttachment'])->name('download-attachment');

        // Ciclo de vida operativo: admin y técnico.
        Route::middleware('permission:soportes.manage')->group(function () {
            Route::put('/{id}', [SupportController::class, 'update'])->name('update');
            Route::match(['post', 'put'], '/{id}/reasignar', [SupportController::class, 'reassign'])->name('reassign');
            Route::match(['post', 'put'], '/{id}/asignar-tecnico', [SupportController::class, 'reassign'])->name('assign-tech');
            Route::post('/{id}/materiales', [SupportController::class, 'addMaterial'])->name('material')->middleware('idempotency');
            Route::post('/{id}/pausar', [SupportController::class, 'pause'])->name('pause');
            Route::post('/{id}/reanudar', [SupportController::class, 'resume'])->name('resume');
            Route::post('/{id}/actualizar-fecha-cierre', [SupportController::class, 'updateCloseDate'])->name('update-close-date');
            Route::post('/{id}/archivos', [SupportController::class, 'uploadAttachment'])->name('upload-attachment');
            Route::delete('/archivos/{attachmentId}', [SupportController::class, 'deleteAttachment'])->name('delete-attachment');
        });

        // Borrado de registros de soporte: solo administrador (trazabilidad ITIL).
        Route::middleware('permission:soportes.delete')->group(function () {
            Route::delete('/{id}', [SupportController::class, 'destroy'])->name('destroy');
            Route::post('/eliminar-masivos', [SupportController::class, 'bulkDelete'])->name('bulk-delete');
        });
    });

    Route::prefix('categorias')->name('categorias.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');

        Route::middleware('permission:categorias.manage')->group(function () {
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::match(['put', 'patch'], '/{id}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('usuarios')->name('usuarios.')->middleware('permission:usuarios.manage')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '/{id}', [UserController::class, 'update'])->name('update');
        Route::post('/{id}/restablecer', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{id}/alternar-estado', [UserController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('auditoria')->name('auditoria.')->middleware('permission:auditoria.view')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/export', [AuditController::class, 'export'])->name('export');
        Route::get('/export-bitacora', [AuditController::class, 'exportBitacora'])->name('export-bitacora');
        Route::get('/{id}', [AuditController::class, 'show'])->name('show');
    });

    // Papelera universal: restaurar o purgar, solo administración.
    Route::middleware('permission:auditoria.view')->group(function () {
        Route::get('/papelera', [PapeleraController::class, 'index'])->name('papelera.index');
        Route::post('/papelera/{entidad}/{id}/restaurar', [PapeleraController::class, 'restore'])->name('papelera.restore');
        Route::delete('/papelera/{entidad}/{id}', [PapeleraController::class, 'destroyForever'])->name('papelera.purge');
    });

    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        // Página de configuración personal: candado grande vacío de roles —
        // ésta ya no es de admin; cualquier cuenta la utiliza.
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::match(['put', 'patch', 'post'], '/', [SettingsController::class, 'update'])->name('update');

        // Cambio de contraseña propia: todo autenticado.
        Route::post('/password', [SettingsController::class, 'updatePassword'])->name('password');

        // Administración de plataforma, solo con entrada de sistema abierta.
        Route::middleware('permission:configuracion.manage')->group(function () {
            Route::get('/sistema', [ConfiguracionGlobalController::class, 'index'])->name('sistema.index');
            Route::match(['put', 'patch', 'post'], '/sistema', [ConfiguracionGlobalController::class, 'update'])->name('sistema.update');
        });
    });

    // Editor de roles con matriz de permisos (administración funcional).
    Route::middleware('permission:usuarios.manage')->group(function () {
        Route::get('/roles', [RolesAdminController::class, 'index'])->name('roles.index');
        Route::match(['put', 'patch', 'post'], '/roles/{role}', [RolesAdminController::class, 'update'])->name('roles.update');
    });

    Route::get('/acerca', [AboutController::class, 'index'])->name('acerca.index');

    Route::prefix('reportes')->name('reportes.')->middleware('permission:reportes.view')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/tickets/pdf', [ReportsController::class, 'ticketsPdf'])->name('tickets.pdf');
        Route::get('/tickets/excel', [ReportsController::class, 'ticketsExcel'])->name('tickets.excel');
        Route::get('/inventario/pdf', [ReportsController::class, 'inventoryPdf'])->name('inventory.pdf');
        Route::get('/inventario/excel', [ReportsController::class, 'inventoryExcel'])->name('inventory.excel');
        Route::get('/equipos/excel', [EquipmentController::class, 'exportExcel'])->name('equipos.excel');
        Route::get('/mantenimientos/pdf', [ReportsController::class, 'maintenancePdf'])->name('maintenance.pdf');
        Route::get('/mantenimientos/excel', [ReportsController::class, 'maintenanceExcel'])->name('maintenance.excel');
        Route::get('/rendimiento/pdf', [ReportsController::class, 'performancePdf'])->name('performance.pdf');
    });

    Route::prefix('mantenimientos')->name('mantenimientos.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::get('/dashboard', [MaintenanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/crear', [MaintenanceController::class, 'create'])->name('create');
        Route::get('/proximos', [MaintenanceController::class, 'upcoming'])->name('upcoming');
        Route::get('/{id}', [MaintenanceController::class, 'show'])->name('show');
        Route::get('/{id}/pdf', [MaintenanceController::class, 'generateWorkOrderPdf'])->name('pdf');
        Route::get('/{id}/materiales', [MaintenanceController::class, 'materiales'])->name('materiales');

        Route::middleware('permission:mantenimientos.manage')->group(function () {
            Route::post('/', [MaintenanceController::class, 'store'])->name('store');
            Route::match(['put', 'patch', 'post'], '/{id}', [MaintenanceController::class, 'update'])->name('update');
            Route::post('/{id}/materiales', [MaintenanceController::class, 'addMaterial'])->name('add-material')->middleware('idempotency');
            Route::delete('/{id}', [MaintenanceController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/completar', [MaintenanceController::class, 'complete'])->name('complete');
            Route::post('/{id}/posponer', [MaintenanceController::class, 'postpone'])->name('postpone');
            Route::post('/{id}/cancelar', [MaintenanceController::class, 'cancel'])->name('cancel');
            Route::post('/eliminar-masivo', [MaintenanceController::class, 'bulkDelete'])->name('bulk-delete');
        });
    });

    // Notificaciones: alcance por usuario autenticado en el controlador.
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::get('/count', [NotificationController::class, 'countUnread'])->name('count');
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'delete'])->name('destroy');
    });
});

// Módulo 03 / PASO 0 multicanal: seguimiento público del ticket con token.
Route::get('/portal/ticket/{token}', [PublicTicketPortalController::class, 'show'])->name('portal.ticket.status');
