<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Application\Mappers\ProductDetailMapper;
use Modules\Inventory\Domain\Models\Product;

final readonly class GetInventoryDashboardUseCase
{
    public function __construct(
        private ListProductsUseCase $listProductsUseCase,
        private GetInventoryKpisUseCase $getInventoryKpisUseCase
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $paginated = $this->listProductsUseCase->execute($filters, perPage: $perPage, page: $page);

        $equipos = DB::table('equipos')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->leftJoin('empleados', 'equipos.empleado_id', '=', 'empleados.id')
            ->select('equipos.*', 'departamentos.nombre as departamento_nombre', 'empleados.nombre as empleado_nombre', 'empleados.apellido as empleado_apellido')
            ->orderBy('equipos.codigo_inventario')
            ->get();

        return [
            'products' => array_map(fn (Product $p): array => ProductDetailMapper::toArray($p), $paginated['items']),
            'pagination' => [
                'total' => $paginated['total'],
                'per_page' => $paginated['per_page'],
                'current_page' => $paginated['current_page'],
                'last_page' => $paginated['last_page'],
            ],
            'kpis' => $this->getInventoryKpisUseCase->execute(),
            'filters' => $filters,
            'equipos' => $equipos,
            'departamentos' => DB::table('departamentos')->select('id', 'nombre')->orderBy('nombre')->get(),
            'empleados' => DB::table('empleados')->select('id', 'nombre', 'apellido', 'departamento_id')->orderBy('nombre')->get(),
            'movimientos' => DB::table('inventario_movimientos')
                ->leftJoin('usuarios', 'inventario_movimientos.usuario_id', '=', 'usuarios.id')
                ->select('inventario_movimientos.*', 'usuarios.username as usuario_nombre')
                ->orderByDesc('inventario_movimientos.id')
                ->limit(50)
                ->get(),
            'ubicaciones' => DB::table('inventario_ubicaciones')
                ->leftJoin('departamentos', 'inventario_ubicaciones.departamento_id', '=', 'departamentos.id')
                ->select('inventario_ubicaciones.*', 'departamentos.nombre as departamento_nombre')
                ->get(),
        ];
    }
}
