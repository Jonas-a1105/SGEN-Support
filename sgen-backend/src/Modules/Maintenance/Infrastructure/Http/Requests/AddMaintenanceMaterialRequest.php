<?php

declare(strict_types=1);

namespace Modules\Maintenance\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AddMaintenanceMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('mantenimientos.manage');
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:inventario_items,id'],
            // #37: cantidades fraccionarias válidas, tope razonable.
            'cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3', 'max:10000'],
        ];
    }

    public function itemId(): int
    {
        return (int) $this->validated()['item_id'];
    }

    public function cantidad(): int
    {
        return (int) $this->validated()['cantidad'];
    }
}
