<?php
namespace App\Services;

use App\Models\Equipo;
use App\Models\Departamento;
use App\Models\Empleado;

class EquipoService
{
    private $equipoModel;
    private $departamentoModel;
    private $empleadoModel;

    public function __construct()
    {
        $this->equipoModel = new Equipo();
        $this->departamentoModel = new Departamento();
        $this->empleadoModel = new Empleado();
    }

    /**
     * Busca equipos por serial, código o cédula de empleado
     * Retorna array estructurado para respuesta JSON
     */
    public function buscarEquipos(string $query, ?int $departamentoIdRestrict = null): array
    {
        $query = trim($query);
        
        // Use the new partial search method
        $equipos = $this->equipoModel->searchByTerm($query);

        if (empty($equipos)) {
            return ['found' => false, 'error' => 'No se encontraron equipos con ese criterio.'];
        }

        // Filter by department restriction if active
        if ($departamentoIdRestrict) {
            $equipos = array_filter($equipos, function($eq) use ($departamentoIdRestrict) {
                return $eq->departamento_id == $departamentoIdRestrict;
            });
            // Re-index array
            $equipos = array_values($equipos);

            if (empty($equipos)) {
                return ['found' => false, 'error' => 'El equipo encontrado no pertenece a su departamento.'];
            }
        }

        // Process results
        // Case 1: Single Result -> Return detailed flat structure for direct assignment
        if (count($equipos) === 1) {
            $eq = $equipos[0];
            return [
                'found' => true,
                'multiple' => false,
                'id' => $eq->id,
                'tipo' => $eq->tipo,
                'marca' => $eq->marca,
                'modelo' => $eq->modelo,
                'serial' => $eq->numero_serie,
                'codigo' => $eq->codigo_inventario,
                'departamento_id' => $eq->departamento_id,
                'departamento_nombre' => $eq->departamento_nombre
            ];
        }

        // Case 2: Multiple Results -> Return list for selection modal
        $equiposData = array_map(function($eq) {
            return [
                'id' => $eq->id,
                'tipo' => $eq->tipo,
                'marca' => $eq->marca,
                'modelo' => $eq->modelo,
                'serial' => $eq->numero_serie,
                'codigo_inventario' => $eq->codigo_inventario,
                'departamento_id' => $eq->departamento_id,
                'departamento_nombre' => $eq->departamento_nombre
            ];
        }, $equipos);

        return [
            'found' => true,
            'multiple' => true,
            'equipos' => $equiposData
        ];
    }

    /**
     * Duplica un equipo existente
     */
    public function duplicarEquipo(int $id): ?array
    {
        $equipo = $this->equipoModel->findById($id);
        if (!$equipo) {
            return null;
        }

        $datos = [
            'codigo_inventario' => $equipo->codigo_inventario . '-COPIA',
            'numero_serie'      => $equipo->numero_serie . '-COPIA',
            'tipo'              => $equipo->tipo,
            'marca'             => $equipo->marca,
            'modelo'            => $equipo->modelo,
            'procesador'        => $equipo->procesador,
            'memoria_ram'       => $equipo->memoria_ram,
            'almacenamiento'    => $equipo->almacenamiento,
            'sistema_operativo' => $equipo->sistema_operativo,
            'direccion_ip'      => null,
            'driver'            => $equipo->driver,
            'toner'             => $equipo->toner,
            'departamento_id'   => $equipo->departamento_id,
            'empleado_id'       => null,
            'ubicacion_fisica'  => $equipo->ubicacion_fisica,
            'estado'            => 'disponible',
            'fecha_compra'      => $equipo->fecha_compra,
            'proveedor'         => $equipo->proveedor,
            'proveedor_rif'     => $equipo->proveedor_rif,
            'garantia'          => $equipo->garantia,
            'valor_compra'      => $equipo->valor_compra,
        ];

        try {
            $newId = $this->equipoModel->create($datos);
            if ($newId) {
                return ['id' => $newId, 'codigo' => $datos['codigo_inventario']];
            }
        } catch (\PDOException $e) {
            // Permitir que el controlador maneje la excepción o retornarla
            throw $e;
        }

        return null;
    }
}
