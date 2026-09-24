<?php

declare(strict_types=1);

namespace App\Infrastructure\Inventory\Persistence\Repositories;

use App\Infrastructure\Inventory\Persistence\Eloquent\Models\EloquentProductModel;
use App\Infrastructure\Inventory\Persistence\Eloquent\Models\EloquentStockMovementModel;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Domain\Enums\ProductStatus;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;
use Modules\Inventory\Domain\ValueObjects\Money;
use Modules\Inventory\Domain\ValueObjects\Quantity;
use Modules\Inventory\Domain\ValueObjects\Sku;

final class PostgresProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        $model = EloquentProductModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findBySku(string $sku): ?Product
    {
        $model = EloquentProductModel::where('codigo', strtoupper(trim($sku)))->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function paginate(array $filters = [], int $perPage = 15, int $page = 1): array
    {
        $query = EloquentProductModel::query();

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'ilike', $term)
                    ->orWhere('codigo', 'ilike', $term)
                    ->orWhere('categoria', 'ilike', $term);
            });
        }

        if (! empty($filters['categoria'])) {
            $query->where('categoria', $filters['categoria']);
        }

        if (! empty($filters['low_stock']) && $filters['low_stock'] === true) {
            $query->whereColumn('stock_actual', '<=', 'stock_minimo');
        }

        $paginator = $query->orderBy('id', 'desc')->paginate(perPage: $perPage, page: $page);

        return [
            'items' => array_map(fn ($m) => $this->toDomain($m), $paginator->items()),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function save(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $attributes = [
                'codigo' => $product->sku()->value(),
                'nombre' => $product->name(),
                'categoria' => $product->category(),
                'descripcion' => $product->description(),
                'marca' => $product->brand(),
                'modelo' => $product->model(),
                'unidad_medida' => $product->unitOfMeasure(),
                'stock_actual' => $product->currentStock()->value(),
                'stock_minimo' => $product->minimumStock()->value(),
                'ubicacion' => $product->location(),
                'valor_compra' => $product->purchasePrice()->amount(),
            ];

            if ($product->id() !== null) {
                $model = EloquentProductModel::findOrFail($product->id());
                $model->update($attributes);
            } else {
                $model = EloquentProductModel::create($attributes);
            }

            foreach ($product->pullRecordedMovements() as $movement) {
                EloquentStockMovementModel::create([
                    'item_id' => $model->id,
                    'usuario_id' => $movement->userId() > 0 ? $movement->userId() : 1,
                    'tipo_movimiento' => $movement->type()->value,
                    'cantidad' => $movement->quantity()->value(),
                    'motivo' => $movement->reason(),
                    'fecha' => now(),
                ]);
            }

            return $this->toDomain($model);
        });
    }

    public function getKpis(): array
    {
        $stats = DB::table('inventario_items')
            ->selectRaw('
                COUNT(*) as total_items,
                COALESCE(SUM(stock_actual), 0) as total_units,
                COUNT(CASE WHEN stock_actual <= stock_minimo THEN 1 END) as low_stock_count,
                COALESCE(SUM(stock_actual * valor_compra), 0) as total_valuation
            ')
            ->first();

        return [
            'total_items' => (int) ($stats->total_items ?? 0),
            'total_units' => (int) ($stats->total_units ?? 0),
            'low_stock_count' => (int) ($stats->low_stock_count ?? 0),
            'total_valuation' => (float) ($stats->total_valuation ?? 0.0),
        ];
    }

    private function toDomain(EloquentProductModel $model): Product
    {
        return new Product(
            id: $model->id,
            sku: Sku::fromString($model->codigo),
            name: $model->nombre,
            category: $model->categoria,
            description: $model->descripcion,
            brand: $model->marca,
            model: $model->modelo,
            unitOfMeasure: $model->unidad_medida,
            currentStock: Quantity::fromInteger($model->stock_actual),
            minimumStock: Quantity::fromInteger($model->stock_minimo),
            location: $model->ubicacion,
            purchasePrice: Money::fromFloat($model->valor_compra),
            status: ProductStatus::ACTIVE
        );
    }
}
