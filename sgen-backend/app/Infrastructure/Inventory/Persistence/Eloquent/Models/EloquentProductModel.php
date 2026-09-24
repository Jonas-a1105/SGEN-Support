<?php

declare(strict_types=1);

namespace App\Infrastructure\Inventory\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $categoria
 * @property string|null $descripcion
 * @property string|null $marca
 * @property string|null $modelo
 * @property string|null $unidad_medida
 * @property int $stock_actual
 * @property int $stock_minimo
 * @property string|null $ubicacion
 * @property string|null $fecha_compra
 * @property string|null $proveedor
 * @property string|null $proveedor_rif
 * @property string|null $garantia_fin
 * @property float $valor_compra
 */
class EloquentProductModel extends Model
{
    protected $table = 'inventario_items';

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'descripcion',
        'marca',
        'modelo',
        'unidad_medida',
        'stock_actual',
        'stock_minimo',
        'ubicacion',
        'fecha_compra',
        'proveedor',
        'proveedor_rif',
        'garantia_fin',
        'valor_compra',
    ];

    protected $casts = [
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'valor_compra' => 'float',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(EloquentStockMovementModel::class, 'item_id');
    }
}
