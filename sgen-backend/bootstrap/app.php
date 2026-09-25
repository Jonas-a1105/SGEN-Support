<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
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
