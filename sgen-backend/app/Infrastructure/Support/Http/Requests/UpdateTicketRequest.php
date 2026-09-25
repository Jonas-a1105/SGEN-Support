<?php

declare(strict_types=1);

namespace App\Infrastructure\Support\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTicketRequest extends FormRequest
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
            'titulo' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'prioridad' => ['nullable', 'string', 'in:baja,media,alta,critica'],
            'estado' => ['nullable', 'string', 'in:pendiente,en_proceso,resuelto'],
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'fecha_cierre' => ['nullable', 'string'],
            'solucion' => ['nullable', 'string'],
            'tiempo_atencion_minutos' => ['nullable', 'integer'],
            'firma_base64' => ['nullable', 'string', 'max:1000000'],
            'firma' => ['nullable', 'string', 'max:1000000'],
        ];
    }
}
