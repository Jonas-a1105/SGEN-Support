<?php

declare(strict_types=1);

namespace App\Infrastructure\Equipment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEquipmentRequest extends FormRequest
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
            'codigo_inventario' => ['nullable', 'string', 'max:50'],
            'id' => ['nullable', 'string', 'max:50'],
            'numero_serie' => ['nullable', 'string', 'max:100'],
            'tipo' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'ubicacion_fisica' => ['nullable', 'string', 'max:255'],
            'procesador' => ['nullable', 'string', 'max:100'],
            'memoria_ram' => ['nullable', 'string', 'max:50'],
            'almacenamiento' => ['nullable', 'string', 'max:100'],
            'sistema_operativo' => ['nullable', 'string', 'max:100'],
            'direccion_ip' => ['nullable', 'string', 'max:50'],
            'driver' => ['nullable', 'string', 'max:150'],
            'toner' => ['nullable', 'string', 'max:100'],
            'fecha_compra' => ['nullable', 'date'],
            'proveedor' => ['nullable', 'string', 'max:150'],
            'garantia' => ['nullable', 'date'],
            'valor_compra' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
