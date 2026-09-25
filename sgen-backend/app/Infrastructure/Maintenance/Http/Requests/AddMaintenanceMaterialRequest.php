<?php

declare(strict_types=1);

namespace App\Infrastructure\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AddMaintenanceMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:inventario_items,id'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:10000'],
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
