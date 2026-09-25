<?php

declare(strict_types=1);

namespace Modules\Employee\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('personal.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'firstName' => ['nullable', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'lastName' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'cedula' => ['nullable', 'string', 'max:20'],
            'idDoc' => ['nullable', 'string', 'max:20'],
            'cargo' => ['nullable', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'departmentId' => ['nullable', 'integer', 'exists:departamentos,id'],
            'rol' => ['nullable', 'string', 'in:tecnico,administrador,consultor'],
            'role' => ['nullable', 'string'],
            'usuario_id' => ['nullable', 'integer', 'exists:usuarios,id'],
            'userId' => ['nullable', 'integer', 'exists:usuarios,id'],
        ];
    }
}
