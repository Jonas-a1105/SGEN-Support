<?php

declare(strict_types=1);

namespace Modules\Equipment\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEquipmentRequest extends FormRequest
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
            'codigo_inventario' => ['nullable', 'string', 'max:50', 'unique:equipos,codigo_inventario'],
            'id' => ['nullable', 'string', 'max:50'],
            'numero_serie' => ['nullable', 'string', 'max:100', 'unique:equipos,numero_serie'],
            'tipo' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'nombre' => ['nullable', 'string', 'max:100'],
            // Estados formales del enum de la app + etiquetas legadas que el
            // dominio normaliza vía EquipmentStatus::fromLabel().
            'estado' => ['nullable', 'string', 'max:50', 'in:disponible,en_uso,en_reparacion,fuera_de_servicio,en_reserva,prestado,de_baja,perdido,nuevo,usado,Disponible,En Uso,En Reparación,Reparación,Fuera de Servicio,Baja,En Reserva'],
            'status' => ['nullable', 'string', 'max:50'],
            'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'ubicacion_fisica' => ['nullable', 'string', 'max:255'],
            'procesador' => ['nullable', 'string', 'max:100'],
            'memoria_ram' => ['nullable', 'string', 'max:50'],
            'almacenamiento' => ['nullable', 'string', 'max:100'],
            'sistema_operativo' => ['nullable', 'string', 'max:100'],
            // #1: formato de IP válido — la unicidad la garantiza la BD.
            'direccion_ip' => ['nullable', 'string', 'max:50', 'ip'],
            'driver' => ['nullable', 'string', 'max:150'],
            'toner' => ['nullable', 'string', 'max:100'],
            'fecha_compra' => ['nullable', 'date'],
            // #14 coherencia temporal: la garantía jamás vence antes de la compra.
            'garantia' => ['nullable', 'date', 'after_or_equal:fecha_compra'],
            'proveedor' => ['nullable', 'string', 'max:150'],

            'valor_compra' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
