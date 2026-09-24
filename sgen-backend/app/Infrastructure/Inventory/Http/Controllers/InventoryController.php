<?php

declare(strict_types=1);

namespace App\Infrastructure\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Inventory\Http\Requests\AdjustStockRequest;
use App\Infrastructure\Inventory\Http\Requests\ReassignEquipmentRequest;
use App\Infrastructure\Inventory\Http\Requests\StoreEquipmentRequest;
use App\Infrastructure\Inventory\Http\Requests\StoreProductRequest;
use App\Infrastructure\Inventory\Http\Requests\TransferStockRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Application\DTOs\CreateProductDTO;
use Modules\Inventory\Application\DTOs\StockAdjustmentDTO;
use Modules\Inventory\Application\Mappers\ProductDetailMapper;
use Modules\Inventory\Application\UseCases\AdjustStockUseCase;
use Modules\Inventory\Application\UseCases\CreateProductUseCase;
use Modules\Inventory\Application\UseCases\GetInventoryDashboardUseCase;
use Modules\Inventory\Application\UseCases\ReassignEquipmentUseCase;
use Modules\Inventory\Application\UseCases\RegisterEquipmentUseCase;
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
        $useCase->execute(StockAdjustmentDTO::fromArray($request->validated()));

        return back()->with('success', 'Ajuste de existencias procesado con éxito.');
    }

    public function storeEquipment(StoreEquipmentRequest $request, RegisterEquipmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute($request->toDTO());

        return back()->with('success', 'Equipo registrado exitosamente en el inventario.');
    }

    public function transferStock(TransferStockRequest $request, TransferStockUseCase $useCase): RedirectResponse
    {
        $useCase->execute($request->toDTO(), (int) ($request->user()?->id ?? 1));

        return back()->with('success', 'Transferencia de stock procesada con éxito.');
    }

    public function reassignEquipment(ReassignEquipmentRequest $request, ReassignEquipmentUseCase $useCase): RedirectResponse
    {
        $useCase->execute($request->toDTO());

        return back()->with('success', 'Ubicación y asignación del equipo actualizadas con éxito.');
    }

    public function show(int|string $id, ProductRepositoryInterface $repository): Response
    {
        $product = $repository->findById((int) $id);
        abort_if($product === null, 404, 'Producto no encontrado');

        return Inertia::render('Inventory/Show', ['product' => ProductDetailMapper::toArray($product)]);
    }
}
