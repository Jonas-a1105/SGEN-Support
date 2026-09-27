# 📜 Registro de Cambios (Changelog)

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato se basa en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### ✨ Añadido (Added) — fase 4 y fase 5 completadas
- **Visibilidad por rol (RBAC de lectura)**: `App\Support\Visibility\VisibilityScope` aplicado a los listados sensibles (equipos, mantenimientos); admin/técnico/consultor ven global, operador) solo su departamento (empleado/cuenta). la navegación del sidebar ya está también filtrada por rol del usuario (`NAV_ITEMS_REGISTRY.roles`). La matriz Spatie sigue protegiendo las acciones. Tests `VisibilityScopeTest` (operador solo su área; admin global).
- **Dashboard por rol — "Mi trabajo hoy"**: la página ya muestra una tarjeta personal que agrega mis tickets pendientes/en proceso/cerrados, los equipos que tengo custodiados y las órdenes laborales asignadas a mi usuario — el usuario entra, lee y opera sin navegar.
- La pestaña "Sistema" en Configuración ahora está limitada a `configuracion.manage`; las entradas de Roles/Papelera/Claves globales ya no quedan solo en URL.
- **Acceso tab a plataforma operativa**: pestaña "Sistema" en Configuración (solo `configuracion.manage`) con entradas a Claves globales, Editor de roles y Papelera — ya no solo URL.
- **Datos maestros mínimos en alta de usuarios**: `email` ahora obligatorio si no hay empleado vinculado (o un empleado con email garantizado) — asegura que el reseteo self-service por correo tenga una vía real. Tests que construyen usuarios sin email actualizados al contrato.
- **Datos maestros mínimos en alta de usuarios**: `email` ahora obligatorio si no hay empleado vinculado (o un empleado con email garantizado) — asegura que el reseteo self-service por correo tenga una vía real. Tests que construyen usuarios sin email actualizados al contrato.
- **Cascade de evidencia reforzada** (migración PostgreSQL): soportes, mantenimientos, kardex de inventario, consumos e ubicaciones ahora RESTRICT/SET NULL donde la historia debe sobrevivir; no se borra en cascada la bitácora patrimonial.
- **Baja patrimonial formal de equipos**: wizard con motivo legal/valor de recuperación/destino/nota/consentimiento irreversible; precondiciones durales (custodia abierta, mantenimientos o tickets pendientes lo vetan), estado a `de_baja`, queda fuera del ciclo con acta PDF y hash SHA-256 grabado. El equipo eliminado queda en la papelera, no se destruye.
- **Soft delete universal de catálogos** (equipos, inventario, departamentos, categorías, mantenimientos) + **Papelera** (`/papelera`): listado, restauración en 1 clic y purga definitiva con control administrativo.
- **Editor de roles con matriz Spatie** (`/roles`): visual y sincronización de permisos por rol, rol de sistema `admin` bloqueado para no autodestruir acceso, con conteo real de usuarios por rol.
- **Configuración global visual** (`/configuracion/sistema`): las claves operativas se ajustan desde la UI con `actualizado_por` en bitácora (para login, sesiones, SLA, cooldown de emails, autocierre).
- **Autocierre de sesiones concurrentes** (`seguridad.sesiones_concurrentes_max` = 3 por defecto): al iniciar una nueva sesión se cierran las más antiguas si supera el límite.
- **Self-service de contraseña** (`forgot/reset` por email) con token firmado de un solo uso y invalidación inmediata de sesiones.
- **SLA laboral real** (`SlaPolicy` + tabla `feriados`): los plazos corren en horario laboral L–V 08:00–18:00, feriados no cuentan, y las pausas extienden solo tiempo útil.
- Suite completa en verde, PHPStan limpio, typecheck limpio.

### 🔧 Cambios (Changed)
- Equipment: eliminar físico → solo si no tiene historia; con historia, baja formal o papelera (patrimonio protegido end-to-end).
- Inventario: cantidades NUMERIC(15,3) en items/ubicaciones/movimientos/consumos + lock+validación en transferencias.
- Bitácora: navetaje sensible (password/tokens/firmas/binarios) enmascarado en el límite de dominio.

### 📚 Documentación (Docs)
- **`fundamentos.md` actualizado con estado real verificado** (2026-09-26): nueva PARTE 0 con tablas de estado por módulo (01–38), journeys, convenciones universales e innovación; el checklist maestro de 60 validaciones ahora está marcado ítem por ítem (✅/🟡/❌) con evidencia y tests.

