<?php

declare(strict_types=1);

namespace Modules\Support\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('soportes.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'prioridad' => ['nullable', 'string', 'in:baja,media,alta,critica'],
            'estado' => ['nullable', 'string', 'in:pendiente,en_proceso,en_espera,resuelto,cerrado,cancelado'],
            'empleado_id' => ['nullable', 'integer', 'exists:empleados,id'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'solucion' => ['nullable', 'string', 'max:5000'],
            // `fecha_cierre` y `tiempo_atencion_minutos` NO se aceptan aquí:
            // los calcula el servidor (anti-fraude de SLA). La fecha de cierre
            // solo se toca por su endpoint dedicado.
            // Firma de conformidad en el mismo flujo: mismo rigor probatorio.
            'firma_base64' => ['nullable', 'string', 'starts_with:data:image/', 'min:100', 'max:1000000'],
            // #22: versionado optimista del ticket.
            'version' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
