<?php

declare(strict_types=1);

namespace Modules\Support\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReopenTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cualquier usuario autenticado puede pedir reapertura; la legitimidad
        // (solicitante o personal operativo) y la ventana las aplica el
        // dominio en una sola transacción con lock (regla #31).
        return $this->user() !== null;
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
