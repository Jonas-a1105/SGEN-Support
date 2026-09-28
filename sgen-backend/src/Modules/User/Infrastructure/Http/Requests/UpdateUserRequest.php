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
            // Los campos omitidos se preservan en el caso de uso; lo que no se
            // permite es vaciar el email sin dejar empleado vinculado, porque
            // la cuenta perdería toda vía de recuperación de contraseña.
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'email' => ['nullable', 'email', 'max:150', 'unique:usuarios,email,'.$userId],
        ];
    }

    /**
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function ($validator): void {
                if (! $this->has('email')) {
                    return;
                }

                $email = $this->input('email');
                $empleado = $this->input('empleado_id');

                if (($email === null || $email === '') && ($empleado === null || $empleado === '')) {
                    $validator->errors()->add(
                        'email',
                        'La cuenta necesita un email o un empleado vinculado para la recuperación de contraseña.'
                    );
                }
            },
        ];
    }
}
