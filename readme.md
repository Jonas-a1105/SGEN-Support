# 🛡️ SGEN-Support - Sistema de Gestión de Soporte Técnico

**Versión:** 1.0.2 | **Última actualización:** 2025-12-10

**SGEN-Support** es una aplicación web moderna, robusta y escalable desarrollada en **PHP 8** bajo el patrón de arquitectura **MVC** (Modelo-Vista-Controlador).

El sistema gestiona el ciclo de vida completo de las solicitudes de soporte técnico, inventario de equipos y auditoría de usuarios, con una interfaz de usuario premium estilo **"Liquid Glass"** y funciones avanzadas de seguridad y reportes.

---

## 🚀 Características Principales

### 🎨 Interfaz y Experiencia de Usuario (UX/UI)
*   **Diseño Glassmorphism:** Interfaz moderna con efectos de vidrio, desenfoque y sombras suaves.
*   **Tema Dinámico:** Selector de **Modo Claro / Modo Oscuro** persistente por usuario.
*   **Bootstrap 5:** Diseño totalmente responsivo y adaptable a móviles.
*   **Alertas Animadas:** Integración con **SweetAlert2** para confirmaciones de eliminación y cierre de sesión con animaciones personalizadas.
*   **Inactividad:** Cierre de sesión automático tras 2 minutos de inactividad por seguridad.

### 🛠️ Gestión Técnica (CRUD Avanzado)
*   **Tickets de Soporte:** Creación, asignación, seguimiento y resolución de incidencias.
*   **Inventario de Equipos:** Registro de activos con validación de seriales únicos y asociación a departamentos.
*   **Mantenimientos Programados:** Gestión de mantenimientos preventivos y correctivos con cronograma.
*   **Bitácora de Empleados:** Búsqueda y gestión de personal con validación de Cédula única.
*   **Gestión de Departamentos:** Administración de las áreas de la institución.

### 🔒 Seguridad y Auditoría
*   **Arquitectura Segura:** Enrutador personalizado (`Router.php`), uso estricto de **PDO** para prevenir inyecciones SQL y saneamiento de datos XSS.
*   **Validación Robusta:** Clase `Validator` personalizada que garantiza la integridad de los datos antes de tocar la base de datos.
*   **Bitácora de Acciones:** Registro detallado de *quién hizo qué* (Crear, Editar, Eliminar) con enlaces directos al objeto afectado.
*   **Logs de Sesión:** Historial de inicios y cierres de sesión con cálculo de duración.
*   **Roles y Permisos:** Sistema de control de acceso (RBAC) para Administradores, Técnicos y Consultores.

### 📄 Reportes y Notificaciones
*   **Reportes PDF:** Generación de reportes individuales y generales usando **Dompdf**, con campos para firmas y logotipos institucionales.
*   **Notificaciones:** Sistema de alertas internas (ej: "Te han asignado un ticket") visible en el panel superior.

---

## 💻 Tecnologías Utilizadas

*   **Backend:** PHP 8.1+
*   **Base de Datos:** MySQL / MariaDB
*   **Frontend:** Bootstrap 5, CSS3 (Variables & Animations), JavaScript (ES6).
*   **Gestor de Paquetes:** Composer.
*   **Librerías Clave:**
    *   `dompdf/dompdf`: Generación de reportes PDF.
    *   `vlucas/phpdotenv`: Gestión de variables de entorno (opcional).
    *   **SweetAlert2:** Alertas modales interactivas.
    *   **DataTables:** Tablas dinámicas con búsqueda y paginación.

---

## 📂 Estructura del Proyecto

