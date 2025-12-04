<?php
namespace App\Core;

class Router {
    private $routes = [
        'GET' => [],
        'POST' => []
    ];

    public static function load(string $file) {
        $router = new static;
        require $file;
        return $router;
    }

    public function get(string $uri, array $controllerAction) {
        $this->routes['GET'][$uri] = $controllerAction;
    }

    public function post(string $uri, array $controllerAction) {
        $this->routes['POST'][$uri] = $controllerAction;
    }

    public function direct(string $uri, string $requestMethod) {
        // --- LÓGICA ROBUSTA PARA WINDOWS Y LINUX ---
        
        // 1. Si el servidor Apache tiene mod_rewrite activo y pasa la variable 'url'
        if (isset($_GET['url'])) {
            $uri = '/' . trim($_GET['url'], '/');
        } 
        // 2. Fallback: Cálculo manual de la URI (Vital si .htaccess falla o en Nginx)
        else {
            // CORRECCIÓN CRÍTICA: Normalizar las barras a formato UNIX (/)
            // Windows usa \ en rutas de archivo, pero la URL siempre es /
            $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            
            // Si la URI empieza con la carpeta del proyecto, la quitamos para obtener la ruta relativa
            if (strpos($uri, $scriptName) === 0) {
                $uri = substr($uri, strlen($scriptName));
            } elseif (strpos($uri, dirname($scriptName)) === 0) {
                 // Intento secundario por si dirname incluye 'public'
                 $uri = substr($uri, strlen(dirname($scriptName)));
            }
            
            // Limpieza final de parámetros GET
            if (strpos($uri, '?') !== false) {
                $uri = substr($uri, 0, strpos($uri, '?'));
            }
        }

        // Normalizar raíz
        if ($uri === '' || $uri === '/index.php') {
            $uri = '/';
        }

        // --- MANEJO DE RUTAS ---

        if (array_key_exists($uri, $this->routes[$requestMethod])) {
            $action = $this->routes[$requestMethod][$uri];
            return $this->callAction($action[0], $action[1], []);
        }

        // Rutas dinámicas
        foreach ($this->routes[$requestMethod] as $route => $action) {
            if (preg_match('#^' . preg_replace('/\{\w+\}/', '(\d+)', $route) . '$#', $uri, $matches)) {
                array_shift($matches);
                return $this->callAction($action[0], $action[1], $matches);
            }
        }

        $this->trigger404("Ruta no encontrada: {$uri}");
    }

    private function callAction(string $controllerClass, string $method, array $params = []) {
        if (!class_exists($controllerClass)) {
            $this->trigger404("Controlador no existe: {$controllerClass}");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            $this->trigger404("Método {$method} no existe en {$controllerClass}");
        }

        call_user_func_array([$controller, $method], $params);
    }
    
    private function trigger404($message) {
        http_response_code(404);
        // Si tienes una vista de error bonita, cárgala aquí.
        // require __DIR__ . '/../../src/Views/error/404.php'; 
        echo "<div style='font-family:sans-serif; padding:20px; text-align:center;'>";
        echo "<h1>Error 404</h1>";
        echo "<p>{$message}</p>";
        echo "<p><small>Verifica tu archivo config/routes.php o la URL.</small></p>";
        echo "</div>";
        exit;
    }
}