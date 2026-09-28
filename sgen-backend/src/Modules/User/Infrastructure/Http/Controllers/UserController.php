<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\User\Application\DTOs\CreateUserDTO;
use Modules\User\Application\DTOs\UpdateUserDTO;
use Modules\User\Application\UseCases\CreateUserUseCase;
use Modules\User\Application\UseCases\DeleteUserUseCase;
use Modules\User\Application\UseCases\GetUserDirectoryUseCase;
use Modules\User\Application\UseCases\ResetUserPasswordUseCase;
use Modules\User\Application\UseCases\ToggleUserActiveUseCase;
use Modules\User\Application\UseCases\UpdateUserUseCase;
use Modules\User\Infrastructure\Http\Requests\StoreUserRequest;
use Modules\User\Infrastructure\Http\Requests\UpdateUserRequest;

final class UserController extends Controller
{
    public function index(Request $request, GetUserDirectoryUseCase $useCase): Response
    {
        $filters = $request->only(['search', 'rol']);

        return Inertia::render('User/Index', $useCase->execute($filters));
    }

    public function store(StoreUserRequest $request, CreateUserUseCase $useCase): RedirectResponse
    {
        $useCase->execute(CreateUserDTO::fromArray($request->validated()));

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function update(int $id, UpdateUserRequest $request, UpdateUserUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute(
                $id,
                UpdateUserDTO::fromArray($request->validated()),
                (int) $request->user()->id
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Alterna el acceso de la cuenta sin destruir su trazabilidad.
     * Las protecciones (último admin, tickets activos) son del caso de uso.
     */
    public function toggleActive(int $id, ToggleUserActiveUseCase $useCase, Request $request): RedirectResponse
    {
        try {
            $activo = $useCase->execute($id, (int) $request->user()->id);

            return back()->with(
                'success',
                $activo ? 'Usuario reactivado; ya puede ingresar.' : 'Usuario desactivado; su historial se conserva.'
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id, DeleteUserUseCase $useCase, Request $request): RedirectResponse
    {
        try {
            $useCase->execute($id, (int) $request->user()->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado satisfactoriamente.');
    }

    /**
     * Restablecimiento operado por administración: la contraseña temporal
     * viaja una sola vez por flash de sesión y el usuario deberá cambiarla
     * en su próximo ingreso.
     */
    public function resetPassword(int $id, ResetUserPasswordUseCase $useCase): RedirectResponse
    {
        $temporary = $useCase->execute($id);

        return back()
            ->with('success', 'Contraseña restablecida. El usuario deberá cambiarla al ingresar.')
            ->with('temp_password', ['user_id' => $id, 'password' => $temporary]);
    }
}
