<?php

declare(strict_types=1);

namespace Modules\Equipment\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Equipment\Application\DTOs\TransferEquipmentDTO;

class TransferEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('equipos.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'equipo_id' => ['required', 'integer', 'exists:equipos,id'],
            'departamento_origen_id' => ['required', 'integer', 'exists:departamentos,id'],
            'departamento_destino_id' => ['required', 'integer', 'exists:departamentos,id'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDTO(): TransferEquipmentDTO
    {
        return TransferEquipmentDTO::fromArray($this->validated());
    }
}
