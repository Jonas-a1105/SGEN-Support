<?php

declare(strict_types=1);

namespace Modules\Equipment\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Equipment\Application\DTOs\RegisterEquipmentDTO;

class RegisterEquipmentRequest extends FormRequest
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
            'codigo_inventario' => ['required', 'string', 'max:50', 'unique:equipos,codigo_inventario'],
            'tipo' => ['required', 'string', 'max:50'],
            'marca' => ['required', 'string', 'max:100'],
            'modelo' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'in:disponible,en_uso,en_reparacion,fuera_de_servicio,en_reserva,prestado,de_baja,perdido'],
            'numero_serie' => ['nullable', 'string', 'max:100', 'unique:equipos,numero_serie'],
            'procesador' => ['nullable', 'string', 'max:100'],
            'memoria_ram' => ['nullable', 'string', 'max:100'],
            'almacenamiento' => ['nullable', 'string', 'max:100'],
            'sistema_operativo' => ['nullable', 'string', 'max:100'],
            'direccion_ip' => ['nullable', 'ip', 'max:100'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
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
