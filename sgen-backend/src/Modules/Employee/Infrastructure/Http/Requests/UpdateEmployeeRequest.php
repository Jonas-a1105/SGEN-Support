<?php

declare(strict_types=1);

namespace Modules\Employee\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEmployeeRequest extends FormRequest
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
        $employeeId = $this->route('id');

        // `empleados.email` es UNIQUE en BD (incluye filas en papelera), por eso
        // el ignore del propio registro es obligatorio para no bloquear su edición.
        $emailUnico = Rule::unique('empleados', 'email')->ignore($employeeId);

        // `cedula` es única solo entre empleados vigentes (índice parcial).
        $cedulaUnica = Rule::unique('empleados', 'cedula')
            ->whereNull('deleted_at')
            ->ignore($employeeId);

        return [
            'nombre' => ['nullable', 'string', 'max:100'],
            'firstName' => ['nullable', 'string', 'max:100'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'lastName' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150', $emailUnico],
            'cedula' => ['nullable', 'string', 'max:15', clone $cedulaUnica],
            'idDoc' => ['nullable', 'string', 'max:15', clone $cedulaUnica],
            'cargo' => ['nullable', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'departmentId' => ['nullable', 'integer', 'exists:departamentos,id'],
            // Paridad con el enum real de `empleados.rol` (migración 000005).
            'rol' => ['nullable', 'string', 'in:tecnico,administrador,consultor'],
            'role' => ['nullable', 'string', 'in:tecnico,administrador,consultor'],
            'usuario_id' => ['nullable', 'integer', 'exists:usuarios,id'],
            'userId' => ['nullable', 'integer', 'exists:usuarios,id'],
        ];
    }
}
