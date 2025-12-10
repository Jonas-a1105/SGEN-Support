<?php
namespace App\Services;

use App\Models\Soporte;
use App\Models\Equipo;
use App\Models\Usuario;
use App\Models\Notificacion;
use App\Models\Empleado;

/**
 * TicketService - Gestión de lógica de negocio para tickets de soporte
 */
class TicketService
{
    private $soporteModel;
    private $equipoModel;
    private $usuarioModel;
    private $notificacionModel;

    public function __construct()
    {
        $this->soporteModel = new Soporte();
        $this->equipoModel = new Equipo();
        $this->usuarioModel = new Usuario();
        $this->notificacionModel = new Notificacion();
    }

    /**
     * Crea un nuevo ticket de soporte
     */
    public function crearTicket(array $datos, int $usuarioId): ?int
    {
        $datos['fecha'] = date('Y-m-d H:i:s');
        $datos['estado'] = 'pendiente';
        $datos['usuario_creacion_id'] = $usuarioId;

        $ticketId = $this->soporteModel->create($datos);

        if ($ticketId) {
            // Actualizar estado del equipo a "En reparación"
            $this->equipoModel->update($datos['equipo_id'], ['estado' => 'en_reparacion']);
            
            // Notificar a todos los técnicos
            $this->notificarTecnicos($ticketId);
        }

        return $ticketId;
    }

    /**
     * Actualiza un ticket existente
     */
    public function actualizarTicket(int $id, array $datos): bool
    {
        return $this->soporteModel->update($id, $datos);
    }

    /**
     * Asigna un técnico a un ticket
     */
    public function asignarTecnico(int $soporteId, int $empleadoId): bool
    {
        $updateData = [
            'empleado_id' => $empleadoId,
            'estado' => 'en_proceso'
        ];

        $resultado = $this->soporteModel->update($soporteId, $updateData);

        if ($resultado) {
            // Notificar al técnico asignado
            $empleado = (new Empleado())->findById($empleadoId);
            if ($empleado && $empleado->usuario_id) {
                $this->notificacionModel->createNotification(
                    $empleado->usuario_id,
                    "Te han asignado el ticket #{$soporteId}",
                    "/soportes/ver/{$soporteId}"
                );
            }
        }

        return $resultado;
    }

    /**
     * Marca un ticket como resuelto y calcula tiempos
     */
    public function resolverTicket(int $id): array
    {
        $soporte = $this->soporteModel->findById($id);
        
        if (!$soporte) {
            return ['success' => false, 'error' => "Ticket #{$id} no encontrado."];
        }

        if ($soporte->estado !== 'en_proceso') {
            return ['success' => false, 'error' => "El ticket debe estar 'en proceso' para resolverse."];
        }

        $fechaCierre = date('Y-m-d H:i:s');
        $tiempoMinutos = (strtotime($fechaCierre) - strtotime($soporte->fecha)) / 60;

        $updateData = [
            'estado' => 'resuelto',
            'fecha_cierre' => $fechaCierre,
            'tiempo_atencion_minutos' => round($tiempoMinutos)
        ];

        $resultado = $this->soporteModel->update($id, $updateData);

        if ($resultado) {
            // Actualizar estado del equipo: si tiene USUARIO asignado vuelve a 'en_uso', si solo depto o nada -> 'disponible'
            $equipo = $this->equipoModel->findById($soporte->equipo_id);
            if ($equipo) {
                // El usuario pidió: "si esta asignado a un usuario solamente" -> en_uso
                // "si solo esta asignado a un departamento entonces pasa a disponible"
                $nuevoEstado = (!empty($equipo->empleado_id)) ? 'en_uso' : 'disponible';
                $this->equipoModel->update($soporte->equipo_id, ['estado' => $nuevoEstado]);
            }

            // Notificar al creador del ticket
            if ($soporte->usuario_creacion_id) {
                $this->notificacionModel->createNotification(
                    $soporte->usuario_creacion_id,
                    "Tu ticket #{$id} ha sido resuelto.",
                    "/soportes/ver/{$id}"
                );
            }
        }

        return ['success' => $resultado, 'soporte' => $soporte];
    }

