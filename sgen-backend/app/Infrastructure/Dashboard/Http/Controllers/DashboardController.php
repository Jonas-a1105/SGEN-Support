<?php

declare(strict_types=1);

namespace App\Infrastructure\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Dashboard\Application\UseCases\GetDashboardMetricsUseCase;

class DashboardController extends Controller
{
    public function index(Request $request, GetDashboardMetricsUseCase $useCase): Response
    {
        $year = (int) $request->input('year', 2025);

        return Inertia::render('Dashboard/Index', [
            'metrics' => $useCase->execute($year)->toArray(),
        ]);
    }
}
