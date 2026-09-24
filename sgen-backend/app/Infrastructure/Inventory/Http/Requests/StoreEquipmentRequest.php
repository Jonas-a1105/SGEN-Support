<?php

declare(strict_types=1);

namespace App\Infrastructure\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Inventory\Application\DTOs\RegisterEquipmentDTO;

class StoreEquipmentRequest extends FormRequest
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
            'codigo_inventario' => ['required', 'string', 'max:50'],
            'tipo' => ['required', 'string', 'max:50'],
            'marca' => ['required', 'string', 'max:100'],
            'modelo' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:50'],
            'numero_serie' => ['nullable', 'string', 'max:100'],
            'procesador' => ['nullable', 'string', 'max:100'],
            'memoria_ram' => ['nullable', 'string', 'max:100'],
            'almacenamiento' => ['nullable', 'string', 'max:100'],
            'sistema_operativo' => ['nullable', 'string', 'max:100'],
            'direccion_ip' => ['nullable', 'string', 'max:100'],
            'departamento_id' => ['nullable', 'integer'],
            'empleado_id' => ['nullable', 'integer'],
            'ubicacion_fisica' => ['nullable', 'string', 'max:255'],
            'valor_compra' => ['nullable', 'numeric', 'min:0'],
            'proveedor' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function toDTO(): RegisterEquipmentDTO
    {
        return RegisterEquipmentDTO::fromArray($this->validated());
    }
}