```text
/sgen-support/
|
|-- /config/
|   |-- database.php       # Credenciales de la BD
|   |-- routes.php         # Definición de todas las rutas del sistema
|
|-- /docs/                 # Documentación del sistema
|   |-- Guia_Permisos_Roles.md  # Matriz de permisos por rol
|   |-- Guia_Optimizacion.md    # Guía de optimización
|   |-- Guia_email.md           # Configuración de correo
|   |-- Instrucciones_SLA.md    # Instrucciones SLA
|
|-- /public/               # Única carpeta accesible desde el navegador
|   |-- index.php          # Front Controller
|   |-- css/               # Estilos principales (main.css)
|   |-- js/                # Lógica JS (app.js, inactivity-logout.js)
|   |-- img/               # Recursos gráficos
|
|-- /src/                  # Lógica de la Aplicación
|   |-- /Core/             # Núcleo (Router, Controller, Model, Validator)
|   |-- /Models/           # Modelos de datos (Soporte, Equipo, Usuario...)
|   |-- /Views/            # Vistas HTML/PHP (Layouts, Forms, Lists)
|   |-- /Controllers/      # Controladores de cada módulo
|
|-- /vendor/               # Dependencias de Composer
|-- composer.json          # Configuración de dependencias
```

## 📦 Instalación

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/Jonas-a1105/SGEN-Support.git
    cd sgen-support
    ```

2.  **Instalar dependencias:**
    ```bash
    composer install
    ```

3.  **Configuración:**
    *   Crea una base de datos en MySQL.
    *   Renombra `.env.example` a `.env` (o edita `config/database.php`) y configura tus credenciales.

4.  **Ejecutar:**
    *   **Windows:** Doble clic en `SGEN-support.bat`.
    *   **Linux/Mac:** Ejecuta `./SGEN-Support.sh` en la terminal.
    *   **Manual:** Configura tu servidor web (Apache/Nginx) para apuntar a la carpeta `public/` o usa:
        ```bash
        php -S localhost:8000 -t public
        ```

---

## 👥 Roles del Sistema

El sistema implementa un control de acceso basado en roles (RBAC) con tres niveles:

### 👑 Administrador
*   Acceso completo a todas las funcionalidades
*   Gestión de usuarios, empleados y departamentos
*   Asignación y reasignación de técnicos
*   Acceso a logs y auditorías
*   Eliminación de registros

### 🔧 Técnico
*   Gestión de tickets asignados
*   Creación y realización de mantenimientos
*   Generación de reportes
*   **Sin acceso a:** gestión de empleados/usuarios, eliminación masiva

### 👁️ Consultor
*   Visualización de información de su departamento
*   Creación de tickets de soporte
*   Comentarios en tickets
*   **Sin acceso a:** asignación de técnicos, mantenimientos, módulos administrativos

> 📖 Para una matriz detallada de permisos, consulta [`docs/Guia_Permisos_Roles.md`](docs/Guia_Permisos_Roles.md)

---

## 📋 Historial de Versiones

### v1.0.2 (2025-12-10)
*   **🔐 Control de Acceso:**
    *   Menú "Empleados" restringido solo a administradores
    *   Eliminación masiva de equipos restringida a administradores
    *   Botón de editar empleado en departamento restringido a administradores
    *   Botones "Nuevo Mantenimiento" restringidos a admin/técnico
    *   Botón "Asignar" en tickets restringido a admin/técnico
*   **🎨 UI/UX:**
    *   Logo unificado (diamante geométrico) en header, sidebar, login y "Acerca de"
    *   Corrección del parpadeo de tema claro/oscuro al cargar
    *   Badge de estado de equipos ahora muestra el estado real (En Reparación, Fuera de Servicio, etc.)
*   **🐛 Correcciones:**
    *   Corregido alias SQL para mostrar correctamente el empleado asignado a equipos

### v1.0.1 (2025-12-08)
*   Mejoras de rendimiento y optimización
*   Correcciones menores de interfaz

### v1.0.0 (2025-12-01)
*   Versión inicial del sistema

---

## 📚 Documentación Adicional

| Documento | Descripción |
|-----------|-------------|
| [Guía de Permisos por Rol](docs/Guia_Permisos_Roles.md) | Matriz detallada de permisos para cada rol |
| [Guía de Optimización](docs/Guia_Optimizacion.md) | Configuración de rendimiento del servidor |
| [Guía de Email](docs/Guia_email.md) | Configuración del sistema de correo |
| [Instrucciones SLA](docs/Instrucciones_SLA.md) | Niveles de servicio y tiempos de respuesta |

---

### 🌟 Créditos

Desarrollado con ❤️ por **Jonás Mendoza**.

