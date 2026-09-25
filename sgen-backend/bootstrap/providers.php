<?php

use App\Providers\AppServiceProvider;
use Modules\About\Infrastructure\Providers\AboutModuleServiceProvider;
use Modules\Audit\Infrastructure\Providers\AuditModuleServiceProvider;
use Modules\Category\Infrastructure\Providers\CategoryModuleServiceProvider;
use Modules\Dashboard\Infrastructure\Providers\DashboardModuleServiceProvider;
use Modules\Department\Infrastructure\Providers\DepartmentModuleServiceProvider;
use Modules\Employee\Infrastructure\Providers\EmployeeModuleServiceProvider;
use Modules\Equipment\Infrastructure\Providers\EquipmentModuleServiceProvider;
use Modules\Inventory\Infrastructure\Providers\InventoryModuleServiceProvider;
use Modules\Maintenance\Infrastructure\Providers\MaintenanceModuleServiceProvider;
use Modules\Notification\Infrastructure\Providers\NotificationModuleServiceProvider;
use Modules\Reports\Infrastructure\Providers\ReportsModuleServiceProvider;
use Modules\Settings\Infrastructure\Providers\SettingsModuleServiceProvider;
use Modules\Support\Infrastructure\Providers\SupportModuleServiceProvider;
use Modules\User\Infrastructure\Providers\UserModuleServiceProvider;

return [
    AppServiceProvider::class,
    InventoryModuleServiceProvider::class,
    DashboardModuleServiceProvider::class,
    SupportModuleServiceProvider::class,
    EquipmentModuleServiceProvider::class,
    EmployeeModuleServiceProvider::class,
    DepartmentModuleServiceProvider::class,
    CategoryModuleServiceProvider::class,
    UserModuleServiceProvider::class,
    AuditModuleServiceProvider::class,
    SettingsModuleServiceProvider::class,
    AboutModuleServiceProvider::class,
    MaintenanceModuleServiceProvider::class,
    ReportsModuleServiceProvider::class,
    NotificationModuleServiceProvider::class,
];
