<?php
namespace App\Controllers;

use App\Core\Controller; 
use App\Core\Validator; 
use App\Models\Usuario; 
use PDOException; 

class UsuariosController extends Controller
{
    private $usuarioModel; 
    private $empleadoModel;
    private $departamentoModel;
    private $rolesPermitidos = ['admin', 'tecnico', 'consultor'];
    
    public function __construct() { 
        parent::__construct(); 
        $this->restrictTo(['admin']); 
        $this->usuarioModel = new Usuario(); 
        $this->empleadoModel = new \App\Models\Empleado();
        $this->departamentoModel = new \App\Models\Departamento();
    }
    
    public function index() { 
        $usuarios = $this->usuarioModel->findAll(); 
        $this->render('usuarios/lista', [ 'titulo' => 'Gestión de Usuarios', 'usuarios' => $usuarios ]); 
    }
    
    public function crear() { 
        $this->render('usuarios/formulario', [ 
            'titulo' => 'Crear Nuevo Usuario', 
            'usuario' => null, 
            'allowedRoles' => $this->rolesPermitidos,
            'empleados' => $this->empleadoModel->findAll(),
            'departamentos' => $this->departamentoModel->findAll()
        ]); 
    }
    
    public function editar(int $id) { 
        $usuario = $this->usuarioModel->findById($id); 
        if (!$usuario) { 
            $this->setFlashMessage('error', 'Usuario no encontrado.'); 
            header('Location: ' . BASE_URL . 'usuarios'); 
            exit; 
        } 
        $this->render('usuarios/formulario', [ 
            'titulo' => "Editar Usuario: {$usuario->username}", 
            'usuario' => $usuario, 
            'allowedRoles' => $this->rolesPermitidos,
            'empleados' => $this->empleadoModel->findAll(),
            'departamentos' => $this->departamentoModel->findAll()
        ]); 
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }
        
        $validator = new Validator($_POST);
        $id = $validator->getInt('id'); 
        $es_edicion = !empty($id);

        $validator->check('username', 'required');
        $validator->check('rol', 'required');
        $validator->check('rol', 'inList', 'Rol no válido.', $this->rolesPermitidos);
        $password = $validator->get('password');
        if (!$es_edicion && empty($password)) { 
            $validator->check('password', 'required', 'La contraseña es obligatoria al crear.'); 
        }
        if (!empty($password)) { 
            $validator->check('password', 'minLength', 'Pass debe tener min 6 chars.', 6);
        }
        
        if ($validator->fails()) { 
            $this->setFlashMessage('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $datos = [ 
            'username' => $validator->get('username'), 
            'rol' => $validator->get('rol'),
            'empleado_id' => $validator->getInt('empleado_id') ?: null,
            'departamento_id' => $validator->getInt('departamento_id') ?: null
        ];
        
        if (!empty($password)) { $datos['password'] = password_hash($password, PASSWORD_DEFAULT); }

        try {
            if ($es_edicion) {
                if ($this->usuarioModel->update($id, $datos)) {
                    $this->setFlashMessage('success', "Usuario #{$id} actualizado.");
                    // --- LOG ACTUALIZADO ---
                    $this->logBitacora("Actualizó al usuario {$datos['username']}", 'usuario', $id);
                }
            } else {
                if ($newId = $this->usuarioModel->create($datos)) {
                    $this->setFlashMessage('success', 'Usuario creado exitosamente.');
                    // --- LOG ACTUALIZADO ---
                    $this->logBitacora("Creó al usuario {$datos['username']}", 'usuario', $newId);
                }
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { 
                $this->setFlashMessage('error', 'Error: El username "' . $datos['username'] . '" ya está en uso.'); 
            }
            else { 
                $this->setFlashMessage('error', 'Error de BD: ' . $e->getMessage()); 
            }
            header('Location: ' . $_SERVER['HTTP_REFERER']); 
            exit;
        }
        header("Location: " . BASE_URL . "usuarios"); 
        exit;
    }

    public function eliminar(int $id)
    {
        if ($id == 1) { 
            $this->setFlashMessage('error', 'No se puede eliminar al administrador principal.');
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }
        $usuario = $this->usuarioModel->findById($id); 
        if (!$usuario) { 
            $this->setFlashMessage('error', 'Usuario no encontrado.'); 
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }

        if ($this->usuarioModel->delete($id)) {
            $this->setFlashMessage('success', "Usuario #{$id} eliminado.");
            // --- LOG ACTUALIZADO ---
            $this->logBitacora("Eliminó al usuario {$usuario->username}", 'usuario', $id);
        } else {
            $this->setFlashMessage('error', "Error al eliminar.");
        }
        header('Location: ' . BASE_URL . 'usuarios'); 
        exit;
    }
}