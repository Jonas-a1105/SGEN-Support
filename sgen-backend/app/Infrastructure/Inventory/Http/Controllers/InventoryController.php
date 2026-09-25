<?php

declare(strict_types=1);

namespace App\Infrastructure\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Inventory\Http\Requests\AdjustStockRequest;
use App\Infrastructure\Inventory\Http\Requests\StoreProductRequest;
use App\Infrastructure\Inventory\Http\Requests\TransferStockRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Application\DTOs\CreateProductDTO;
use Modules\Inventory\Application\DTOs\StockAdjustmentDTO;
use Modules\Inventory\Application\UseCases\AdjustStockUseCase;
use Modules\Inventory\Application\UseCases\CreateProductUseCase;
use Modules\Inventory\Application\UseCases\GetInventoryDashboardUseCase;
use Modules\Inventory\Application\UseCases\GetProductDetailUseCase;
use Modules\Inventory\Application\UseCases\TransferStockUseCase;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;

class InventoryController extends Controller
{
    public function index(Request $request, GetInventoryDashboardUseCase $useCase): Response
    {
        $filters = $request->only(['search', 'categoria', 'low_stock']);
        $page = (int) $request->input('page', 1);

        return Inertia::render('Inventory/Index', $useCase->execute($filters, $page));
    }

    public function store(StoreProductRequest $request, CreateProductUseCase $useCase): RedirectResponse
    {
        $useCase->execute(CreateProductDTO::fromArray($request->validated()));

        return redirect()->route('inventario.index')->with('success', 'Producto creado exitosamente.');
    }

    public function adjustStock(AdjustStockRequest $request, AdjustStockUseCase $useCase): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()?->id;
        $useCase->execute(StockAdjustmentDTO::fromArray($data));

        return back()->with('success', 'Ajuste de existencias procesado con éxito.');
    }

    public function transferStock(TransferStockRequest $request, TransferStockUseCase $useCase): RedirectResponse
    {
        $userId = (int) ($request->user()?->id ?? DB::table('usuarios')->orderBy('id')->value('id') ?? 1);
        $useCase->execute($request->toDTO(), $userId);

        return back()->with('success', 'Transferencia de stock procesada con éxito.');
    }

    public function show(int|string $id, GetProductDetailUseCase $useCase): Response
    {
        $data = $useCase->execute((int) $id);
        abort_if($data === null, 404, 'Producto no encontrado');

        return Inertia::render('Inventory/Show', $data);
    }

    public function update(int|string $id, Request $request, ProductRepositoryInterface $repository): RedirectResponse
    {
        $product = $repository->findById((int) $id);
        abort_if($product === null, 404, 'Producto no encontrado');

        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'unit_of_measure' => ['nullable', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        DB::table('inventario_items')->where('id', (int) $id)->update([
            'codigo' => $validated['sku'] ?? $product->sku()->value(),
            'nombre' => $validated['name'],
            'categoria' => $validated['category'],
            'unidad_medida' => $validated['unit_of_measure'] ?? $product->unitOfMeasure(),
            'marca' => $validated['brand'] ?? $product->brand(),
            'modelo' => $validated['model'] ?? $product->model(),
            'stock_minimo' => isset($validated['minimum_stock']) ? (int) $validated['minimum_stock'] : $product->minimumStock()->value(),
            'valor_compra' => isset($validated['purchase_price']) ? (float) $validated['purchase_price'] : $product->purchasePrice()->amount(),
            'ubicacion' => $validated['location'] ?? $product->location(),
            'descripcion' => $validated['description'] ?? $product->description(),
            'updated_at' => now(),
        ]);

        return redirect()->route('inventario.index')->with('success', 'Producto actualizado exitosamente.');
    }
}
