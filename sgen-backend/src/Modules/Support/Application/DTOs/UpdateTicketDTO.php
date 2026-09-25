<?php

declare(strict_types=1);

namespace Modules\Support\Application\DTOs;

final class UpdateTicketDTO
{
    public function __construct(
        public readonly ?string $titulo = null,
        public readonly ?string $descripcion = null,
        public readonly ?string $prioridad = null,
        public readonly ?string $estado = null,
        public readonly ?int $empleadoId = null,
        public readonly ?int $categoriaId = null,
        public readonly ?string $fechaCierre = null,
        public readonly ?string $solucion = null,
        public readonly ?int $tiempoAtencionMinutos = null,
        public readonly ?string $firma = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            titulo: isset($data['titulo']) ? (string) $data['titulo'] : (isset($data['title']) ? (string) $data['title'] : null),
            descripcion: isset($data['descripcion']) ? (string) $data['descripcion'] : (isset($data['description']) ? (string) $data['description'] : null),
            prioridad: isset($data['prioridad']) ? (string) $data['prioridad'] : (isset($data['priority']) ? (string) $data['priority'] : null),
            estado: isset($data['estado']) ? (string) $data['estado'] : (isset($data['status']) ? (string) $data['status'] : null),
            empleadoId: isset($data['empleado_id']) ? (int) $data['empleado_id'] : null,
            categoriaId: isset($data['categoria_id']) ? (int) $data['categoria_id'] : null,
            fechaCierre: isset($data['fecha_cierre']) ? (string) $data['fecha_cierre'] : null,
            solucion: isset($data['solucion']) ? (string) $data['solucion'] : null,
            tiempoAtencionMinutos: isset($data['tiempo_atencion_minutos']) ? (int) $data['tiempo_atencion_minutos'] : null,
            firma: isset($data['firma_base64']) ? (string) $data['firma_base64'] : (isset($data['firma']) ? (string) $data['firma'] : null)
        );
    }
}
