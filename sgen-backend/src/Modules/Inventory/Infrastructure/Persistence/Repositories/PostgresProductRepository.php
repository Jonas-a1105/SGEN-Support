<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Repositories;

use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\EloquentProductModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\EloquentStockMovementModel;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Application\DTOs\TransferStockDTO;
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

    public function getRecentMovements(int $limit = 50): array
    {
        return DB::table('inventario_movimientos')
            ->leftJoin('usuarios', 'inventario_movimientos.usuario_id', '=', 'usuarios.id')
            ->select('inventario_movimientos.*', 'usuarios.username as usuario_nombre')
            ->orderByDesc('inventario_movimientos.id')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function getMovementsByProductId(int $productId, int $limit = 100): array
    {
        return DB::table('inventario_movimientos')
            ->leftJoin('usuarios', 'inventario_movimientos.usuario_id', '=', 'usuarios.id')
            ->leftJoin('departamentos as orig_dept', 'inventario_movimientos.origen_departamento_id', '=', 'orig_dept.id')
            ->leftJoin('departamentos as dest_dept', 'inventario_movimientos.destino_departamento_id', '=', 'dest_dept.id')
            ->where('inventario_movimientos.item_id', $productId)
            ->select(
                'inventario_movimientos.*',
                'usuarios.username as usuario_nombre',
                'orig_dept.nombre as origen_departamento_nombre',
                'dest_dept.nombre as destino_departamento_nombre'
            )
            ->orderByDesc('inventario_movimientos.id')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function getLocations(): array
    {
        return DB::table('inventario_ubicaciones')
            ->leftJoin('departamentos', 'inventario_ubicaciones.departamento_id', '=', 'departamentos.id')
            ->select('inventario_ubicaciones.*', 'departamentos.nombre as departamento_nombre')
            ->get()
            ->all();
    }

    public function getInventoryDashboardLookups(): array
    {
        $equipos = DB::table('equipos')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->leftJoin('empleados', 'equipos.empleado_id', '=', 'empleados.id')
            ->whereIn('equipos.estado', ['disponible', 'nuevo', 'en_reserva'])
            ->select('equipos.*', 'departamentos.nombre as departamento_nombre', 'empleados.nombre as empleado_nombre', 'empleados.apellido as empleado_apellido')
            ->orderBy('equipos.codigo_inventario')
            ->get()
            ->all();

        $departamentos = DB::table('departamentos')
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get()
            ->all();

        $empleados = DB::table('empleados')
            ->select('id', 'nombre', 'apellido', 'departamento_id')
            ->orderBy('nombre')
            ->get()
            ->all();

        return [
            'equipos' => $equipos,
            'departamentos' => $departamentos,
            'empleados' => $empleados,
        ];
    }

    public function transferStock(TransferStockDTO $dto, int $userId): void
    {
        DB::transaction(function () use ($dto, $userId): void {
            if ($dto->origenId !== null) {
                DB::table('inventario_ubicaciones')
                    ->where('item_id', $dto->itemId)
                    ->where('departamento_id', $dto->origenId)
                    ->decrement('cantidad', $dto->cantidad);
            }

            $dest = DB::table('inventario_ubicaciones')
                ->where('item_id', $dto->itemId)
                ->where('departamento_id', $dto->destinoId)
                ->first();

            if ($dest) {
                DB::table('inventario_ubicaciones')
                    ->where('id', $dest->id)
                    ->increment('cantidad', $dto->cantidad);
            } else {
                DB::table('inventario_ubicaciones')->insert([
                    'item_id' => $dto->itemId,
                    'departamento_id' => $dto->destinoId,
                    'cantidad' => $dto->cantidad,
                    'updated_at' => now(),
                ]);
            }

            DB::table('inventario_movimientos')->insert([
                'item_id' => $dto->itemId,
                'usuario_id' => $userId,
                'origen_departamento_id' => $dto->origenId,
                'destino_departamento_id' => $dto->destinoId,
                'tipo_movimiento' => 'TRANSFERENCIA',
                'cantidad' => $dto->cantidad,
                'motivo' => $dto->motivo ?? 'Transferencia entre almacenes',
                'fecha' => now(),
            ]);
        });
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
