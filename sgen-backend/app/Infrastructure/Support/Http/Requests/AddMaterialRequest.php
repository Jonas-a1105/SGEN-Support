<?php

declare(strict_types=1);

namespace App\Infrastructure\Support\Http\Requests;

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
            'cantidad' => ['required', 'integer', 'min:1'],
        ];
    }
}
