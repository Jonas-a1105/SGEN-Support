<?php

declare(strict_types=1);

namespace App\Infrastructure\About\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\About\Application\UseCases\GetSystemAboutUseCase;

final class AboutController extends Controller
{
    public function index(GetSystemAboutUseCase $useCase): Response
    {
        return Inertia::render('About/Index', [
            'about' => $useCase->execute()->toArray(),
        ]);
    }
}
