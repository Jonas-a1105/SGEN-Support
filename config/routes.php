<?php
// Este archivo es cargado por el Router
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\SoportesController;
use App\Controllers\EquiposController;
use App\Controllers\DepartamentosController;
use App\Controllers\EmpleadosController;
use App\Controllers\UsuariosController;
use App\Controllers\LogController;
use App\Controllers\PerfilController;
use App\Controllers\ConfiguracionController;
use App\Controllers\ReportesController;
use App\Controllers\NotificacionesController;
use App\Controllers\BitacoraController;
use App\Controllers\InventarioController;
use App\Controllers\MantenimientosController;
use App\Controllers\AboutController;

// $router está disponible porque lo pasamos desde el Router::load()

// Rutas de Autenticación
$router->get('/auth/login',    [AuthController::class, 'login']);
$router->post('/auth/procesar', [AuthController::class, 'procesar']);
$router->get('/auth/logout',   [AuthController::class, 'logout']);

// Dashboard
$router->get('/', [HomeController::class, 'index']); 

// Soportes
$router->get('/soportes',           [SoportesController::class, 'index']);
$router->get('/soportes/crear',     [SoportesController::class, 'crear']);
$router->get('/soportes/ver/{id}',  [SoportesController::class, 'ver']);
$router->get('/soportes/editar/{id}', [SoportesController::class, 'editar']);
$router->post('/soportes/guardar',  [SoportesController::class, 'guardar']);
$router->get('/soportes/eliminar/{id}', [SoportesController::class, 'eliminar']); 
$router->get('/soportes/asignar/{id}', [SoportesController::class, 'asignar']);
$router->post('/soportes/guardar_firma', [SoportesController::class, 'guardar_firma']);
$router->get('/soportes/pdf/{id}',      [SoportesController::class, 'pdf']);
$router->post('/soportes/agregar_comentario', [SoportesController::class, 'agregar_comentario']);
$router->post('/soportes/subir_archivo', [SoportesController::class, 'subir_archivo']);
$router->get('/soportes/descargar_archivo/{id}', [SoportesController::class, 'descargar_archivo']);
$router->post('/soportes/actualizar_fecha_cierre', [SoportesController::class, 'actualizar_fecha_cierre']);
$router->post('/soportes/guardar_observaciones', [SoportesController::class, 'guardar_observaciones']);
$router->get('/soportes/marcar_espera/{id}', [SoportesController::class, 'marcar_espera']);
$router->get('/soportes/reanudar/{id}', [SoportesController::class, 'reanudar']);
$router->get('/soportes/resolver/{id}', [SoportesController::class, 'resolver']);
$router->post('/soportes/agregar_consumo', [SoportesController::class, 'agregar_consumo']);

// Categorías
$router->get('/categorias', [App\Controllers\CategoriasController::class, 'index']);
$router->get('/categorias/crear', [App\Controllers\CategoriasController::class, 'crear']);
$router->post('/categorias/guardar', [App\Controllers\CategoriasController::class, 'guardar']);
$router->get('/categorias/editar/{id}', [App\Controllers\CategoriasController::class, 'editar']);
$router->post('/categorias/actualizar/{id}', [App\Controllers\CategoriasController::class, 'actualizar']);
$router->get('/categorias/eliminar/{id}', [App\Controllers\CategoriasController::class, 'eliminar']);

// Equipos

$router->post('/equipos/guardar', [EquiposController::class, 'guardar']);
$router->post('/equipos/eliminar/{id}', [EquiposController::class, 'eliminar']);
$router->get('/equipos/apiBuscar', [EquiposController::class, 'apiBuscar']);

// Departamentos
$router->get('/departamentos', [DepartamentosController::class, 'index']);
$router->get('/departamentos/crear', [DepartamentosController::class, 'crear']);
$router->get('/departamentos/ver/{id}', [DepartamentosController::class, 'ver']);
$router->get('/departamentos/editar/{id}', [DepartamentosController::class, 'editar']);
$router->post('/departamentos/guardar', [DepartamentosController::class, 'guardar']);
$router->get('/departamentos/eliminar/{id}', [DepartamentosController::class, 'eliminar']);
$router->post('/departamentos/asignarEquipo', [DepartamentosController::class, 'asignarEquipo']);

