<?php

declare(strict_types=1);

namespace App\Infrastructure\Employee\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEmployeeRequest extends FormRequest
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
            'nombre' => ['nullable', 'string', 'max:100'],
            'firstName' => ['nullable', 'string', 'max:100'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'lastName' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'cedula' => ['nullable', 'string', 'max:20'],
            'idDoc' => ['nullable', 'string', 'max:20'],
            'cargo' => ['nullable', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'departmentId' => ['nullable', 'integer', 'exists:departamentos,id'],
            'rol' => ['nullable', 'string', 'in:tecnico,administrador,consultor'],
            'role' => ['nullable', 'string'],
            'usuario_id' => ['nullable', 'integer'],
            'userId' => ['nullable', 'integer'],
        ];
    }
}
