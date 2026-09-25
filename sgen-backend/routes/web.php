<?php

use App\Infrastructure\About\Http\Controllers\AboutController;
use App\Infrastructure\Audit\Http\Controllers\AuditController;
use App\Infrastructure\Auth\Http\Controllers\AuthController;
use App\Infrastructure\Category\Http\Controllers\CategoryController;
use App\Infrastructure\Dashboard\Http\Controllers\DashboardController;
use App\Infrastructure\Department\Http\Controllers\DepartmentController;
use App\Infrastructure\Employee\Http\Controllers\EmployeeController;
use App\Infrastructure\Equipment\Http\Controllers\EquipmentController;
use App\Infrastructure\Inventory\Http\Controllers\InventoryController;
use App\Infrastructure\Maintenance\Http\Controllers\MaintenanceController;
use App\Infrastructure\Notification\Http\Controllers\NotificationController;
use App\Infrastructure\Reports\Http\Controllers\ReportsController;
use App\Infrastructure\Settings\Http\Controllers\SettingsController;
use App\Infrastructure\Support\Http\Controllers\SupportController;
use App\Infrastructure\User\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas (Invitados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas Protegidas (Autenticación Requerida)
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
            Route::post('/registrar', [EquipmentController::class, 'register'])->name('register');
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
        Route::post('/', [SupportController::class, 'store'])->name('store');
        Route::get('/{id}', [SupportController::class, 'show'])->name('show');
        Route::get('/{id}/pdf', [SupportController::class, 'generatePdf'])->name('pdf');
        Route::post('/{id}/comentarios', [SupportController::class, 'addComment'])->name('comment');
        Route::post('/{id}/calificar', [SupportController::class, 'rate'])->name('rate');
        Route::post('/{id}/firma', [SupportController::class, 'saveSignature'])->name('signature');
        Route::get('/archivos/{attachmentId}/descargar', [SupportController::class, 'downloadAttachment'])->name('download-attachment');

        // Ciclo de vida operativo: admin y técnico.
        Route::middleware('permission:soportes.manage')->group(function () {
            Route::put('/{id}', [SupportController::class, 'update'])->name('update');
            Route::match(['post', 'put'], '/{id}/reasignar', [SupportController::class, 'reassign'])->name('reassign');
            Route::match(['post', 'put'], '/{id}/asignar-tecnico', [SupportController::class, 'reassign'])->name('assign-tech');
            Route::post('/{id}/materiales', [SupportController::class, 'addMaterial'])->name('material');
            Route::post('/{id}/pausar', [SupportController::class, 'pause'])->name('pause');
            Route::post('/{id}/reanudar', [SupportController::class, 'resume'])->name('resume');
            Route::post('/{id}/reabrir', [SupportController::class, 'reopen'])->name('reopen');
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
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('auditoria')->name('auditoria.')->middleware('permission:auditoria.view')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/export', [AuditController::class, 'export'])->name('export');
        Route::get('/export-bitacora', [AuditController::class, 'exportBitacora'])->name('export-bitacora');
        Route::get('/{id}', [AuditController::class, 'show'])->name('show');
    });

    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        // Cambio de contraseña propia: cualquier usuario autenticado.
        Route::post('/password', [SettingsController::class, 'updatePassword'])->name('password');

        Route::middleware('permission:configuracion.manage')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::match(['put', 'patch', 'post'], '/', [SettingsController::class, 'update'])->name('update');
        });
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
            Route::post('/{id}/materiales', [MaintenanceController::class, 'addMaterial'])->name('add-material');
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
