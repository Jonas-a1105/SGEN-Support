<?php

declare(strict_types=1);

namespace Modules\Category\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Category\Infrastructure\Http\Requests\StoreCategoryRequest;
use Modules\Category\Infrastructure\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Category\Application\DTOs\CreateCategoryDTO;
use Modules\Category\Application\DTOs\UpdateCategoryDTO;
use Modules\Category\Application\UseCases\CreateCategoryUseCase;
use Modules\Category\Application\UseCases\DeleteCategoryUseCase;
use Modules\Category\Application\UseCases\GetCategoryListUseCase;
use Modules\Category\Application\UseCases\UpdateCategoryUseCase;

final class CategoryController extends Controller
{
    public function index(Request $request, GetCategoryListUseCase $useCase): Response
    {
        $filters = $request->only(['search']);

        return Inertia::render('Category/Index', $useCase->execute($filters));
    }

    public function store(StoreCategoryRequest $request, CreateCategoryUseCase $useCase): RedirectResponse
    {
        $useCase->execute(CreateCategoryDTO::fromArray($request->validated()));

        return redirect()->route('categorias.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function update(int $id, UpdateCategoryRequest $request, UpdateCategoryUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id, UpdateCategoryDTO::fromArray($request->validated()));

        return back()->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(int $id, DeleteCategoryUseCase $useCase): RedirectResponse
    {
        $useCase->execute($id);

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada satisfactoriamente.');
    }
}
