<?php

declare(strict_types=1);

namespace Modules\Category\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('categorias.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'icono' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{3,8}$/'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
