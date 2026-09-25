<?php

declare(strict_types=1);

namespace App\Infrastructure\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Settings\Http\Requests\UpdateSettingsRequest;
use App\Infrastructure\Settings\Http\Requests\UpdateUserPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Settings\Application\DTOs\UpdatePasswordDTO;
use Modules\Settings\Application\DTOs\UpdateSettingsDTO;
use Modules\Settings\Application\UseCases\GetSettingsUseCase;
use Modules\Settings\Application\UseCases\UpdateSettingsUseCase;
use Modules\Settings\Application\UseCases\UpdateUserPasswordUseCase;

final class SettingsController extends Controller
{
    public function index(Request $request, GetSettingsUseCase $useCase): Response
    {
        $userId = (int) ($request->user()?->id ?? 1);

        return Inertia::render('Settings/Index', [
            'settings' => $useCase->execute($userId)->toArray(),
        ]);
    }

    public function update(UpdateSettingsRequest $request, UpdateSettingsUseCase $useCase): RedirectResponse
    {
        $userId = (int) $request->user()->id;
        $useCase->execute($userId, UpdateSettingsDTO::fromArray($request->validated()));

        return back()->with('success', 'Preferencias actualizadas correctamente.');
    }

    public function updatePassword(UpdateUserPasswordRequest $request, UpdateUserPasswordUseCase $useCase): RedirectResponse
    {
        $userId = (int) $request->user()->id;
        $useCase->execute($userId, UpdatePasswordDTO::fromArray($request->validated()));

        return back()->with('success', 'Contraseña actualizada con éxito.');
    }
}
