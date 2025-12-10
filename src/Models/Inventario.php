<?php
namespace App\Models;

use PDO;
use App\Core\Model;
use Exception;



class Inventario extends Model {
    protected $table = 'inventario_items';

    /**
     * Obtiene todos los ítems del inventario.
     * @return array
     */
    /**
     * Obtiene todos los ítems del inventario.
     * @return array
     */
    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function obtenerTodosPaginated(int $limit, int $offset, string $search = ''): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE codigo LIKE :search1 OR nombre LIKE :search2 OR marca LIKE :search3 OR modelo LIKE :search4 OR categoria LIKE :search5";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
            $params[':search4'] = "%$search%";
            $params[':search5'] = "%$search%";
        }
        
        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function countAll(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE codigo LIKE :search1 OR nombre LIKE :search2 OR marca LIKE :search3 OR modelo LIKE :search4 OR categoria LIKE :search5";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
            $params[':search4'] = "%$search%";
            $params[':search5'] = "%$search%";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? (int)$result->total : 0;
    }

    /**
     * Registra un nuevo ítem en el inventario.
     * @return int ID del item creado, o 0 si falló
     */
    /**
     * Registra un nuevo ítem en el inventario.
     * @return int ID del item creado, o 0 si falló
     */
    public function registrarItem(array $datos): int
    {
        $sql = "INSERT INTO {$this->table} (codigo, nombre, categoria, descripcion, marca, modelo, unidad_medida, stock_actual, stock_minimo, ubicacion, fecha_compra, proveedor, proveedor_rif, garantia_fin, valor_compra) 
                VALUES (:codigo, :nombre, :categoria, :descripcion, :marca, :modelo, :unidad_medida, :stock_actual, :stock_minimo, :ubicacion, :fecha_compra, :proveedor, :proveedor_rif, :garantia_fin, :valor_compra)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'categoria' => $datos['categoria'],
            'descripcion' => $datos['descripcion'],
            'marca' => $datos['marca'],
            'modelo' => $datos['modelo'],
            'unidad_medida' => $datos['unidad_medida'],
            'stock_actual' => $datos['stock_actual'],
            'stock_minimo' => $datos['stock_minimo'],
            'ubicacion' => $datos['ubicacion'],
            'fecha_compra' => $datos['fecha_compra'],
            'proveedor' => $datos['proveedor'],
            'proveedor_rif' => $datos['proveedor_rif'],
            'garantia_fin' => $datos['garantia_fin'],
            'valor_compra' => $datos['valor_compra']
        ]);

        return $result ? (int)$this->pdo->lastInsertId() : 0;
    }

    /**
     * Actualiza un ítem existente del inventario.
     */
    public function actualizarItem(array $datos): bool
    {
        $sql = "UPDATE {$this->table} SET 
                    codigo = :codigo, 
                    nombre = :nombre, 
                    categoria = :categoria, 
                    descripcion = :descripcion, 
                    marca = :marca, 
                    modelo = :modelo, 
                    unidad_medida = :unidad_medida, 
                    stock_minimo = :stock_minimo, 
                    ubicacion = :ubicacion, 
                    proveedor = :proveedor, 
                    proveedor_rif = :proveedor_rif, 
                    valor_compra = :valor_compra 
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $datos['id'],
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'categoria' => $datos['categoria'],
            'descripcion' => $datos['descripcion'],
            'marca' => $datos['marca'],
            'modelo' => $datos['modelo'],
            'unidad_medida' => $datos['unidad_medida'],
            'stock_minimo' => $datos['stock_minimo'],
            'ubicacion' => $datos['ubicacion'],
            'proveedor' => $datos['proveedor'],
            'proveedor_rif' => $datos['proveedor_rif'],
            'valor_compra' => $datos['valor_compra'],
        ]);
    }



    /**
     * Obtiene la cantidad disponible de un ítem en una ubicación específica.
     * Si $departamentoId es null, se refiere al almacén central.
     */
    public function obtenerStockUbicacion(int $itemId, ?int $departamentoId = null): int
    {
        $sql = "SELECT cantidad FROM inventario_ubicaciones WHERE item_id = :item_id AND " .
               ($departamentoId === null ? "departamento_id IS NULL" : "departamento_id = :dept_id");
        $params = ['item_id' => $itemId];
        if ($departamentoId !== null) {
            $params['dept_id'] = $departamentoId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Devuelve el detalle de stock por ubicación para un ítem.
     */
    public function obtenerStockDetallado(int $itemId): array
    {
        $sql = "SELECT u.cantidad, d.nombre AS departamento
                FROM inventario_ubicaciones u
                LEFT JOIN departamentos d ON u.departamento_id = d.id
                WHERE u.item_id = :item_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['item_id' => $itemId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Lista todos los ítems que tienen stock disponible en un departamento.
     */
    public function obtenerItemsPorDepartamento(int $departamentoId): array
    {
        $sql = "SELECT i.*, u.cantidad AS stock_departamento
                FROM inventario_items i
                JOIN inventario_ubicaciones u ON i.id = u.item_id
                WHERE u.departamento_id = :dept_id AND u.cantidad > 0
                ORDER BY i.nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['dept_id' => $departamentoId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }





    /**
     * Ajusta (incrementa o decrementa) el stock en una ubicación.
     */
    /**
     * Ajusta (incrementa o decrementa) el stock en una ubicación.
     */
    public function ajustarStockUbicacion(int $itemId, ?int $departamentoId, int $cantidad): void
    {
        // Verificar si ya existe registro para esa ubicación
        $sqlCheck = "SELECT id FROM inventario_ubicaciones WHERE item_id = :item_id AND " .
                    ($departamentoId === null ? "departamento_id IS NULL" : "departamento_id = :dept_id");
        $params = ['item_id' => $itemId];
        if ($departamentoId !== null) {
            $params['dept_id'] = $departamentoId;
        }
        $stmt = $this->pdo->prepare($sqlCheck);
        $stmt->execute($params);
        $exists = $stmt->fetch(PDO::FETCH_OBJ);
        if ($exists) {
            // Actualizar registro existente
            $sqlUpd = "UPDATE inventario_ubicaciones SET cantidad = cantidad + :cant WHERE id = :id";
            $stmtUpd = $this->pdo->prepare($sqlUpd);
            $stmtUpd->execute(['cant' => $cantidad, 'id' => $exists->id]);
        } else {
            // Si es una resta y no existe registro, lanzar error
            if ($cantidad < 0) {
                throw new Exception('Intento de restar stock inexistente');
            }
            // Insertar nuevo registro
            $sqlIns = "INSERT INTO inventario_ubicaciones (item_id, departamento_id, cantidad)
                       VALUES (:item_id, :dept_id, :cant)";
            $stmtIns = $this->pdo->prepare($sqlIns);
            $stmtIns->execute([
                'item_id' => $itemId,
                'dept_id' => $departamentoId,
                'cant'    => $cantidad,
            ]);
        }
    }

    public function registrarMovimiento(int $itemId, int $usuarioId, string $tipo, int $cantidad, string $motivo, ?int $origenId = null, ?int $destinoId = null, ?int $refId = null): bool
    {
        $sqlMov = "INSERT INTO inventario_movimientos
                   (item_id, usuario_id, tipo_movimiento, cantidad, motivo, origen_departamento_id, destino_departamento_id, referencia_id)
                   VALUES (:item_id, :usuario_id, :tipo, :cantidad, :motivo, :origen, :destino, :ref)";
        $stmtMov = $this->pdo->prepare($sqlMov);
        return $stmtMov->execute([
            'item_id'   => $itemId,
            'usuario_id'=> $usuarioId,
            'tipo'      => $tipo,
            'cantidad'  => $cantidad,
            'motivo'    => $motivo,
            'origen'    => $origenId,
            'destino'   => $destinoId,
            'ref'       => $refId
        ]);
    }
    /**
     * Obtiene el historial de movimientos de un ítem específico.
     */
    /**
     * Obtiene el historial de movimientos de un ítem específico con paginación.
     */
    public function obtenerMovimientosPaginated(int $itemId, int $limit, int $offset): array
    {
        $sql = "SELECT m.*, u.username 
                FROM inventario_movimientos m
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                WHERE m.item_id = :item_id
                ORDER BY m.fecha DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Cuenta el total de movimientos de un ítem.
     */
    public function countMovimientos(int $itemId): int
    {
        $sql = "SELECT COUNT(*) as total FROM inventario_movimientos WHERE item_id = :item_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['item_id' => $itemId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? (int)$result->total : 0;
    }

    /**
     * Obtiene el historial de movimientos de un ítem específico (Legacy).
     */
    public function obtenerMovimientos(int $itemId): array
    {
        $sql = "SELECT m.*, u.username 
                FROM inventario_movimientos m
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                WHERE m.item_id = :item_id
                ORDER BY m.fecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['item_id' => $itemId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene el historial global de movimientos de inventario.
     */
    public function obtenerTodosMovimientos(int $limit = 100): array
    {
        $sql = "SELECT m.*, i.nombre as item_nombre, i.codigo as item_codigo, u.username 
                FROM inventario_movimientos m
                JOIN inventario_items i ON m.item_id = i.id
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                ORDER BY m.fecha DESC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene el historial de movimientos de un departamento específico.
     * Incluye transferencias entrantes, salientes y consumos.
     */
    public function obtenerMovimientosPorDepartamento(int $departamentoId, int $limit = 20): array
    {
        $sql = "SELECT m.*, i.nombre as item_nombre, i.codigo as item_codigo, u.username,
                       d_origen.nombre as origen_nombre, d_destino.nombre as destino_nombre
                FROM inventario_movimientos m
                JOIN inventario_items i ON m.item_id = i.id
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                LEFT JOIN departamentos d_origen ON m.origen_departamento_id = d_origen.id
                LEFT JOIN departamentos d_destino ON m.destino_departamento_id = d_destino.id
                WHERE m.origen_departamento_id = :dept_id 
                   OR m.destino_departamento_id = :dept_id2
                ORDER BY m.fecha DESC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':dept_id', $departamentoId, PDO::PARAM_INT);
        $stmt->bindValue(':dept_id2', $departamentoId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


    public function registrarBajaRecord(int $itemId, int $cantidad, string $motivo, int $usuarioId): bool
    {
        $sqlBaja = "INSERT INTO bajas_inventario (inventario_id, cantidad, motivo, usuario_id) 
                    VALUES (:item_id, :cantidad, :motivo, :usuario_id)";
        $stmtBaja = $this->pdo->prepare($sqlBaja);
        return $stmtBaja->execute([
            'item_id' => $itemId,
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId
        ]);
    }

    /**
     * Elimina un ítem del inventario y sus registros relacionados.
     */
    /**
     * Elimina un ítem del inventario y sus registros relacionados.
     */
    public function eliminarItem(int $id): bool
    {
        // Eliminar ubicaciones
        $stmt = $this->pdo->prepare("DELETE FROM inventario_ubicaciones WHERE item_id = :id");
        $stmt->execute(['id' => $id]);

        // Eliminar movimientos
        $stmt = $this->pdo->prepare("DELETE FROM inventario_movimientos WHERE item_id = :id");
        $stmt->execute(['id' => $id]);
        
        // Eliminar bajas
        $stmt = $this->pdo->prepare("DELETE FROM bajas_inventario WHERE inventario_id = :id");
        $stmt->execute(['id' => $id]);

        // Eliminar ítem
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
?>
