<?php

declare(strict_types=1);

namespace App\Infrastructure\Support\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReopenTicketRequest extends FormRequest
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
            'motivo' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motivo.required' => 'El motivo de la reapertura es obligatorio.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres explicativos.',
            'motivo.max' => 'El motivo no puede exceder los 1000 caracteres.',
        ];
    }
}
