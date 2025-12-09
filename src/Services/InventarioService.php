<?php
namespace App\Services;

use App\Models\Inventario;
use App\Models\Bitacora;
use App\Core\Database;
use Exception;
use PDO;

class InventarioService
{
    private $inventarioModel;
    private $bitacoraModel;
    private $pdo;

    public function __construct()
    {
        $this->inventarioModel = new Inventario();
        $this->bitacoraModel = new Bitacora();
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Registra un nuevo ítem en el inventario con transacción
     */
    public function registrarItem(array $datos, ?int $usuarioId = null): int
    {
        try {
            $this->pdo->beginTransaction();

            $datos['ubicacion'] = $datos['ubicacion'] ?? 'Almacén Central';
            $datos['stock_actual'] = $datos['stock_actual'] ?? 0;
            $datos['stock_minimo'] = $datos['stock_minimo'] ?? 0;
            
            // Auto-generate SKU code if empty
            if (empty($datos['codigo'])) {
                $datos['codigo'] = $this->generarCodigoSKU($datos['categoria'] ?? 'GEN');
            }
            
            // 1. Crear el ítem
            $itemId = $this->inventarioModel->registrarItem($datos); // Refactored to be simple insert
            
            if ($itemId <= 0) {
                throw new Exception("Error al insertar el ítem en la base de datos.");
            }

            // 2. Si hay stock inicial, registrar entrada
            if ($datos['stock_actual'] > 0) {
                // Ajustar stock en ubicación NULL (Almacén Central)
                $this->inventarioModel->ajustarStockUbicacion($itemId, null, $datos['stock_actual']);
                
                // Registrar movimiento inicial
                $this->inventarioModel->registrarMovimiento(
                    $itemId, 
                    $usuarioId ?? 1, 
                    'ENTRADA', 
                    $datos['stock_actual'], 
                    'Stock Inicial'
                );
            }
            
            // 3. Bitácora
            if ($usuarioId) {
                $this->bitacoraModel->createLog(
                    $usuarioId,
                    $_SESSION['username'] ?? 'Sistema',
                    "Registró nuevo ítem '{$datos['nombre']}'",
                    'inventario',
                    $itemId
                );
            }

            $this->pdo->commit();
            return $itemId;

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Actualiza un ítem existente
     */
    public function actualizarItem(int $id, array $datos, ?int $usuarioId = null): void
    {
        // Simple update, no transaction strictly needed unless extending logic
        $this->inventarioModel->actualizarItem($datos);
        
        if ($usuarioId) {
             $this->bitacoraModel->createLog(
                $usuarioId,
                $_SESSION['username'] ?? 'Sistema',
                "Actualizó el artículo #{$id} ({$datos['nombre']})",
                'inventario',
                $id
            );
        }
    }

    /**
     * Registra un movimiento de stock (entrada/salida simple)
     */
    public function registrarMovimiento(int $itemId, int $cantidad, string $tipo, string $motivo, int $usuarioId): void
    {
        try {
            $this->pdo->beginTransaction();

            $cantidadReal = ($tipo === 'ENTRADA') ? $cantidad : -$cantidad;
            
            // 1. Ajustar stock en Almacén Central (por defecto para movimientos simples)
            // Si el stock baja de 0 se lanza excepción desde el modelo
            $this->inventarioModel->ajustarStockUbicacion($itemId, null, $cantidadReal);
            
            // 2. Registrar movimiento
            $this->inventarioModel->registrarMovimiento(
                $itemId, 
                $usuarioId, 
                $tipo, 
                $cantidad, 
                $motivo,
                null, // Origen
                null  // Destino
            );
            
            // 3. Actualizar stock global del ítem (denormalized column)
            // Ideally we should recalculate or just update. For now, simple update.
            $operador = ($tipo === 'ENTRADA') ? '+' : '-';
            $sqlUpdate = "UPDATE inventario_items SET stock_actual = stock_actual $operador ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sqlUpdate);
            $stmt->execute([$cantidad, $itemId]);

            $this->bitacoraModel->createLog(
                $usuarioId,
                $_SESSION['username'] ?? 'Sistema',
                "Registró movimiento {$tipo} de {$cantidad} unidades para ítem #{$itemId}",
                'inventario',
                $itemId
            );

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Transfiere stock entre ubicaciones
     */
    public function transferirStock(int $itemId, ?int $origenId, ?int $destinoId, int $cantidad, int $usuarioId, string $motivo): void
    {
        if ($origenId == $destinoId) {
            throw new Exception("El origen y el destino no pueden ser iguales.");
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Verificar stock origen
            $stockOrigen = $this->inventarioModel->obtenerStockUbicacion($itemId, $origenId);
            if ($stockOrigen < $cantidad) {
                throw new Exception("Stock insuficiente en la ubicación de origen.");
            }

            // 2. Ajustar stocks
            $this->inventarioModel->ajustarStockUbicacion($itemId, $origenId, -$cantidad);
            $this->inventarioModel->ajustarStockUbicacion($itemId, $destinoId, $cantidad);

            // 3. Registrar movimiento
            $this->inventarioModel->registrarMovimiento(
                $itemId, 
                $usuarioId, 
                'TRANSFERENCIA', 
                $cantidad, 
                $motivo,
                $origenId,
                $destinoId
            );

            $this->bitacoraModel->createLog(
                $usuarioId,
                $_SESSION['username'] ?? 'Sistema',
                "Transfirió {$cantidad} unidades del ítem #{$itemId}",
                'inventario',
                $itemId
            );

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Registra una baja de inventario
     */
    public function registrarBaja(int $itemId, int $cantidad, string $motivo, int $usuarioId, bool $eliminarCompleto = false): void
    {
        try {
            $this->pdo->beginTransaction();

            $item = $this->inventarioModel->findById($itemId);
            if (!$item) {
                throw new Exception("Ítem no encontrado.");
            }

            if ($eliminarCompleto) {
                if ($this->inventarioModel->eliminarItem($itemId)) {
                    $this->bitacoraModel->createLog(
                        $usuarioId,
                        $_SESSION['username'] ?? 'Sistema',
                        "Eliminó el artículo #{$itemId} del inventario",
                        'inventario',
                        $itemId
                    );
                } else {
                    throw new Exception("No se pudo eliminar el artículo.");
                }
            } else {
                if ($cantidad <= 0) {
                    throw new Exception("La cantidad debe ser mayor a 0.");
                }

                // 1. Verificar stock en Almacén Central (o general)
                // Asumimos baja del central por defecto según lógica anterior
                $stockCentral = $this->inventarioModel->obtenerStockUbicacion($itemId, null);
                if ($stockCentral < $cantidad) {
                    throw new Exception("No hay suficiente stock en Almacén Central para dar de baja.");
                }

                // 2. Restar stock
                $this->inventarioModel->ajustarStockUbicacion($itemId, null, -$cantidad);

                // 3. Registrar baja en tabla específica
                $this->inventarioModel->registrarBajaRecord($itemId, $cantidad, $motivo, $usuarioId);

                // 4. Registrar movimiento
                $this->inventarioModel->registrarMovimiento(
                    $itemId, 
                    $usuarioId, 
                    'BAJA', 
                    $cantidad, 
                    "Baja: " . $motivo
                );

                // 5. Actualizar stock global
                $sqlUpdate = "UPDATE inventario_items SET stock_actual = stock_actual - ? WHERE id = ?";
                $stmt = $this->pdo->prepare($sqlUpdate);
                $stmt->execute([$cantidad, $itemId]);

                $this->bitacoraModel->createLog(
                    $usuarioId,
                    $_SESSION['username'] ?? 'Sistema',
                    "Registró baja de {$cantidad} unidades del ítem #{$itemId}",
                    'inventario',
                    $itemId
                );
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Registra el consumo de un ítem dentro de un ticket de soporte.
     */
    public function consumirEnTicket(int $itemId, int $soporteId, int $cantidad, int $usuarioId, int $departamentoId): void
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Verificar stock en el departamento del técnico
            // Si el departamento es null? Asumimos que siempre hay deptoId en este flujo
            $stock = $this->inventarioModel->obtenerStockUbicacion($itemId, $departamentoId);
            if ($stock < $cantidad) {
                throw new Exception('Stock insuficiente en tu departamento para realizar este consumo.');
            }

            // 2. Restar del stock del departamento
            $this->inventarioModel->ajustarStockUbicacion($itemId, $departamentoId, -$cantidad);

            // 3. Registrar consumo vinculado al ticket
            $this->inventarioModel->registrarConsumoRecord($soporteId, $itemId, $cantidad, $usuarioId);

            // 4. Registrar movimiento general
            $this->inventarioModel->registrarMovimiento(
                $itemId, 
                $usuarioId, 
                'CONSUMO', 
                $cantidad, 
                "Consumo en Ticket #{$soporteId}",
                $departamentoId, // Origen
                null, // Destino
                $soporteId // Referencia
            );

            // 5. Actualizar stock global (denormalized)
            $sqlUpdGlobal = "UPDATE inventario_items SET stock_actual = stock_actual - ? WHERE id = ?";
            $stmtUpd = $this->pdo->prepare($sqlUpdGlobal);
            $stmtUpd->execute([$cantidad, $itemId]);

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Genera un código SKU automático basado en la categoría
     * Formato: CAT-XXXXXX (prefijo de categoría + 6 dígitos únicos)
     */
    private function generarCodigoSKU(string $categoria): string
    {
        // Mapeo de categorías a prefijos
        $prefijos = [
            'Hardware' => 'HW',
            'Software' => 'SW',
            'Periféricos' => 'PF',
            'Cables' => 'CB',
            'Consumibles' => 'CS',
            'Herramientas' => 'HT',
            'Otros' => 'OT',
            'GEN' => 'GN'
        ];
        
        $prefijo = $prefijos[$categoria] ?? 'GN';
        
        // Generar número único: timestamp + random
        $unique = substr(time(), -4) . str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT);
        
        $codigo = $prefijo . '-' . $unique;
        
        // Verificar que no exista (aunque es muy improbable que repita)
        $sql = "SELECT COUNT(*) FROM inventario_items WHERE codigo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$codigo]);
        
        if ($stmt->fetchColumn() > 0) {
            // Si existe, agregar un dígito random extra
            $codigo = $prefijo . '-' . $unique . mt_rand(0, 9);
        }
        
        return $codigo;
    }

}
