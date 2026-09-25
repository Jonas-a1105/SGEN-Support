<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EloquentStockMovementModel extends Model
{
    protected $table = 'inventario_movimientos';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'usuario_id',
        'origen_departamento_id',
        'destino_departamento_id',
        'tipo_movimiento',
        'cantidad',
        'motivo',
        'referencia_id',
        'fecha',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'fecha' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(EloquentProductModel::class, 'item_id');
    }
}