### 🐛 Corregido (Fixed)
- **Wizard de registro invisible fuera de Inventario**: el CSS legado escondía `.detail-view-container` salvo clase `.active` (patrón de view-switching anterior a Inertia); el componente ahora se autogoberna con `v-if` y fuerza su propia visibilidad sin depender de ese estado.

### ✨ Añadido (Added) — ola transversal
- **#21 Idempotency-Key end-to-end**: middleware con replay de respuesta ante reintentos (creación de tickets, firma de custodia, calificación, firma de ticket y consumo de materiales); tabla `idempotency_keys` persistente; el FE emite la clave por intento de envío.
- **#22 Locking optimista**: columna `version` en `equipos`/`soportes` con UPDATE atómico por versión + 409 formal al conflicto; la edición se despacha con la versión vista en pantalla.
- **#44 Máscara de secretos en bitácora**: `SensitiveDataMasker` en el límite del dominio, recursivo, y bloqueo de payload binario/base64 en `bitacora_acciones`.
- **#45 Correos del sistema con anti-spam**: `EmailThrottler` (cooldown por tipo/destinatario desde config global) + tabla `correos_enviados` con evidencia.
- **#37 Cantidades decimales (3 dígitos)**: `NUMERIC(15,3)` en items/ubicaciones/movimientos/consumos; validaciones fraccionarias.

