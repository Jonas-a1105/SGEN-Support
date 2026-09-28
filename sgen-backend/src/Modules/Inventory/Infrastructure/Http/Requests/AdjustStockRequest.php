<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('inventario.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:inventario_items,id'],
            // Solo tipos con semántica de ajuste directo: las transferencias,
            // bajas y consumos tienen su propio flujo transaccional.
            'type' => ['required', 'string', 'in:ENTRADA,SALIDA,AJUSTE'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.in' => 'El tipo de movimiento no está disponible en este endpoint; use ENTRADA, SALIDA o AJUSTE.',
        ];
    }
}
