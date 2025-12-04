<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Usuario;

class PerfilController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct(); // ¡Importante! Asegura que el usuario esté logueado
        $this->usuarioModel = new Usuario();
    }

    /**
     * Muestra el formulario de perfil (cambio de contraseña).
     */
    public function index()
    {
        $this->render('perfil/index', [
            'titulo' => 'Mi Perfil'
        ]);
    }

    /**
     * Procesa la actualización de la contraseña del usuario.
     */
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'perfil');
            exit;
        }

        // 1. Validar los datos de entrada
        $validator = new Validator($_POST);
        $validator->check('password_actual', 'required', 'La contraseña actual es obligatoria.');
        $validator->check('password_nuevo', 'required', 'La nueva contraseña es obligatoria.');
        $validator->check('password_nuevo', 'minLength', 'La nueva contraseña debe tener al menos 6 caracteres.', 6);
        $validator->check('password_confirmar', 'required', 'Debe confirmar la nueva contraseña.');

        $password_nuevo = $validator->get('password_nuevo');
        $password_confirmar = $validator->get('password_confirmar');

        if ($password_nuevo !== $password_confirmar) {
             $validator->check('password_confirmar', 'match', 'Las contraseñas no coinciden.');
        }

        if ($validator->fails()) {
            $this->setFlashMessage('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            header('Location: ' . BASE_URL . 'perfil');
            exit;
        }

        // 2. Verificar la contraseña actual
        $userId = $_SESSION['user_id'];
        $usuario = $this->usuarioModel->findById($userId);

        $password_actual = $validator->get('password_actual');

        if (!$usuario || !password_verify($password_actual, $usuario->password)) {
            $this->setFlashMessage('error', 'La contraseña actual que ingresó es incorrecta.');
            header('Location: ' . BASE_URL . 'perfil');
            exit;
        }

        // 3. Actualizar la contraseña
        $nuevaPasswordHash = password_hash($password_nuevo, PASSWORD_DEFAULT);
        $datos = ['password' => $nuevaPasswordHash];

        if ($this->usuarioModel->update($userId, $datos)) {
            $this->setFlashMessage('success', '¡Contraseña actualizada exitosamente!');
        } else {
            $this->setFlashMessage('error', 'Error: No se pudo actualizar la contraseña.');
        }

        header('Location: ' . BASE_URL . 'perfil');
        exit;
    }
}