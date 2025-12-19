<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;
    private $dsn;

    private function __construct() {
        // Incluir las constantes de configuración
        require_once __DIR__ . '/../../config/database.php';
        
        $this->dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($this->dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Loguear error internamente si es necesario, pero NO exponer en public
            error_log("Database Connection Error: " . $e->getMessage());
            throw new PDOException("Error de conexión a la base de datos.");
        }
    }

    // El método Singleton para obtener la instancia
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Método para obtener la conexión PDO
    public function getConnection() {
        return $this->pdo;
    }
}