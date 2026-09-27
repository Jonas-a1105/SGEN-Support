<?php

use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsureSessionIdle;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Cierre por inactividad antes de las reglas de cuenta; cabeceras
        // de seguridad al final para firmar también los redirects.
        $middleware->web(append: [
            HandleInertiaRequests::class,
            EnsureSessionIdle::class,
            SecurityHeaders::class,
            EnsurePasswordChanged::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            // #21: replay de respuestas ante doble-submit (acciones críticas).
            'idempotency' => \App\Http\Middleware\IdempotencyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Verificación de SLA vencidos + notificaciones (cada 15 min)
        $schedule->command('sla:verify')->everyFifteenMinutes();

        // Job de seguridad: materializa órdenes de mantenimiento recurrentes
        // faltantes cuando faltan 7 días o menos para la próxima fecha.
        $schedule->command('mantenimientos:materializar')->dailyAt('06:00');
    })
    ->create();
