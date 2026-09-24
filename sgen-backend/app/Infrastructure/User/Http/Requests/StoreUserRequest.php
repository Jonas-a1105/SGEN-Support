<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50', 'unique:usuarios,username'],
            'password' => ['required', 'string', 'min:4', 'max:255'],
            'rol' => ['required', 'string', 'in:admin,tecnico,consultor,operador'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
        ];
    }
}
