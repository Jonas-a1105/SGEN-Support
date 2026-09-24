<?php

declare(strict_types=1);

namespace App\Infrastructure\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Inventory\Application\DTOs\ReassignEquipmentDTO;

class ReassignEquipmentRequest extends FormRequest
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
            'equipo_id' => ['required', 'integer', 'exists:equipos,id'],
            'departamento_id' => ['nullable', 'integer'],
            'empleado_id' => ['nullable', 'integer'],
            'ubicacion_fisica' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDTO(): ReassignEquipmentDTO
    {
        return ReassignEquipmentDTO::fromArray($this->validated());
    }
}
