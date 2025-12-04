<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Usuario;

class ConfiguracionController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct(); // Requiere login
        $this->usuarioModel = new Usuario();
    }

    /**
     * Muestra la página de configuración del tema.
     */
    public function index()
    {
        $this->render('configuracion/index', [
            'titulo' => 'Configuración de Tema'
        ]);
    }

    /**
     * Guarda la preferencia de tema del usuario.
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'configuracion');
            exit;
        }

        $validator = new Validator($_POST);
        $validator->check('tema', 'required', 'Debe seleccionar un tema.');
        $validator->check('tema', 'inList', 'El tema no es válido.', ['light', 'dark']);

        if ($validator->fails()) {
            $this->setFlashMessage('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            header('Location: ' . BASE_URL . 'configuracion');
            exit;
        }

        $nuevoTema = $validator->get('tema');
        $userId = $_SESSION['user_id'];

        // Guardar en la Base de Datos
        if ($this->usuarioModel->update($userId, ['tema' => $nuevoTema])) {
            // Actualizar la Sesión
            $_SESSION['tema'] = $nuevoTema;
            $this->setFlashMessage('success', '¡Tema actualizado correctamente!');
        } else {
            $this->setFlashMessage('error', 'No se pudo guardar el tema.');
        }

        header('Location: ' . BASE_URL . 'configuracion');
        exit;
    }
}