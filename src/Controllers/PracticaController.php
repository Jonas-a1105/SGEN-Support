<?php
namespace App\Controllers;

use App\Core\Controller;

class PracticaController extends Controller
{
    /**
     * El método index es el corazón de nuestra página.
     * Aquí le decimos al sistema qué "vista" (archivo HTML) cargar.
     */
    public function index()
    {
        // 1. Definimos algunos "datos" (ingredientes de nuestra receta)
        $mensaje = "¡Hola Mundo! Este es mi primer código.";
        $hora_actual = date('H:i:s');
        $usuario = $_SESSION['username'] ?? 'Aprendiz';

        // 2. Enviamos esos datos a la vista usando el método render()
        // El primer parámetro es la ruta de la vista: 'practica/hola'
        // El segundo es un arreglo con los datos
        $this->render('practica/hola', [
            'titulo' => 'Mi Primera Página',
            'mimensaje' => $mensaje,
            'hora' => $hora_actual,
            'nombre_usuario' => $usuario
        ]);
    }
}
