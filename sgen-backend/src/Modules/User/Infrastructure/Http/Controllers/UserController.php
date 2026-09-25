<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\User\Infrastructure\Http\Requests\StoreUserRequest;
use Modules\User\Infrastructure\Http\Requests\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\User\Application\DTOs\CreateUserDTO;
use Modules\User\Application\DTOs\UpdateUserDTO;
use Modules\User\Application\UseCases\CreateUserUseCase;
use Modules\User\Application\UseCases\DeleteUserUseCase;
use Modules\User\Application\UseCases\GetUserDirectoryUseCase;
use Modules\User\Application\UseCases\UpdateUserUseCase;

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
        $useCase->execute($id, UpdateUserDTO::fromArray($request->validated()));

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(int $id, DeleteUserUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id);

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado satisfactoriamente.');
    }
}
