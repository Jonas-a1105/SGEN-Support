<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Http\Requests;

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
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
        ];
    }
}
