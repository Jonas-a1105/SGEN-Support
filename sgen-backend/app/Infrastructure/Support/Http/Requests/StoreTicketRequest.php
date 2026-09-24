<?php

declare(strict_types=1);

namespace App\Infrastructure\Support\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTicketRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'equipo_id' => ['nullable', 'integer', 'exists:equipos,id'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'prioridad' => ['nullable', 'string', 'in:baja,media,alta,critica'],
            'estado' => ['nullable', 'string', 'in:pendiente,en_proceso,resuelto'],
            'solicitante' => ['nullable', 'string', 'max:255'],
            'departamento' => ['nullable', 'string', 'max:255'],
        ];
    }
}
