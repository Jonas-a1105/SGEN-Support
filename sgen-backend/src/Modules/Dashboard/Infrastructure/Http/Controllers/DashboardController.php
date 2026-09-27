<?php

declare(strict_types=1);

namespace Modules\Dashboard\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Dashboard\Application\UseCases\GetDashboardMetricsUseCase;

class DashboardController extends Controller
{
    public function index(Request $request, GetDashboardMetricsUseCase $useCase): Response
    {
        $year = (int) $request->input('year', (int) date('Y'));

        // Dashboard por rol: la vista personal además de la compilación global.
        return Inertia::render('Dashboard/Index', [
            'metrics' => $useCase->execute($year, $request->user()?->id)->toArray(),
        ]);
    }
}
