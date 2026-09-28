<?php

declare(strict_types=1);

namespace Modules\Settings\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Modules\Settings\Application\DTOs\UpdatePasswordDTO;
use Modules\Settings\Application\DTOs\UpdateSettingsDTO;
use Modules\Settings\Application\UseCases\GetSettingsUseCase;
use Modules\Settings\Application\UseCases\UpdateSettingsUseCase;
use Modules\Settings\Application\UseCases\UpdateUserPasswordUseCase;
use Modules\Settings\Infrastructure\Http\Requests\UpdateSettingsRequest;
use Modules\Settings\Infrastructure\Http\Requests\UpdateUserPasswordRequest;

final class SettingsController extends Controller
{
    public function index(Request $request, GetSettingsUseCase $useCase): Response
    {
        $userId = (int) $request->user()->id;

        return Inertia::render('Settings/Index', [
            'settings' => $useCase->execute($userId)->toArray(),
            // La superficie "Sistema" es invisible para cualquiera sin el permiso.
            'puede_administrar' => $request->user()?->can('configuracion.manage') ?? false,
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

        try {
            $useCase->execute($userId, UpdatePasswordDTO::fromArray($request->validated()));
        } catch (InvalidArgumentException $e) {
            // Contraseña actual incorrecta: es un error de validación del
            // formulario (422 con mensaje en `current_password`), no un fallo
            // del servidor (500).
            throw ValidationException::withMessages([
                'current_password' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Contraseña actualizada con éxito.');
    }
}
