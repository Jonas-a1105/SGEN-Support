<?php

declare(strict_types=1);

namespace Modules\Equipment\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Wizard de baja patrimonial (Módulo 11): nadie retira un activo sin motivo
 * legal, valor recuperable declarado, destino y ruta de saneamiento regida.
 */
class DecommissionEquipmentRequest extends FormRequest
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
            'motivo' => ['required', 'string', 'in:robo,obsolescencia,donacion,venta,desecho'],
            'valor_recuperacion' => ['nullable', 'numeric', 'min:0'],
            'destino' => ['nullable', 'string', 'max:200'],
            'nota' => ['nullable', 'string', 'max:1000'],
            // Consentimiento explícito: la baja es irreversible para el ciclo normal.
            'confirmar_irreversible' => ['required', 'accepted'],
        ];
    }
}
