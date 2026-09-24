# 📜 Registro de Cambios (Changelog)

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato se basa en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

---

## [1.1.0] - 2026-09-24

### 🚀 Añadido (Added)
- **Suite de Componentes Base Reutilizables** (`resources/js/Components/UI`):
  - `BaseKpiCard.vue`: Tarjetas de indicadores con borde dinámico de 4px, animación en hover, soporte para 8 variantes semánticas y etiquetas en minúsculas sin transformaciones forzadas.
  - `BaseSearchToolbar.vue`: Barra de búsqueda unificada con slots para filtros, botones de acción y alineaciones intercambiables.
  - `BaseViewModeToggle.vue`: Selector estandarizado para alternar entre vista en cuadrícula y vista en tabla.
  - `BaseToggleSwitch.vue`: Interruptor de alternancia accesible y responsivo.
  - `BaseConfirmModal.vue`: Diálogo de confirmación con variantes destructivas o neutrales.
  - `BasePageHeader.vue`: Encabezados modulares con título, descripción y slots de acciones.
  - `BaseCard.vue`, `BaseButton.vue`, `BaseEmptyState.vue`: Primitivas visuales sin sombras ni estilos inline.
- **Pipeline de CI/CD Automatizado** (`.github/workflows/`):
  - `ci.yml`: Validación continua con PHP 8.2, SQLite en memoria, Laravel Pint, PHPUnit (124 tests) y build de Vite.
  - `release-desktop.yml`: Empaquetado automático en Windows con `electron-builder`, generación de instaladores NSIS, cálculo de SHA256 y despliegue a GitHub Releases para `electron-updater`.
  - `pr-lint.yml`: Validación estricta de Conventional Commits en Pull Requests.
- **Automatización de Lanzamientos**:
  - Script PowerShell `scripts/release.ps1` para validación de tests, sincronización de versiones (SemVer), etiquetado Git y despliegue automático.
- **Infraestructura de Repositorio Senior**:
  - `.gitattributes` para normalización de fin de línea (LF/CRLF) y protección de binarios.
  - `commitlint.config.js` para estandarización de commits.
  - Plantillas de PR e Issues estructuradas (`.github/PULL_REQUEST_TEMPLATE.md` e `ISSUE_TEMPLATE/`).

### 🔄 Modificado (Changed)
- **Refactorización Integral de 11 Módulos**:
  - Todos los módulos (`Dashboard`, `Support`, `Inventory`, `Equipment`, `Employee`, `Department`, `User`, `Category`, `Maintenance`, `Reports`, `Audit`) fueron migrados a los componentes compartidos centralizados.
  - Reducción del bundle CSS de **328 kB a 294.09 kB** (-34 kB de estilos duplicados eliminados).
  - Eliminación del 100% de atributos `style="..."` e inline styles en los componentes de la aplicación.
  - Supresión completa de sombras no-none (`box-shadow: none !important;`).

### 🔒 Seguridad (Security)
- Auditoría automatizada de vulnerabilidades en dependencias Composer y NPM en cada commit.
- Estricto aislamiento de credenciales `.env` y secretos en el empaquetado de producción.

---

## [1.0.28] - 2026-01-27

### 🚀 Añadido (Added)
- Integración de `electron-updater` en la aplicación de escritorio (`desktop/main.js`).
- Detección automática y silenciosa de nuevas versiones con confirmación de usuario para descarga e instalación.
- Arquitectura híbrida cliente/servidor para sincronización de base de datos local y remota.
