<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\BitacoraModel;

class BitacoraController extends Controller
{
    private $bitacoraModel;

    public function __construct()
    {
        parent::__construct();
        // Admin, Consultor y Tecnico pueden ver la bitácora (filtrada para no-admins)
        $this->restrictTo(['admin', 'consultor', 'tecnico']);
        $this->bitacoraModel = new BitacoraModel();
    }

    /**
     * Muestra la lista de acciones de la bitácora con paginación y selector de items por página.
     */
    public function index()
    {
        // Parámetros de paginación
        $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 20; // items por página
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($currentPage - 1) * $perPage;

        if ($_SESSION['rol'] === 'consultor' || $_SESSION['rol'] === 'tecnico') {
            $deptId = $_SESSION['departamento_id'] ?? 0;
            $acciones = $this->bitacoraModel->findByDepartmentId($deptId, $perPage, $offset);
            $totalRecords = $this->bitacoraModel->countByDepartmentId($deptId);
        } else {
            // Admin ve todo
            $acciones = $this->bitacoraModel->findPaginated($perPage, $offset);
            $totalRecords = $this->bitacoraModel->countAll();
        }
        
        $totalPages = (int)ceil($totalRecords / $perPage);

        $this->render('bitacora/lista', [
            'titulo'       => 'Bitácora de Acciones',
            'acciones'     => $acciones,
            'currentPage'  => $currentPage,
            'totalPages'   => $totalPages,
            'totalRecords' => $totalRecords,
            'perPage'      => $perPage,
        ]);
    }
}