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
     */
    public function registrarItem(array $datos): bool
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO {$this->table} (codigo, nombre, categoria, descripcion, marca, modelo, unidad_medida, stock_actual, stock_minimo, ubicacion, fecha_compra, proveedor, proveedor_rif, garantia_fin, valor_compra) 
                    VALUES (:codigo, :nombre, :categoria, :descripcion, :marca, :modelo, :unidad_medida, :stock_actual, :stock_minimo, :ubicacion, :fecha_compra, :proveedor, :proveedor_rif, :garantia_fin, :valor_compra)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
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

            $itemId = $this->pdo->lastInsertId();

            // Si hay stock inicial, registrarlo en Almacén Central (departamento_id = NULL)
            if ($datos['stock_actual'] > 0) {
                $this->ajustarStockUbicacion($itemId, null, $datos['stock_actual']);
                
                // Opcional: Registrar movimiento inicial
                $sqlMov = "INSERT INTO inventario_movimientos (item_id, usuario_id, tipo_movimiento, cantidad, motivo)
                           VALUES (:item_id, :usuario_id, 'ENTRADA', :cantidad, 'Stock Inicial')";
                $stmtMov = $this->pdo->prepare($sqlMov);
                $stmtMov->execute([
                    'item_id' => $itemId,
                    'usuario_id' => $_SESSION['user_id'] ?? 1, // Fallback a admin si no hay sesión (aunque debería haber)
                    'cantidad' => $datos['stock_actual']
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            // Log error if needed
            return false;
        }
    }

    /**
     * Actualiza stock en el almacén central y registra movimiento.
     */
    public function actualizarStock(int $id, int $cantidad, string $tipo, int $usuario_id, string $motivo): bool
    {
        try {
            $this->pdo->beginTransaction();
            $cantidadReal = ($tipo === 'ENTRADA') ? $cantidad : -$cantidad;
            // Ajustar stock en la ubicación central (departamento_id NULL)
            $this->ajustarStockUbicacion($id, null, $cantidadReal);
            // Registrar movimiento
            $sqlMov = "INSERT INTO inventario_movimientos (item_id, usuario_id, tipo_movimiento, cantidad, motivo)
                       VALUES (:item_id, :usuario_id, :tipo, :cantidad, :motivo)";
            $stmtMov = $this->pdo->prepare($sqlMov);
            $stmtMov->execute([
                'item_id'   => $id,
                'usuario_id'=> $usuario_id,
                'tipo'      => $tipo,
                'cantidad'  => $cantidad,
                'motivo'    => $motivo,
            ]);
            // Actualizar stock global en la tabla de items
            $operador = ($tipo === 'ENTRADA') ? '+' : '-';
            $sqlUpdate = "UPDATE {$this->table} SET stock_actual = stock_actual $operador :cant WHERE id = :id";
            $stmtUpd = $this->pdo->prepare($sqlUpdate);
            $stmtUpd->execute(['cant' => $cantidad, 'id' => $id]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
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
     * Transfiere stock entre dos ubicaciones (puede ser central <-> dept).
     */
    public function transferirStock(int $itemId, ?int $origenDeptId, ?int $destinoDeptId, int $cantidad, int $usuarioId, string $motivo): bool
    {
        try {
            $this->pdo->beginTransaction();
            // Verificar stock disponible en origen
            $stockOrigen = $this->obtenerStockUbicacion($itemId, $origenDeptId);
            if ($stockOrigen < $cantidad) {
                throw new Exception('Stock insuficiente en la ubicación de origen');
            }
            // Restar del origen y sumar al destino
            $this->ajustarStockUbicacion($itemId, $origenDeptId, -$cantidad);
            $this->ajustarStockUbicacion($itemId, $destinoDeptId, $cantidad);
            // Registrar movimiento
            $sqlMov = "INSERT INTO inventario_movimientos
                       (item_id, usuario_id, tipo_movimiento, cantidad, motivo, origen_departamento_id, destino_departamento_id)
                       VALUES (:item_id, :usuario_id, 'TRANSFERENCIA', :cantidad, :motivo, :origen, :destino)";
            $stmtMov = $this->pdo->prepare($sqlMov);
            $stmtMov->execute([
                'item_id'   => $itemId,
                'usuario_id'=> $usuarioId,
                'cantidad'  => $cantidad,
                'motivo'    => $motivo,
                'origen'    => $origenDeptId,
                'destino'   => $destinoDeptId,
            ]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Registra el consumo de un ítem dentro de un ticket de soporte.
     */
    public function consumirEnTicket(int $itemId, int $soporteId, int $cantidad, int $usuarioId, int $departamentoId): bool
    {
        try {
            $this->pdo->beginTransaction();
            // Verificar stock en el departamento del técnico
            $stock = $this->obtenerStockUbicacion($itemId, $departamentoId);
            if ($stock < $cantidad) {
                throw new Exception('Stock insuficiente en el departamento');
            }
            // Restar del stock del departamento
            $this->ajustarStockUbicacion($itemId, $departamentoId, -$cantidad);
            // Registrar consumo vinculado al ticket (si la tabla existe)
            $sqlConsumo = "INSERT INTO inventario_consumos (soporte_id, item_id, cantidad, usuario_id, fecha)
                           VALUES (:soporte_id, :item_id, :cantidad, :usuario_id, NOW())";
            $stmtCons = $this->pdo->prepare($sqlConsumo);
            $stmtCons->execute([
                'soporte_id'=> $soporteId,
                'item_id'   => $itemId,
                'cantidad'  => $cantidad,
                'usuario_id'=> $usuarioId,
            ]);
            // Registrar movimiento general
            $sqlMov = "INSERT INTO inventario_movimientos
                       (item_id, usuario_id, tipo_movimiento, cantidad, motivo, origen_departamento_id, referencia_id)
                       VALUES (:item_id, :usuario_id, 'CONSUMO', :cantidad, :motivo, :origen, :ref)";
            $stmtMov = $this->pdo->prepare($sqlMov);
            $stmtMov->execute([
                'item_id'   => $itemId,
                'usuario_id'=> $usuarioId,
                'cantidad'  => $cantidad,
                'motivo'    => "Consumo en Ticket #{$soporteId}",
                'origen'    => $departamentoId,
                'ref'       => $soporteId,
            ]);
            // Actualizar stock global (opcional)
            $sqlUpdGlobal = "UPDATE inventario_items SET stock_actual = stock_actual - :cant WHERE id = :id";
            $stmtUpd = $this->pdo->prepare($sqlUpdGlobal);
            $stmtUpd->execute(['cant' => $cantidad, 'id' => $itemId]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Ajusta (incrementa o decrementa) el stock en una ubicación.
     */
    private function ajustarStockUbicacion(int $itemId, ?int $departamentoId, int $cantidad): void
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
    /**
     * Obtiene el historial de movimientos de un ítem específico.
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
     * Registra una baja de inventario (daño, pérdida, etc).
     * Resta del stock central (o especificado) y guarda registro en bajas_inventario.
     */
    public function registrarBaja(int $itemId, int $cantidad, string $motivo, int $usuarioId): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Verificar stock actual (asumimos que se da de baja del stock general/central por defecto, 
            // o podríamos implementar lógica para seleccionar ubicación. Por simplicidad y según requerimiento,
            // restaremos del stock disponible donde haya).
            // Para simplificar "Almacén Virtual", vamos a restar del stock total, priorizando Almacén Central.
            
            $stockCentral = $this->obtenerStockUbicacion($itemId, null);
            
            if ($stockCentral >= $cantidad) {
                $this->ajustarStockUbicacion($itemId, null, -$cantidad);
            } else {
                // Si no hay suficiente en central, buscar en otras ubicaciones? 
                // Por ahora, lanzamos error si no hay en central, para obligar a transferir primero o simplificar.
                // O simplemente permitimos restar y que quede negativo? NO.
                throw new Exception("No hay suficiente stock en Almacén Central para dar de baja. Transfiera primero o ajuste.");
            }

            // 2. Registrar en tabla bajas_inventario
            $sqlBaja = "INSERT INTO bajas_inventario (inventario_id, cantidad, motivo, usuario_id) 
                        VALUES (:item_id, :cantidad, :motivo, :usuario_id)";
            $stmtBaja = $this->pdo->prepare($sqlBaja);
            $stmtBaja->execute([
                'item_id' => $itemId,
                'cantidad' => $cantidad,
                'motivo' => $motivo,
                'usuario_id' => $usuarioId
            ]);

            // 3. Registrar movimiento para historial
            $sqlMov = "INSERT INTO inventario_movimientos (item_id, usuario_id, tipo_movimiento, cantidad, motivo)
                       VALUES (:item_id, :usuario_id, 'BAJA', :cantidad, :motivo)";
            $stmtMov = $this->pdo->prepare($sqlMov);
            $stmtMov->execute([
                'item_id' => $itemId,
                'usuario_id' => $usuarioId,
                'cantidad' => $cantidad,
                'motivo' => "Baja: " . $motivo
            ]);

            // 4. Actualizar stock global
            $sqlUpdate = "UPDATE {$this->table} SET stock_actual = stock_actual - :cant WHERE id = :id";
            $stmtUpd = $this->pdo->prepare($sqlUpdate);
            $stmtUpd->execute(['cant' => $cantidad, 'id' => $itemId]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    /**
     * Elimina un ítem del inventario y sus registros relacionados.
     */
    public function eliminarItem(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();

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
            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
?>
