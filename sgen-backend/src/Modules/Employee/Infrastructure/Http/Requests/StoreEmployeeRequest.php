<?php

declare(strict_types=1);

namespace Modules\Employee\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $cedulaUnica = Rule::unique('empleados', 'cedula')->whereNull('deleted_at');

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'firstName' => ['nullable', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'lastName' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('empleados', 'email')],
            'cedula' => ['nullable', 'string', 'max:15', clone $cedulaUnica],
            'idDoc' => ['nullable', 'string', 'max:15', clone $cedulaUnica],
            'cargo' => ['nullable', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'departmentId' => ['nullable', 'integer', 'exists:departamentos,id'],
            // El enum real de `empleados.rol` solo admite estos valores;
            // cualquier otro revienta en Postgres al persistir.
            'rol' => ['nullable', 'string', 'in:tecnico,administrador,consultor'],
            'role' => ['nullable', 'string', 'in:tecnico,administrador,consultor'],
            'usuario_id' => ['nullable', 'integer', 'exists:usuarios,id'],
            'userId' => ['nullable', 'integer', 'exists:usuarios,id'],
        ];
    }
}
