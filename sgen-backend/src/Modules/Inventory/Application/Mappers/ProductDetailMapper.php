<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Mappers;

use Modules\Inventory\Domain\Models\Product;

final class ProductDetailMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(mixed $p): array
    {
        if ($p instanceof Product) {
            return [
                'id' => $p->id(),
                'sku' => $p->sku()->value(),
                'name' => $p->name(),
                'category' => $p->category(),
                'description' => $p->description(),
                'brand' => $p->brand(),
                'model' => $p->model(),
                'unit_of_measure' => $p->unitOfMeasure(),
                'current_stock' => $p->currentStock()->value(),
                'minimum_stock' => $p->minimumStock()->value(),
                'is_low_stock' => $p->isLowStock(),
                'purchase_price' => $p->purchasePrice()->amount(),
                'location' => $p->location(),
            ];
        }

        $isLow = method_exists($p, 'isLowStock')
            ? $p->isLowStock()
            : (($p->current_stock ?? $p->currentStock ?? 0) <= ($p->minimum_stock ?? $p->minimumStock ?? 0));

        return [
            'id' => is_object($p) && method_exists($p, 'id') ? $p->id() : ($p->id ?? null),
            'sku' => is_object($p) && method_exists($p, 'sku') ? (is_object($p->sku()) ? $p->sku()->value() : $p->sku()) : ($p->sku ?? ''),
            'name' => is_object($p) && method_exists($p, 'name') ? $p->name() : ($p->name ?? ''),
            'category' => is_object($p) && method_exists($p, 'category') ? $p->category() : ($p->category ?? ''),
            'description' => is_object($p) && method_exists($p, 'description') ? $p->description() : ($p->description ?? null),
            'brand' => is_object($p) && method_exists($p, 'brand') ? $p->brand() : ($p->brand ?? null),
            'model' => is_object($p) && method_exists($p, 'model') ? $p->model() : ($p->model ?? null),
            'unit_of_measure' => is_object($p) && method_exists($p, 'unitOfMeasure') ? $p->unitOfMeasure() : ($p->unit_of_measure ?? $p->unitOfMeasure ?? null),
            'current_stock' => is_object($p) && method_exists($p, 'currentStock') ? (is_object($p->currentStock()) ? $p->currentStock()->value() : $p->currentStock()) : ($p->current_stock ?? $p->currentStock ?? 0),
            'minimum_stock' => is_object($p) && method_exists($p, 'minimumStock') ? (is_object($p->minimumStock()) ? $p->minimumStock()->value() : $p->minimumStock()) : ($p->minimum_stock ?? $p->minimumStock ?? 0),
            'is_low_stock' => $isLow,
            'purchase_price' => (float) (is_object($p) && method_exists($p, 'purchasePrice') ? (is_object($p->purchasePrice()) ? $p->purchasePrice()->amount() : $p->purchasePrice()) : ($p->purchase_price ?? $p->purchasePrice ?? 0.0)),
            'location' => is_object($p) && method_exists($p, 'location') ? $p->location() : ($p->location ?? null),
        ];
    }
}
