<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('usuarios.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('usuario') ?? $this->route('id');

        return [
            'username' => ['required', 'string', 'max:50', 'unique:usuarios,username,'.$userId],
            'password' => ['nullable', 'string', Password::min(10)->mixedCase()->numbers()->uncompromised()],
            'rol' => ['required', 'string', 'in:admin,tecnico,consultor,operador'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            // Sin email no hay recuperación self-service: empleado con email válido
            // o email directo de la cuenta sigue siendo pasaje obligatorio.
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'email' => ['nullable', 'email', 'max:150', 'unique:usuarios,email,'.$userId],
        ];
    }
}
