# 🛡️ SGEN-Support - Sistema de Gestión de Soporte Técnico

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
*   **Bitácora de Empleados:** Búsqueda y gestión de personal con validación de Cédula única.
*   **Gestión de Departamentos:** Administración de las áreas de la institución.

### 🔒 Seguridad y Auditoría
*   **Arquitectura Segura:** Enrutador personalizado (`Router.php`), uso estricto de **PDO** para prevenir inyecciones SQL y saneamiento de datos XSS.
*   **Validación Robusta:** Clase `Validator` personalizada que garantiza la integridad de los datos antes de tocar la base de datos.
*   **Bitácora de Acciones:** Registro detallado de *quién hizo qué* (Crear, Editar, Eliminar) con enlaces directos al objeto afectado.
*   **Logs de Sesión:** Historial de inicios y cierres de sesión con cálculo de duración.
*   **Roles y Permisos:** Sistema de control de acceso (ACL) para Administradores, Técnicos y Consultores.

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
    git clone https://github.com/jm-1105/sgen-support.git
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

## 👥 Roles del Sistema

*   **Consultor:** Crea y consulta sus tickets.
*   **Técnico:** Atiende y actualiza tickets asignados.
*   **Administrador:** Supervisa métricas, asigna técnicos y gestiona el sistema completo.

---

### 🌟 Créditos

Desarrollado con ❤️ por **Jonás Mendoza**.
