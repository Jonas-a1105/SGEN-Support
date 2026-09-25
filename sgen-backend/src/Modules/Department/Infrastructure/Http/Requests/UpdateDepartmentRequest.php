<?php

declare(strict_types=1);

namespace Modules\Department\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('departamentos.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['nullable', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:100'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'jefe_area_nombre' => ['nullable', 'string', 'max:200'],
            'manager' => ['nullable', 'string', 'max:200'],
            'jefe_area_id' => ['nullable', 'integer'],
            'managerId' => ['nullable', 'integer'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'desc' => ['nullable', 'string', 'max:500'],
        ];
    }
}
