<?php

use App\Infrastructure\Inventory\Providers\InventoryModuleServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    InventoryModuleServiceProvider::class,
    \App\Infrastructure\Dashboard\Providers\DashboardModuleServiceProvider::class,
    \App\Infrastructure\Support\Providers\SupportModuleServiceProvider::class,
    \App\Infrastructure\Equipment\Providers\EquipmentModuleServiceProvider::class,
    \App\Infrastructure\Employee\Providers\EmployeeModuleServiceProvider::class,
    \App\Infrastructure\Department\Providers\DepartmentModuleServiceProvider::class,
    \Modules\Category\CategoryModuleServiceProvider::class,
    \Modules\User\UserModuleServiceProvider::class,
    \Modules\Audit\AuditModuleServiceProvider::class,
    \App\Infrastructure\Audit\Providers\AuditModuleServiceProvider::class,
    \Modules\Settings\SettingsModuleServiceProvider::class,
    \Modules\About\AboutModuleServiceProvider::class,
    \App\Infrastructure\Maintenance\Providers\MaintenanceModuleServiceProvider::class,
    \App\Infrastructure\Reports\Providers\ReportsModuleServiceProvider::class,
];
