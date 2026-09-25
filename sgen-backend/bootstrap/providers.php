<?php

use App\Infrastructure\Dashboard\Providers\DashboardModuleServiceProvider;
use App\Infrastructure\Department\Providers\DepartmentModuleServiceProvider;
use App\Infrastructure\Employee\Providers\EmployeeModuleServiceProvider;
use App\Infrastructure\Equipment\Providers\EquipmentModuleServiceProvider;
use App\Infrastructure\Inventory\Providers\InventoryModuleServiceProvider;
use App\Infrastructure\Maintenance\Providers\MaintenanceModuleServiceProvider;
use App\Infrastructure\Notification\Providers\NotificationModuleServiceProvider;
use App\Infrastructure\Reports\Providers\ReportsModuleServiceProvider;
use App\Infrastructure\Support\Providers\SupportModuleServiceProvider;
use App\Providers\AppServiceProvider;
use Modules\About\AboutModuleServiceProvider;
use Modules\Audit\AuditModuleServiceProvider;
use Modules\Category\CategoryModuleServiceProvider;
use Modules\Settings\SettingsModuleServiceProvider;
use Modules\User\UserModuleServiceProvider;

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