### 🔧 Cambios (Changed)
- **Integridad de inventario endurecida (puntos #14–#16)**: transferencias verifican origen≠destino y saldo origen con `lockForUpdate` (bug real: antes podía quedar negativo); `cantidad > 0` por CHECK en movimientos y consumos; saldo por ubicación jamás negativo (CHECK); kardex con `referencia_tipo`/`referencia_id` siempre en pares (trazabilidad completa); `garantía ≥ fecha de compra` en equipos y `próxima fecha ≥ fecha` en mantenimientos.
- **Alta de equipos unificada sobre el wizard de 4 pasos**: `ViewEditEquipmentWizard.vue` (el diseño del Inventario: Básica → Especificaciones → Ubicación → Adquisición) es ahora el formulario canónico único — la pantalla de **Equipos** lo presenta como vista de formulario directo, con edición precargada desde la ficha JSON cuando el listado es ligero. Se retiraron el endpoint duplicado `/equipos/registrar` (request/DTO/use case adapter) y el modal legacy con estilos propios. Un solo flujo, un solo contrato, la misma cadena custodial en ambos módulos.

### ✨ Añadido (Added)
- **Módulo de Custodias formales (Módulo 12)**: tabla `custodias` con cadena patrimonial — índice parcial único **una custodia activa por equipo** (#6), apertura/cierre atómico en cada reasignación con eslabón cerrado y actor que la ejecutó, firma probatoria del custodio (hash SHA-256 + IP + agente + sello, única e inmutable) y bloqueo de borrado físico del equipo con historia custodial. Pestaña "Custodia" en la ficha del equipo con historial y modal de firma (canvas).
- **Regla de offboarding #27**: un empleado con custodias activas no puede desvincularse; el error lista las custodias por recuperar (`EmployeeHasActiveCustodiesException`).
- **Identificadores únicos a nivel motor (puntos 6.1 #1/#3/#4/#28)**: IP vigente y código patrimonial vigente únicos en `equipos` (índices parciales; baja libera), cédula vigente única en `empleados`, vínculo empleado↔usuario biunívoco en ambas direcciones. Migración con saneamiento determinista (IP '' → NULL; códigos duplicados del legado sufijados con su id) y fallo explícito ante duplicados reales.
- **Estados finales formales de equipo + tickets bloqueados a `de_baja` (punto #30)**: enum ampliado (`de_baja`, `prestado`, `perdido`) alineado con el CHECK de BD; la creación de tickets rechaza equipos dados de baja con mensaje de negocio.
- **Numeración legible transaccional de tickets (punto #24)**: `TIC-AAAA-00001` vía tabla `correlativos` con `lockForUpdate` en la misma transacción de creación (imposible duplicar); backfill histórico por año; URL acepta `TIC-…`, `T-{id}` y numérico; código visible en lista, ficha y PDF.
- **Reapertura con ventana y actor legítimo (punto #31)**: ventana `tickets.ventana_reapertura_dias` desde `configuracion_global`; solo solicitante o personal operativo (autorización 403 real); fuera de ventana → excepción de dominio con mensaje. Ruta `/reabrir` accesible al solicitante.
- **Argon2id como driver de hash (punto #52)**: `config/hashing.php` con argon2id, migración transparente de hashes bcrypt en el primer login (`Hash::needsRehash`), todas las creaciones de contraseña pasan por `Hash::make`.
- **Cierre de sesión por inactividad (punto #50)**: middleware `EnsureSessionIdle` (límite desde `seguridad.idle_minutos`, default 30), registro forense en `sesiones_log` con IP/UA y `motivo_cierre` (manual | inactividad); el login abre la fila y la sesión queda vinculada.
- **Desactivación de usuarios sin borrado** (`usuarios.activo`): las cuentas con historial operativo se desactivan en vez de eliminarse; la desactivación revoca las sesiones vivas al instante y respeta las protecciones (último administrador, técnico con tickets activos). El login de una cuenta inactiva se rechaza con mensaje claro y queda en `intentos_login`. Badge Activo/Inactivo y acción de alternancia en el directorio de usuarios.
- **Estado `cancelado` en la máquina de tickets**: nuevo estado terminal (anulable desde pendiente/en_proceso/en_espera; jamás revive). Enum, hidración, CHECK de BD, validación del endpoint y cobertura combinatoria 6×6 actualizada.
- **Configuración global persistida** (`configuracion_global` + `GlobalConfigSeeder` + `App\Support\Config\ConfiguracionGlobal`): claves de negocio (SLA por prioridad, días de autocierre, intentos máximos de login) ajustables sin desplegar código; lectura cacheada con invalidación al actualizar. Consumidores reales: umbral de bloqueo de login y ventana de autocierre.
- **Registro forense de autenticación** (`intentos_login`): cada intento exitoso o fallido con username, IP y agente — jamás la contraseña (punto #54).

### 🔒 Seguridad (Security)
- **Anti auto-bloqueo de cuentas**: nadie puede desactivar ni eliminar su propia cuenta en sesión (`SelfAccountActionException`); las protecciones estructurales ahora cuentan solo administradores **habilitados** (`countActiveAdmins`) en desactivación, degradación y borrado — un admin inactivo ya no puede "rescatar" el sistema.
- **Bajas de usuarios con historial bloqueadas** (punto #43): un usuario con movimientos de inventario o comentarios en tickets (FK estricta) no puede eliminarse; el error explica los conteos por origen y cualquier FK residual se traduce a mensaje de negocio en lugar de un 500 (`UserHasOperationalHistoryException`, `UserDeletionFailedException`).
- **Bloqueo temporal con alerta a administración**: al agotarse los intentos de login, se notifica una única vez por ráfaga a todos los administradores (anti fuerza bruta con evidencia).
- **CSP estricta con nonce por petición** (`app/Http/Middleware/SecurityHeaders.php`): `Content-Security-Policy` con nonce criptográfico compartido a la plantilla Blade (Ziggy y bootstrap de tema), `object-src 'none'`, `base-uri`/`form-action`/`frame-ancestors` restringidos; origen de Vite (5173) solo cuando el servidor de desarrollo está activo. Cubre el punto #47 del checklist maestro.
- **Validación estricta de firma canvas**: `firma_base64` exige data URL de imagen real (`starts_with:data:image/`, mínimo 100 caracteres).

### ✨ Añadido (Added) — validaciones anteriores del mismo unreleased
- **CHECK `stock_actual >= 0` en `inventario_items`** (migración PostgreSQL): el motor rechaza stock negativo aunque se saltee la capa de aplicación; la migración falla de forma explícita si existe data negativa previa (punto #9 del checklist).
- **Protección de carga operativa**: no se puede eliminar un usuario cuyo empleado vinculado tiene tickets activos; el error lista los tickets por reasignar (`UserHasActiveTicketsException`, punto #26).
- **Calificación única y solo del solicitante** (punto #32): `rateTicket` atómico con `lockForUpdate`, excepción de dominio `RatingNotAllowedException` y guarda inmutable en la entidad `Ticket`.
- **Firma con valor probatorio** (punto #33): `firma_hash_sha256` + `firma_ip` + `firma_user_agent` + `firmado_en` en tickets, capturados tanto en la ruta dedicada como en la resolución con conformidad; firma única e inmutable (`SignatureAlreadyRegisteredException`).
- Tests de cobertura de todas las reglas anteriores (feature + dominio).

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