// Empleados
$router->get('/empleados', [EmpleadosController::class, 'index']);
$router->get('/empleados/crear', [EmpleadosController::class, 'crear']);
$router->get('/empleados/editar/{id}', [EmpleadosController::class, 'editar']);
$router->post('/empleados/guardar', [EmpleadosController::class, 'guardar']);
$router->get('/empleados/eliminar/{id}', [EmpleadosController::class, 'eliminar']);
$router->get('/empleados/apiBuscarPorCedula', [EmpleadosController::class, 'apiBuscarPorCedula']);

// Usuarios
$router->get('/usuarios', [UsuariosController::class, 'index']);
$router->get('/usuarios/crear', [UsuariosController::class, 'crear']);
$router->get('/usuarios/editar/{id}', [UsuariosController::class, 'editar']);
$router->post('/usuarios/guardar', [UsuariosController::class, 'guardar']);
$router->get('/usuarios/eliminar/{id}', [UsuariosController::class, 'eliminar']);

// Logs
$router->get('/logs', [LogController::class, 'index']);

// Perfil y Configuración
$router->get('/perfil', [PerfilController::class, 'index']);
$router->post('/perfil/actualizar', [PerfilController::class, 'actualizar']);
$router->get('/configuracion', [ConfiguracionController::class, 'index']);
$router->post('/configuracion/guardar', [ConfiguracionController::class, 'guardar']);

// Reportes (sistema info moved to /about)
$router->get('/reportes', [ReportesController::class, 'index']);
$router->get('/reportes/soportes', [ReportesController::class, 'soportes']);
$router->get('/reportes/soportes_excel', [ReportesController::class, 'soportes_excel']);
$router->get('/reportes/inventario', [ReportesController::class, 'inventario']);
$router->get('/reportes/mantenimientos', [ReportesController::class, 'mantenimientos']);

// Notificaciones
$router->get('/notificaciones/marcar-todas-leidas', [NotificacionesController::class, 'marcarTodasLeidas']);
$router->get('/notificaciones/leer/{id}', [NotificacionesController::class, 'leer']);

// Bitácora
$router->get('/bitacora', [BitacoraController::class, 'index']);

// Inventario General
$router->get('/inventario',         [InventarioController::class, 'index']);
$router->get('/inventario/crear',   [InventarioController::class, 'crear']);
$router->post('/inventario/crear',  [InventarioController::class, 'crear']);
$router->post('/inventario/movimiento', [InventarioController::class, 'movimiento']);
$router->post('/inventario/baja', [InventarioController::class, 'baja']);
$router->get('/inventario/ver/{id}', [InventarioController::class, 'ver']);
$router->get('/inventario/distribucion/{id}', [InventarioController::class, 'distribucion']);
$router->post('/inventario/transferir', [InventarioController::class, 'transferir']);
$router->get('/inventario/departamento', [InventarioController::class, 'por_departamento']);
$router->get('/inventario/departamento/{id}', [InventarioController::class, 'por_departamento']);

// Mantenimientos
$router->get('/mantenimientos', [MantenimientosController::class, 'index']);
$router->get('/mantenimientos/dashboard', [MantenimientosController::class, 'dashboard']);
$router->get('/mantenimientos/crear', [MantenimientosController::class, 'crear']);
$router->get('/mantenimientos/editar/{id}', [MantenimientosController::class, 'editar']);
$router->get('/mantenimientos/ver/{id}', [MantenimientosController::class, 'ver']);
$router->post('/mantenimientos/guardar', [MantenimientosController::class, 'guardar']);
$router->get('/mantenimientos/eliminar/{id}', [MantenimientosController::class, 'eliminar']);

// Acerca de
$router->get('/about', [AboutController::class, 'index']);