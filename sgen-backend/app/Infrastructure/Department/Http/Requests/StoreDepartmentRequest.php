<?php

declare(strict_types=1);

namespace App\Infrastructure\Department\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDepartmentRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:100'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'jefe_area_nombre' => ['nullable', 'string', 'max:200'],
            'manager' => ['nullable', 'string', 'max:200'],
            'jefe_area_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'managerId' => ['nullable', 'integer', 'exists:empleados,id'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'desc' => ['nullable', 'string', 'max:500'],
        ];
    }
}
