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
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'equipo_id' => ['required', 'integer', 'exists:equipos,id'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'prioridad' => ['required_without_all:impacto,urgencia', 'nullable', 'string', 'in:baja,media,alta,critica'],
            'impacto' => ['nullable', 'string', 'in:bajo,medio,alto'],
            'urgencia' => ['nullable', 'string', 'in:baja,media,alta'],
            'estado' => ['sometimes', 'string', 'in:pendiente,en_proceso,resuelto'],
        ];
    }
}
