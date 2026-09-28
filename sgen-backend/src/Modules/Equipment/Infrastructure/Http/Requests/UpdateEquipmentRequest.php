<?php

declare(strict_types=1);

namespace Modules\Equipment\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEquipmentRequest extends FormRequest
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
        $estadosValidos = [
            'disponible', 'en_uso', 'en_reparacion', 'fuera_de_servicio',
            'en_reserva', 'prestado', 'perdido', 'nuevo', 'usado',
            'Disponible', 'En uso', 'En Uso', 'Reparación', 'Reparacion',
            'En Reparación', 'Fuera de Servicio', 'Fuera de servicio',
            'En Reserva', 'Prestado', 'Perdido', 'Nuevo', 'Usado',
        ];

        return [
            'codigo_inventario' => ['nullable', 'string', 'max:50'],
            'id' => ['nullable', 'string', 'max:50'],
            'numero_serie' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'nombre' => ['nullable', 'string', 'max:100'],
            // La baja patrimonial no se escribe por edición: exige el flujo
            // formal (motivo, acta y evidencia). El alias `status` se valida
            // igual porque el DTO lo acepta como sinónimo de `estado`.
            'estado' => ['nullable', 'string', 'max:50', 'in:'.implode(',', $estadosValidos)],
            'status' => ['nullable', 'string', 'max:50', 'in:'.implode(',', $estadosValidos)],
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
            // #14 coherencia temporal: la garantía jamás vence antes de la compra.
            'garantia' => ['nullable', 'date', 'after_or_equal:fecha_compra'],
            'proveedor' => ['nullable', 'string', 'max:150'],
            // #22: versión vista por el lector (si llega, el UPDATE es atómico).
            'version' => ['nullable', 'integer', 'min:1'],
            'valor_compra' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'estado.in' => 'El estado seleccionado no es válido para edición. La baja oficial se registra únicamente desde el proceso de baja formal.',
            'status.in' => 'El estado seleccionado no es válido para edición. La baja oficial se registra únicamente desde el proceso de baja formal.',
        ];
    }
}
