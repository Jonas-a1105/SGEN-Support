<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Puebla catálogos base de la empresa para poder operar la app:
 * departamentos, un empleado técnico, categorías de tickets e
 * inventario mínimo. Idempotente (solo crea lo que falta).
 */
class CoreCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDepartamentos();
        $this->seedCategorias();
        $this->seedInventario();
    }

    private function seedDepartamentos(): void
    {
        $nombres = [
            'Dpto. de Informática',
            'Dpto. de Recursos Humanos',
            'Dpto. de Contabilidad',
            'Dpto. de Ventas',
            'Dpto. de Gerencia',
            'Dpto. de Producción',
            'Dpto. de Mantenimiento',
        ];

        foreach ($nombres as $nombre) {
            DB::table('departamentos')->updateOrInsert(
                ['nombre' => $nombre],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function seedCategorias(): void
    {
        $categorias = [
            ['nombre' => 'Hardware', 'descripcion' => 'Problemas físicos de equipos', 'icono' => 'bi-cpu', 'color' => '#dc3545'],
            ['nombre' => 'Software', 'descripcion' => 'Errores de programas y SO', 'icono' => 'bi-window', 'color' => '#0d6efd'],
            ['nombre' => 'Red', 'descripcion' => 'Conectividad e internet', 'icono' => 'bi-wifi', 'color' => '#198754'],
            ['nombre' => 'Impresora', 'descripcion' => 'Problemas de impresión', 'icono' => 'bi-printer', 'color' => '#ffc107'],
            ['nombre' => 'Periféricos', 'descripcion' => 'Mouse, teclado, monitor', 'icono' => 'bi-mouse', 'color' => '#6c757d'],
            ['nombre' => 'Otro', 'descripcion' => 'Otros problemas', 'icono' => 'bi-question-circle', 'color' => '#6c757d'],
        ];

        foreach ($categorias as $cat) {
            DB::table('categorias')->updateOrInsert(
                ['nombre' => $cat['nombre']],
                array_merge($cat, ['activo' => true, 'created_at' => now()])
            );
        }
    }

    private function seedInventario(): void
    {
        $items = [
            ['codigo' => 'CAB-001', 'nombre' => 'Cable Patch Cord Cat6 2m', 'categoria' => 'Red', 'stock_actual' => 50, 'stock_minimo' => 10],
            ['codigo' => 'TON-001', 'nombre' => 'Toner HP 85A', 'categoria' => 'Impresora', 'stock_actual' => 8, 'stock_minimo' => 5],
            ['codigo' => 'TEC-001', 'nombre' => 'Teclado USB estándar', 'categoria' => 'Periféricos', 'stock_actual' => 15, 'stock_minimo' => 5],
            ['codigo' => 'MOU-001', 'nombre' => 'Mouse óptico USB', 'categoria' => 'Periféricos', 'stock_actual' => 20, 'stock_minimo' => 5],
            ['codigo' => 'RAM-001', 'nombre' => 'Memoria RAM DDR4 8GB', 'categoria' => 'Hardware', 'stock_actual' => 6, 'stock_minimo' => 3],
        ];

        foreach ($items as $item) {
            DB::table('inventario_items')->updateOrInsert(
                ['codigo' => $item['codigo']],
                [
                    'nombre' => $item['nombre'],
                    'categoria' => $item['categoria'],
                    'stock_actual' => $item['stock_actual'],
                    'stock_minimo' => $item['stock_minimo'],
                    'unidad_medida' => 'unidad',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
