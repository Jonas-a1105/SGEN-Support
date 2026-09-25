<?php

declare(strict_types=1);

namespace App\Infrastructure\Support\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReassignTechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('soportes.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
        ];
    }
}
