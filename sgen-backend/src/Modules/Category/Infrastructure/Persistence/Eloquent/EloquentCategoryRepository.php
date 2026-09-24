<?php

declare(strict_types=1);

namespace Modules\Category\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Category\Domain\Models\Category;
use Modules\Category\Domain\Ports\CategoryRepositoryInterface;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<array<string, mixed>>
     */
    public function listWithStats(array $filters = []): array
    {
        $query = DB::table('categorias')
            ->leftJoin('soportes', 'categorias.id', '=', 'soportes.categoria_id')
            ->select(
                'categorias.id',
                'categorias.nombre',
                'categorias.descripcion',
                'categorias.icono',
                'categorias.color',
                'categorias.activo',
                DB::raw('COUNT(soportes.id) as total_tickets')
            )
            ->groupBy(
                'categorias.id',
                'categorias.nombre',
                'categorias.descripcion',
                'categorias.icono',
                'categorias.color',
                'categorias.activo'
            );

        if (!empty($filters['search'])) {
            $search = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('categorias.nombre', 'like', $search)
                    ->orWhere('categorias.descripcion', 'like', $search);
            });
        }

        return $query->orderBy('categorias.nombre')->get()->all();
    }

    public function findById(int $id): ?Category
    {
        $row = DB::table('categorias')->where('id', $id)->first();
        if ($row === null) {
            return null;
        }

        return Category::create(
            name: (string) $row->nombre,
            description: $row->descripcion !== null ? (string) $row->descripcion : null,
            icon: (string) ($row->icono ?? 'hardware'),
            color: (string) ($row->color ?? '#0d6efd'),
            active: (bool) ($row->activo ?? true),
            id: (int) $row->id
        );
    }

    public function save(Category $category): int
    {
        return (int) DB::table('categorias')->insertGetId([
            'nombre' => $category->name(),
            'descripcion' => $category->description(),
            'icono' => $category->icon(),
            'color' => $category->color(),
            'activo' => $category->isActive() ? 1 : 0,
            'created_at' => now(),
        ]);
    }

    public function update(Category $category): void
    {
        DB::table('categorias')
            ->where('id', $category->id())
            ->update([
                'nombre' => $category->name(),
                'descripcion' => $category->description(),
                'icono' => $category->icon(),
                'color' => $category->color(),
                'activo' => $category->isActive() ? 1 : 0,
            ]);
    }

    public function delete(int $id): void
    {
        DB::table('categorias')->where('id', $id)->delete();
    }

    public function getKpis(): array
    {
        $totalActivas = DB::table('categorias')->where('activo', 1)->count();
        $totalTickets = DB::table('soportes')->count();

        $hardwareTickets = DB::table('soportes')
            ->join('categorias', 'soportes.categoria_id', '=', 'categorias.id')
            ->where('categorias.nombre', 'like', '%Hardware%')
            ->count();

        $hardwarePct = $totalTickets > 0 ? (int) round(($hardwareTickets / $totalTickets) * 100) : 42;

        return [
            'total_activas' => $totalActivas,
            'hardware_stat' => "{$hardwarePct}%",
            'red_stat' => 'Alta prioridad',
        ];
    }
}
