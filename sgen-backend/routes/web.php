<?php

use App\Infrastructure\Auth\Http\Controllers\AuthController;
use App\Infrastructure\Dashboard\Http\Controllers\DashboardController;
use App\Infrastructure\Inventory\Http\Controllers\InventoryController;
use App\Infrastructure\Category\Http\Controllers\CategoryController;
use App\Infrastructure\User\Http\Controllers\UserController;
use App\Infrastructure\Audit\Http\Controllers\AuditController;
use App\Infrastructure\Settings\Http\Controllers\SettingsController;
use App\Infrastructure\About\Http\Controllers\AboutController;
use App\Infrastructure\Maintenance\Http\Controllers\MaintenanceController;
use App\Infrastructure\Reports\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas (Invitados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas Protegidas (Autenticación Requerida)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::post('/ajustar', [InventoryController::class, 'adjustStock'])->name('adjust');
        Route::post('/transferir', [InventoryController::class, 'transferStock'])->name('transfer');
        Route::get('/{id}', [InventoryController::class, 'show'])->name('show');
    });

    Route::prefix('equipos')->name('equipos.')->group(function () {
        Route::get('/', [\App\Infrastructure\Equipment\Http\Controllers\EquipmentController::class, 'index'])->name('index');
        Route::post('/', [\App\Infrastructure\Equipment\Http\Controllers\EquipmentController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Infrastructure\Equipment\Http\Controllers\EquipmentController::class, 'show'])->name('show');
        Route::match(['put', 'patch', 'post'], '/{id}', [\App\Infrastructure\Equipment\Http\Controllers\EquipmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Infrastructure\Equipment\Http\Controllers\EquipmentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('personal')->name('personal.')->group(function () {
        Route::get('/', [\App\Infrastructure\Employee\Http\Controllers\EmployeeController::class, 'index'])->name('index');
        Route::post('/', [\App\Infrastructure\Employee\Http\Controllers\EmployeeController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Infrastructure\Employee\Http\Controllers\EmployeeController::class, 'show'])->name('show');
        Route::match(['put', 'patch', 'post'], '/{id}', [\App\Infrastructure\Employee\Http\Controllers\EmployeeController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Infrastructure\Employee\Http\Controllers\EmployeeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('departamentos')->name('departamentos.')->group(function () {
        Route::get('/', [\App\Infrastructure\Department\Http\Controllers\DepartmentController::class, 'index'])->name('index');
        Route::post('/', [\App\Infrastructure\Department\Http\Controllers\DepartmentController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Infrastructure\Department\Http\Controllers\DepartmentController::class, 'show'])->name('show');
        Route::match(['put', 'patch', 'post'], '/{id}', [\App\Infrastructure\Department\Http\Controllers\DepartmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Infrastructure\Department\Http\Controllers\DepartmentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('soportes')->name('soportes.')->group(function () {
        Route::get('/', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'index'])->name('index');
        Route::get('/crear', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'create'])->name('create');
        Route::post('/', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'show'])->name('show');
        Route::get('/{id}/pdf', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'generatePdf'])->name('pdf');
        Route::put('/{id}', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'destroy'])->name('destroy');
        Route::match(['post', 'put'], '/{id}/reasignar', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'reassign'])->name('reassign');
        Route::match(['post', 'put'], '/{id}/asignar-tecnico', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'reassign'])->name('assign-tech');
        Route::post('/{id}/comentarios', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'addComment'])->name('comment');
        Route::post('/{id}/materiales', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'addMaterial'])->name('material');
        Route::post('/{id}/calificar', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'rate'])->name('rate');
        Route::post('/{id}/firma', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'saveSignature'])->name('signature');
        Route::post('/{id}/pausar', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'pause'])->name('pause');
        Route::post('/{id}/reanudar', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'resume'])->name('resume');
        Route::post('/{id}/actualizar-fecha-cierre', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'updateCloseDate'])->name('update-close-date');
        Route::post('/eliminar-masivos', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/{id}/archivos', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'uploadAttachment'])->name('upload-attachment');
        Route::delete('/archivos/{attachmentId}', [\App\Infrastructure\Support\Http\Controllers\SupportController::class, 'deleteAttachment'])->name('delete-attachment');
    });

    Route::prefix('categorias')->name('categorias.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('auditoria')->name('auditoria.')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/export', [AuditController::class, 'export'])->name('export');
        Route::get('/export-bitacora', [AuditController::class, 'exportBitacora'])->name('export-bitacora');
        Route::get('/{id}', [AuditController::class, 'show'])->name('show');
    });

    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::match(['put', 'patch', 'post'], '/', [SettingsController::class, 'update'])->name('update');
        Route::post('/password', [SettingsController::class, 'updatePassword'])->name('password');
    });

    Route::get('/acerca', [AboutController::class, 'index'])->name('acerca.index');

    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/tickets/pdf', [ReportsController::class, 'ticketsPdf'])->name('tickets.pdf');
        Route::get('/tickets/excel', [ReportsController::class, 'ticketsExcel'])->name('tickets.excel');
        Route::get('/inventario/pdf', [ReportsController::class, 'inventoryPdf'])->name('inventory.pdf');
        Route::get('/mantenimientos/pdf', [ReportsController::class, 'maintenancePdf'])->name('maintenance.pdf');
        Route::get('/rendimiento/pdf', [ReportsController::class, 'performancePdf'])->name('performance.pdf');
    });

    Route::prefix('mantenimientos')->name('mantenimientos.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::get('/dashboard', [MaintenanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/crear', [MaintenanceController::class, 'create'])->name('create');
        Route::post('/', [MaintenanceController::class, 'store'])->name('store');
        Route::get('/proximos', [MaintenanceController::class, 'upcoming'])->name('upcoming');
        Route::get('/{id}', [MaintenanceController::class, 'show'])->name('show');
        Route::match(['put', 'patch', 'post'], '/{id}', [MaintenanceController::class, 'update'])->name('update');
        Route::delete('/{id}', [MaintenanceController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/completar', [MaintenanceController::class, 'complete'])->name('complete');
        Route::post('/{id}/posponer', [MaintenanceController::class, 'postpone'])->name('postpone');
        Route::post('/{id}/cancelar', [MaintenanceController::class, 'cancel'])->name('cancel');
        Route::post('/eliminar-masivo', [MaintenanceController::class, 'bulkDelete'])->name('bulk-delete');
    });
});
