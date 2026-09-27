<?php

declare(strict_types=1);

namespace Modules\Support\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AddMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('soportes.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:inventario_items,id'],
            // #37: cantidades fraccionales válidas (metros, rollos, litros…).
            'cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
        ];
    }
}