    /**
     * Pone un ticket en espera
     */
    public function ponerEnEspera(int $id): array
    {
        $soporte = $this->soporteModel->findById($id);
        
        if (!$soporte) {
            return ['success' => false, 'error' => "Ticket #{$id} no encontrado."];
        }

        if ($soporte->estado !== 'en_proceso') {
            return ['success' => false, 'error' => "El ticket debe estar 'en proceso' para ponerse en espera."];
        }

        $resultado = $this->soporteModel->update($id, ['estado' => 'en_espera']);
        return ['success' => $resultado];
    }

    /**
     * Reanuda un ticket en espera
     */
    public function reanudarTicket(int $id): array
    {
        $soporte = $this->soporteModel->findById($id);
        
        if (!$soporte) {
            return ['success' => false, 'error' => "Ticket #{$id} no encontrado."];
        }

        if ($soporte->estado !== 'en_espera') {
            return ['success' => false, 'error' => "El ticket debe estar 'en espera' para reanudarse."];
        }

        $resultado = $this->soporteModel->update($id, ['estado' => 'en_proceso']);
        return ['success' => $resultado];
    }

    /**
     * Actualiza la fecha de cierre (solo admin)
     */
    public function actualizarFechaCierre(int $id, string $nuevaFecha): array
    {
        $soporte = $this->soporteModel->findById($id);
        
        if (!$soporte) {
            return ['success' => false, 'error' => 'Ticket no encontrado.'];
        }

        $fechaCierreMySQL = date('Y-m-d H:i:s', strtotime($nuevaFecha));
        $tiempoMinutos = (strtotime($fechaCierreMySQL) - strtotime($soporte->fecha)) / 60;

        $resultado = $this->soporteModel->update($id, [
            'fecha_cierre' => $fechaCierreMySQL,
            'tiempo_atencion_minutos' => round($tiempoMinutos)
        ]);

        return ['success' => $resultado, 'fecha' => $fechaCierreMySQL];
    }

    /**
     * Guarda observaciones técnicas
     */
    public function guardarObservaciones(int $id, string $observaciones): bool
    {
        return $this->soporteModel->update($id, ['observaciones' => $observaciones]);
    }

    /**
     * Guarda firma del usuario
     */
    public function guardarFirma(int $id, string $firmaBase64): bool
    {
        return $this->soporteModel->update($id, ['firma_usuario' => $firmaBase64]);
    }

    /**
     * Elimina un ticket
     */
    public function eliminarTicket(int $id): bool
    {
        return $this->soporteModel->delete($id);
    }

    /**
     * Valida permisos de edición para técnicos/consultores
     */
    public function puedeEditar(int $ticketId, int $usuarioId, string $rol): bool
    {
        if ($rol === 'admin') {
            return true;
        }

        $soporte = $this->soporteModel->findById($ticketId);
        if (!$soporte) {
            return false;
        }

        return $soporte->usuario_creacion_id == $usuarioId;
    }

    /**
     * Valida que el equipo pertenezca al departamento del usuario
     */
    public function validarEquipoDepartamento(int $equipoId, ?int $departamentoId): bool
    {
        if (!$departamentoId) {
            return false;
        }

        $equipo = $this->equipoModel->findById($equipoId);
        return $equipo && $equipo->departamento_id == $departamentoId;
    }

    /**
     * Notifica a todos los técnicos sobre un nuevo ticket
     */
    private function notificarTecnicos(int $ticketId): void
    {
        $tecnicos = $this->usuarioModel->findAllTechnicians();
        
        foreach ($tecnicos as $tech) {
            $techId = $tech->usuario_id ?? $tech->id;
            if ($techId) {
                $this->notificacionModel->createNotification(
                    $techId,
                    "Nuevo ticket #{$ticketId} creado.",
                    "/soportes/ver/{$ticketId}"
                );
            }
        }
    }

    /**
     * Notifica a los administradores
     */
    public function notificarAdmins(string $mensaje, string $url): void
    {
        $admins = $this->usuarioModel->findAllAdmins();
        
        foreach ($admins as $admin) {
            $adminId = $admin->id ?? null;
            if ($adminId && $adminId != ($_SESSION['user_id'] ?? 0)) {
                $this->notificacionModel->createNotification($adminId, $mensaje, $url);
            }
        }
    }
}
