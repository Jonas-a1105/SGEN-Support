<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Inventory\Application\DTOs\TransferStockDTO;

class TransferStockRequest extends FormRequest
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
            'item_id' => ['required', 'integer', 'exists:inventario_items,id'],
            'origen_id' => ['nullable', 'integer'],
            'destino_id' => ['required', 'integer'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDTO(): TransferStockDTO
    {
        return TransferStockDTO::fromArray($this->validated());
    }
}
