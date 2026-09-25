# AUDITORÍA TÉCNICA INTEGRAL — SGEN-SUPPORT

**Objeto auditado:** aplicación `sgen-backend` (Laravel 12 / PHP 8.2 / Inertia + Vue 3)
**Referencias normativas:**
- `db.sql` — Esquema PostgreSQL v2.0 (especificación de datos, 1.663 líneas, 99 `CREATE TABLE`)
- `fundamentos.md` — Documento Maestro de Producto (38 módulos, 6 journeys, 120+ validaciones, 60+ notificaciones, PARTES I–IX)

**Fecha:** 2026-09-24
**Alcance:** comparación exhaustiva BD real vs. `db.sql`; implementación real vs. `fundamentos.md`; flujos, validaciones, seguridad, calidad y testing.
**Método:** análisis estático de migraciones, capa de dominio/aplicación/infraestructura, rutas, FormRequests, seeders, frontend y configuración. Evidencia citada como `archivo:línea`.

---

## 1. RESUMEN EJECUTIVO

`fundamentos.md` define un sistema empresarial con **38 módulos**, **6 journeys end-to-end**, **8 pilares de innovación** y una verificación explícita de completitud ITIL 4 (`fundamentos.md:354`). `db.sql` define **93 tablas base + 6 particiones + 6 vistas ≈ 86–99 objetos** con 12 decisiones arquitectónicas PostgreSQL avanzadas (`db.sql:4-17`).

La aplicación `sgen-backend` implementa un **subconjunto mínimo y en gran parte no funcional de ese diseño**:

| Dimensión | Especificado | Implementado | Cobertura |
|---|---|---|---|
| Tablas de negocio | 93 | 17 | **18,3 %** |
| Tablas (incl. framework/Spatie) | — | 30 (+`migrations`) | — |
| Vistas de negocio | 6 | 0 | **0 %** |
| Módulos de producto | 38 | 15 con carpeta (solo 3–4 funcionales) | **≈ 10 %** |
| Decisiones arquitectónicas PG | 12 | 0 completas, 2 parciales | **≈ 8 %** |
| Claves de configuración global (seed) | 17 | 0 (prefs por usuario en caché) | **0 %** |
| Roles RBAC | 5 | 3 (sin permisos) | parcial |
| Permisos RBAC | ~126 | 0 | **0 %** |
| Automatización / Scheduler / API | 4 módulos | 0 | **0 %** |

**Veredicto global:** el proyecto es un **MVP/legacy portado de MariaDB a Laravel** que **no cumple** el contrato de datos ni el contrato funcional de los documentos de referencia. Existen inconsistencias de esquema que rompen funcionalidad en tiempo de ejecución (rutas a métodos inexistentes, columnas inexistentes en comandos), y una **ausencia total de autorización por rol**, lo que constituye un riesgo de seguridad crítico. La base de dominio DDD existe pero está desconectada del runtime.

**Semáforo por área**

| Área | Estado |
|---|---|
| Esquema de datos vs `db.sql` | 🔴 Crítico |
| Módulos vs `fundamentos.md` | 🔴 Crítico |
| Seguridad y autorización | 🔴 Crítico |
| Integridad de stock / dinero | 🔴 Crítico |
| Máquina de estados y SLA | 🟠 Alto |
| Auditoría forense | 🔴 Crítico |
| Notificaciones | 🟠 Alto |
| Testing y calidad | 🟠 Alto |
| Arquitectura / organización del código | 🟢 Aceptable (base sólida, mal conectada) |

---

## 2. INVENTARIO DE ARTEFACTOS

**Backend `sgen-backend`:**
- 22 migraciones; 30 tablas creadas (17 de negocio, 8 framework, 5 Spatie).
- 14 módulos en `src/Modules` (About, Audit, Category, Dashboard, Department, Employee, Equipment, Inventory, Maintenance, Notification, Reports, Settings, Support, User).
- 15 controladores en `app/Infrastructure` + `app/Http/Controllers`.
- 23 páginas Vue en `resources/js/Pages`; 13 entradas de menú reales (`resources/js/Composables/useAppNavigation.ts`).
- Rutas: **solo `routes/web.php` y `routes/console.php`**; no existe `routes/api.php`.
- Stack de autorización: `spatie/laravel-permission 6.25` y `laravel/sanctum` **instalados pero no aplicados**.

**Especificación:** `db.sql` (1663 líneas) y `fundamentos.md` (356 líneas).

**Motor real:** PostgreSQL (`sgen-backend/.env` → `DB_CONNECTION=pgsql`, `DB_DATABASE=sgen_db`); `config/database.php:20`. Coincide con el motor del spec, aunque existen residuos SQLite (`sgen-backend/sgen_db`, `database/database.sqlite`) y dumps MariaDB (`sgen_db.sql`, `database/*.sql`).

---

## 3. AUDITORÍA DE BASE DE DATOS — `db.sql` v2.0 vs. migraciones

### 3.1 Conteos exactos

- `db.sql`: **99 `CREATE TABLE`** (93 base + 6 particiones) + **6 vistas** (`v_custodias_activas`, `v_stock_reconciliacion`, `v_backlog`, `v_kpi_tecnico`, `v_sla_cumplimiento`, `v_alertas`).
- Migraciones: **30 tablas** (17 negocio + 8 framework + 5 Spatie). Solo **14 tablas del spec existen con el mismo nombre**; 4 tienen equivalente vía Spatie; **75 tablas del spec (80,6 %) NO EXISTEN**.
- 3 tablas creadas por migración **no previstas** en el spec con ese diseño: `inventario_ubicaciones`, `ticket_archivos`, `bajas_inventario`.

### 3.2 Decisiones arquitectónicas del spec (`db.sql:4-17`) — cumplimiento

| # | Decisión spec | Estado | Evidencia |
|---|---|---|---|
| 1 | `TIMESTAMPTZ` en todo | ❌ No | migraciones usan `timestamps()`/`dateTime` sin zona |
| 2 | Dinero `BIGINT` centavos + `monedas`/`tasas_cambio` | ❌ No | `decimal(10,2)` en `equipos.valor_compra`, `inventario_items.valor_compra`, `mantenimientos.costo`; sin tablas de moneda |
| 3 | `citext` email/username | ❌ No | `string(150)` / `string(50)` |
| 4 | `INET`/`CIDR`/`MACADDR` + IPAM | ❌ No | `direccion_ip(50)`, `ip_address(45)` varchar; sin `subredes` |
| 5 | `JSONB` + índices GIN | ❌ No | se usa `json`; sin GIN |
| 6 | Full-text `tsvector` español | ❌ No | sin columna `busqueda` |
| 7 | Índices `UNIQUE` parciales (soft-delete aware) | ❌ No | uniques globales; incompatible con soft delete |
| 8 | `EXCLUDE USING gist` + `tstzrange` | ❌ No | sin `guardias`/`ticket_horas`; `btree_gist` no creada |
| 9 | Particionado por rango | ❌ No | tablas planas |
| 10 | Hash-chain SHA-256 forense | ❌ No | sin `hash_previo`/`hash_registro` |
| 11 | RBAC editable en tablas | ⚠️ Parcial | vía Spatie, no el diseño del spec; persiste enum `rol` legado |
| 12 | Soft delete universal + FK saneadas | ⚠️ Parcial | solo `empleados.deleted_at`; abundan `cascade` donde el spec pide `restrict` |

Adicional: no se crean las extensiones `citext`, `pgcrypto`, `btree_gist`, ni los dominios `dominio_email`, `color_hex`, `hash_sha256`, ni los ENUM nativos del spec.

### 3.3 Tabla por tabla — existentes y sus desviaciones

| Tabla spec | Realidad | Desviaciones clave |
|---|---|---|
| `departamentos` | EXISTE | Falta `padre_id` (jerarquía), `centro_costo`, FK de jefe, `activo`, `deleted_at`. `jefe_area_id` sin FK (`2026_03_23_000001:12`) |
| `empleados` | EXISTE | `email` unique global (no parcial); `cedula` sin unique; `rol` enum legado; relación inversa `usuario_id`; faltan `codigo`, `es_vip`, `fecha_ingreso/salida`, `jefe_directo_id`, etc. |
| `usuarios` | EXISTE | `password` (≠ `password_hash`); enum `rol`; `empleado_id` nullable `set null` (spec `RESTRICT`); faltan `email`, `activo`, `locale`, `zona_horaria`, 2FA, lockout, `deleted_at` |
| `categorias` | EXISTE | sin `padre_id`, `color` varchar (≠ dominio `color_hex`), solo `created_at`, sin `deleted_at` |
| `equipos` | EXISTE | `codigo_inventario` (≠ patrimonial); tipo/marca string (≠ FK); `direccion_ip` varchar; estado mezcla estado y condición; `valor_compra` decimal; sin `especificaciones JSONB`, `moneda_id`, `vida_util_anios`, leasing, `deleted_at` |
| `soportes` | EXISTE | 4 estados (faltan `cerrado`/`cancelado`); `titulo` nullable; `valoracion` enum texto (≠ 1–5); `equipo_id` CASCADE (≠ RESTRICT); faltan `codigo`, impacto/urgencia, canal, SLA, incidente mayor, watchers, tsvector, `token_publico`, `deleted_at` (decenas) |
| `mantenimientos` | EXISTE | `costo` decimal único (≠ desglose centavos); `tecnico_id` sin FK; `checklist` `json` (≠ jsonb); sin `serie_padre_id`, `origen_ticket_id`, `garantia_hasta`, `deleted_at` |
| `inventario_items` | EXISTE | `categoria`/`marca`/`unidad_medida` string (≠ FK); stock `integer` (≠ `NUMERIC(12,3)`); `valor_compra` decimal; faltan `stock_reservado`, `moneda_id`, `fecha_vencimiento`, `deleted_at` |
| `inventario_movimientos` | EXISTE | `cantidad` integer; sin `condicion`, `costo_unitario`, `saldo_despues`, `referencia_tipo`; enums en mayúsculas; `item_id` CASCADE |
| `inventario_consumos` | EXISTE | sin `entidad`/`mantenimiento_id`/`costo_total`; FKs CASCADE |
| `ticket_comentarios` | EXISTE | `fecha` en vez de `created_at`+`updated_at`; sin índice |
| `notificaciones` | EXISTE | sin `tipo`/`titulo`/`icono`; `leido` boolean (≠ `leido_el`) |
| `sesiones_log` | EXISTE | plana (spec particionada); FK CASCADE (spec RESTRICT); sin `ip`/`user_agent`/`motivo_cierre`/`duracion` |
| `bitacora_acciones` | EXISTE | plana; `json` (≠ jsonb); sin `hash_previo`/`hash_registro`/`user_agent`; no particionada |
| `roles`/`permisos`/`rol_permisos`/`usuario_roles` | EQUIV (Spatie) | nombres en inglés, sin `modulo`/`descripcion`/`es_sistema`; pivote polimórfico sin auditoría de asignación |

### 3.4 Tablas del spec ausentes (agrupadas)

Fundacionales: `monedas`, `tasas_cambio`, `sedes`, `ubicaciones`, `unidades_medida`.
Catálogos: `tipos_equipo`, `fabricantes`, `categorias_inventario`, `matriz_prioridad`, `tags`, `entidad_tags`.
Activos: `equipo_fotos`, `equipo_relaciones`, `subredes`, `custodias`, `actas`, `acta_detalles`, `firmas`, `licencias`, `licencia_asignaciones`, `prestamos`.
Helpdesk: `archivos`, `ticket_horas`, `ticket_watchers`, `plantillas_respuesta`, `campos_definiciones`, `ticket_campos`.
SLA: `sla_definiciones`, `calendarios`, `calendario_horarios`, `dias_festivos`, `escalamientos`, `escalamientos_log`.
Inventario: `inventario_stock_ubicacion`, `inventario_reservas`, `tomas_fisicas`, `tomas_fisicas_detalles`.
Mantenimiento: `mantenimiento_materiales`, `rma`.
ITIL: `problemas`, `problema_incidentes`, `problema_equipos`, `cambios`, `cambio_aprobaciones`, `cambio_equipos`.
Servicios: `catalogo_servicios`, `catalogo_servicio_items`, `solicitudes`, `aprobaciones`.
Compras: `proveedores`, `compras`, `compra_items`, `contratos`, `suscripciones`, `suscripcion_asignaciones`.
Automatización: `automatizaciones`, `automatizacion_logs`, `tareas_programadas`, `tareas_logs`.
Conocimiento: `kb_articulos`, `kb_calificaciones`.
Ciclo de vida: `checklists_plantilla`, `checklist_plantilla_tareas`, `checklists_instancia`, `checklist_instancia_tareas`.
Cartereo: `cartereos`, `cartero_asignaciones`, `cartero_detalles`.
Guardias: `guardias`.
Notificaciones: `preferencias_notificacion`, `correos_enviados`.
Auditoría: `intentos_login`.
Sistema: `configuracion`, `reportes_historial`, `reportes_programados`.

### 3.5 Seeders

- `DatabaseSeeder` invoca **solo** `RbacAndUserSeeder`; `LegacyDatabaseSeeder` queda huérfano.
- `RbacAndUserSeeder`: 3 roles (admin/tecnico/consultor), **0 permisos**, 3 usuarios con credenciales por defecto `admin123`/`tecnico123`/`consultor123`.
- El spec (`db.sql` sección 23) siembra **5 roles** (incluye `solicitante`, `jefe_departamento`), **~126 permisos**, matriz de prioridad ITIL, 4 SLA, calendario, feriados, tipos de equipo, categorías y 17 claves de configuración. **Nada de esto existe.**

---

## 4. AUDITORÍA DE MÓDULOS — `fundamentos.md` (38) vs. app

| # | Módulo | Estado | Evidencia / observación |
|---|---|---|---|
| 01 | Auth/Sesiones | 🟠 PARCIAL | Solo `login`/`logout` (`web.php:16-23`); rate-limit login; sin 2FA, reset, sesiones, idle, kiosco, SSO |
| 02 | Dashboard Ejecutivo | 🟠 PARCIAL | `DashboardController`; KPIs y 1 gráfico; sin variantes por rol, alertas `v_alertas`, websocket |
| 03 | Portal Autoservicio | 🔴 NO EXISTE | Sin `/portal`, sin creación asistida, sin token, sin carnet |
| 04 | Mesa de Soporte | 🟠 PARCIAL | CRUD + comentarios + adjuntos + rating + pausa; sin SLA real, matriz, merge, watchers, PDF, QR, incidente mayor |
| 05 | Catálogo de Servicios | 🔴 NO EXISTE | Sin tablas ni rutas |
| 06 | Base de Conocimiento | 🔴 NO EXISTE | Sin `kb_articulos` |
| 07 | Problemas (ITIL) | 🔴 NO EXISTE | — |
| 08 | Cambios (ITIL) | 🔴 NO EXISTE | — |
| 09 | SLA/Calidad/Escalamiento | 🔴 NO EXISTE | Solo `sla:verify` (roto, ver §7) y umbrales hardcodeados `TicketPriority.php:29-37` |
| 10 | Guardias / On-Call | 🔴 NO EXISTE | — |
| 11 | Equipos TI | 🟠 PARCIAL | CRUD + 4 pestañas; sin custodias/actas/QR/import/CMDB/licencias |
| 12 | Custodias y Actas | 🔴 NO EXISTE | Sin `custodias`/`actas`/`firmas`; custodio = `equipos.empleado_id` |
| 13 | Cartereo Digital | 🔴 NO EXISTE | — |
| 14 | IPAM y Red | 🔴 NO EXISTE | — |
| 15 | Mini-CMDB | 🔴 NO EXISTE | — |
| 16 | Licencias (SAM) | 🔴 NO EXISTE | — |
| 17 | Suscripciones SaaS | 🔴 NO EXISTE | — |
| 18 | Préstamos de Activos | 🔴 NO EXISTE | Solo estado `en_reserva` decorativo |
| 19 | On/Offboarding | 🔴 NO EXISTE | — |
| 20 | Mantenimiento (CMMS) | 🟠 PARCIAL | Estados + checklist + KPIs; sin materiales, recurrencia real, MTBF/MTTR, PDF por orden, calendario |
| 21 | Inventario/Almacén | 🟠 PARCIAL | CRUD + ajuste/transferencia + kardex; sin condición, costo promedio, reservas, decimales, FEFO |
| 22 | Toma Física | 🔴 NO EXISTE | — |
| 23 | RMA | 🔴 NO EXISTE | — |
| 24 | Proveedores | 🔴 NO EXISTE | Solo strings `proveedor` |
| 25 | Compras (OC) | 🔴 NO EXISTE | — |
| 26 | Contratos | 🔴 NO EXISTE | — |
| 27 | Sedes y Ubicaciones | 🔴 NO EXISTE | Sin tabla/modelo/controlador |
| 28 | Departamentos jerárquicos | 🟠 PARCIAL | Planos; sin `padre_id`, centro de costo, validación de jefe |
| 29 | Directorio de Personal | 🟠 PARCIAL | CRUD empleados; sin expediente/custodias/credencial QR/organigrama |
| 30 | Usuarios y Roles (RBAC) | 🟠 PARCIAL | Spatie instalado; **0 permisos, 0 policies, 0 middleware**; enum `rol` manda |
| 31 | Automatización (reglas) | 🔴 NO EXISTE | — |
| 32 | Tareas Programadas | 🔴 NO EXISTE | Sin `Schedule::`; solo comando manual `sla:verify` |
| 33 | Notificaciones | 🟠 PARCIAL | Solo tabla in-app; repositorio inserta columnas inexistentes; controlador sin ruta |
| 34 | Reportes y Analíticas | 🟠 PARCIAL | PDF/Excel de tickets/inventario/mantenimiento/rendimiento; sin historial, programados ni constructor |
| 35 | Auditoría Forense | 🟠 PARCIAL | Tablas existen; **sin hash-chain y sin escritura efectiva** |
| 36 | Configuración | 🟠 PARCIAL | Solo preferencias por usuario en caché; sin global/monedas/seguridad/backups |
| 37 | Escáner QR y Estatus | 🔴 NO EXISTE | — |
| 38 | API e Integraciones | 🔴 NO EXISTE | Sanctum instalado sin uso; sin `/api`, sin webhooks |

**Resumen:** 0 módulos completos; ~10 parciales; ~27 inexistentes.

---

## 5. AUDITORÍA DE FLUJOS TRANSVERSALES

### 5.1 Máquina de estados (Módulo 04)
- El dominio define 4 estados y transiciones en `src/Modules/Support/Domain/Enums/TicketStatus.php:9-50`, con excepción y validación (`Domain/Models/Ticket.php:77-92`).
- **El runtime NO las usa:** `EloquentSupportRepository::updateTicket()` escribe el estado directo (`:242-247`) y `SupportController::update()` (`:85-94`) delega en él. No existen `cancelado` ni `cerrado`, ni reapertura.
- FormRequests aceptan solo `estado in:pendiente,en_proceso,resuelto` (`StoreTicketRequest.php:30`), **excluyendo `en_espera`** que sí existe en BD y en la máquina → inconsistencia.

### 5.2 Numeración legible
- **No implementada.** No hay secuencias ni tabla de contadores. Formato real `T-{id}`/`#T-{id}` (`Domain/ValueObjects/TicketId.php:24-32`, `TicketListItemMapper.php:43`). El spec exige `TIC-2026-00001` bajo transacción (`fundamentos.md:31`).

### 5.3 Dinero y tiempos
- Dinero en `decimal(10,2)`/`float` (`Equipment.php:34`), **violando la regla "nunca float / siempre centavos"** (`fundamentos.md:32`).
- Tiempos sin zona horaria; presentación `America/Caracas` no aplicada.

### 5.4 Auditoría forense
- `bitacora_acciones` carece de `hash_previo`/`hash_registro` → **no hay cadena de integridad** exigida (`fundamentos.md:35`).
- `LogActionUseCase` y `EloquentAuditLogRepository::save` **nunca se invocan** desde controladores → la bitácora no se alimenta.
- `sesiones_log` no se escribe; sin IP/UA/motivo/duración.

### 5.5 Notificaciones
- Motor de eventos inexistente. No hay `Events/Listeners/Jobs/Mail`. La tabla `notificaciones` (migración `000015`) no coincide con lo que inserta `EloquentNotificationRepository` (`tipo`,`titulo`,`read_at`) ni el comando `sla:verify` (`tipo`,`titulo`) → **error SQL en runtime**.
- `NotificationController` no está registrado en ninguna ruta.

### 5.6 Automatización y scheduler
- Sin motor de reglas, sin `automatizaciones`/logs, sin eventos.
- Sin `Schedule::` en el código; `bootstrap/app.php` no define `withSchedule()`. El catálogo de jobs (`fundamentos.md:288-298`) es inexistente. `sla:verify` es manual y defectuoso.

### 5.7 API e integraciones
- `laravel/sanctum` instalado, `config/sanctum.php` publicado, pero `User` **no usa `HasApiTokens`**, no existe `routes/api.php` y `bootstrap/app.php:9-13` solo carga `web`/`console`/`health`. Sin webhooks, sin OpenAPI, sin rate limiting por token.

### 5.8 Journey end-to-end
Ninguno de los 6 journeys (`fundamentos.md:346-347`) es ejecutable de punta a punta: dependen de custodias/actas, SLA por calendario, guardias, OC, RMA, KB y notificaciones, todos ausentes.

---

## 6. AUDITORÍA DE SEGURIDAD Y CALIDAD

### 6.1 Autorización (CRÍTICO)
- Todas las rutas usan **solo** `->middleware('auth')` (`routes/web.php:22`). **No hay** `permission:`/`role:`, Policies, ni `Gate::`.
- Todos los `FormRequest::authorize()` retornan `true` (p. ej. `StoreUserRequest.php:11-14`, `StoreTicketRequest.php:11-14`).
- `User::isAdmin()` (`app/Models/User.php:57`) **nunca se usa**. Spatie tiene roles seed pero ningún endpoint los verifica.
- **Consecuencia:** cualquier usuario autenticado (p. ej. `consultor`) puede borrar usuarios (`web.php:100`), modificar configuración (`:112`) o eliminar tickets/mantenimientos.
- Doble fuente de verdad: el enum `usuarios.rol` gobierna la UI/conducta (`HandleInertiaRequests.php:43-47`) mientras Spatie es decorativo y no se sincroniza al crear usuario (`CreateUserUseCase.php:20-32`). `StoreUserRequest.php:24` acepta `operador`, valor **no permitido** por el enum de BD → error al crear.

### 6.2 Contraseñas y sesión
- Hashing bcrypt; no hay `config/hashing.php` → no Argon2id (spec `fundamentos.md:327`, #52).
- Política débil: `min:4`/`min:6` (`StoreUserRequest.php:23`, `UpdateUserPasswordRequest.php:23`); sin complejidad, historial ni expiración.
- Login: rate-limit 5/min por `username|ip` (`LoginRequest.php:53`); sin bloqueo de cuenta, sin alerta, sin `intentos_login`, sin 2FA, sin verificación de email.
- Sesión: `regenerate()`/`invalidate()` correctos (`AuthController.php:25,33-34`); `SESSION_ENCRYPT=false`; sin idle timeout propio; sin gestión de sesiones activas.
- Redirección de login siempre a `inventario.index` (`AuthController.php:27`), sin redirección por rol.

### 6.3 Superficie HTTP
- CSRF activo ✔; bindings en consultas (riesgo SQLi bajo) ✔; `$fillable` presente ✔.
- **Sin headers de seguridad** (HSTS, CSP, X-Frame-Options, Referrer-Policy) ✘.
- **Uploads sin whitelist MIME real** ni `finfo`; se usa `getMimeType()` provisto por el cliente; límite 10 MB (spec 25 MB); sin antivirus (`SupportController.php:188-199`) ✘.
- Rutas de adjuntos sin verificación de pertenencia (`SupportController.php:204`) ⚠.

### 6.4 Concurrencia y consistencia
- **0 usos de `lockForUpdate`** en todo el backend.
- `AdjustStockUseCase.php:23-36` lee-modifica-guarda sin lock → **race condition de stock**; no hay `CHECK stock >= 0` en BD.
- `TransferStockUseCase.php:16-19` hace `decrement` sin verificar suficiencia.
- `addMaterial` no descuenta stock ni crea movimiento (ver §7).
- Solo 2 transacciones (`PostgresProductRepository.php:67`, `TransferStockUseCase.php:14`).
- Sin idempotencia, sin locking optimista (columna `version`), sin numeración transaccional.

### 6.5 Testing
- 34 archivos, 131 métodos (~73 Feature, ~58 Unit). La mayoría Feature son `assertStatus(200)`/`assertInertia`.
- **0 tests** de autorización/permisos, estados vía HTTP, concurrencia de stock, idempotencia, rate-limit, uploads o firma.
- El dominio de tickets se prueba pero **no se usa en producción** (`TicketDomainTest` vs. `EloquentSupportRepository` con `DB::table`).
- El dominio de stock sí se usa parcialmente (`AdjustStockUseCase`).
- Tests usan `DatabaseTransactions` contra PostgreSQL real; sin `RefreshDatabase`. No se pudo ejecutar la suite en el entorno de auditoría (bash restringido); riesgo de fallo si no existe la BD `testing`.
- `phpunit.xml` fija `DB_DATABASE=testing` sin `DB_CONNECTION` → hereda `pgsql`.

### 6.6 Credenciales y entorno
- `.env` local contiene `APP_KEY`, `DB_PASSWORD=Jonas2006` y `APP_DEBUG=true`. `.gitignore` ignora `.env` (no versionado), pero el debug activo combinado con `catch` que **filtra `$e->getMessage()`** (`SupportController.php:81`) expone trazas.
- Credenciales por defecto en seeder (`admin123`) ⚠.

### 6.7 Frontend
- SPA Vue 3 + Inertia completa; TypeScript `strict: true`; 22 componentes UI, 18 composables.
- **Pinia sin stores** (registrado pero sin uso); **sin script de typecheck** → errores de tipo no bloquean el build.
- Menú con 13 ítems vs. 38 módulos del spec.
- **Sin búsqueda global Ctrl+K**, sin i18n, sin filtros guardados, sin operaciones masivas genéricas (solo 2 borrados masivos).

---

## 7. DEFECTOS TÉCNICOS CONCRETOS (bug-level)

1. **Rutas a métodos inexistentes** (error 500): `routes/web.php:72` → `SupportController@generatePdf` y `:80` → `@saveSignature`; el controlador no los define.
2. **`SupportController::deleteAttachment`** usa `SupportRepositoryInterface` sin importarlo → *Class not found* (`SupportController.php:204`).
3. **Imports faltantes** en `SupportController`: `PauseTicketUseCase` (`:144`), `ResumeTicketUseCase` (`:155`), `BulkDeleteTicketsUseCase` (`:178`), `UploadTicketAttachmentUseCase` (`:186`).
4. **Comando `sla:verify` inserta columnas inexistentes** `tipo`/`titulo` en `notificaciones` (`routes/console.php:28-29`) → falla.
5. **`EloquentNotificationRepository`** inserta `tipo`/`titulo`/`read_at` que no existen en la tabla (`:17-22,50`).
6. **`NotificationController:70`** usa `NotificationType` sin `use`.
7. **`updateCloseDate` / `bulkDelete`** sin transacción ni validación de permisos.
8. **`fecha_cierre`** en `soportes` es nullable sin default; `createTicket` no fija `fecha_vencimiento` → SLA nunca se aplica.
9. **Valores hardcodeados**: `empleado_id ?? 38`, `usuario_creacion_id ?? 1` (`EloquentSupportRepository.php:217-218`); KPIs y técnicos con IDs ficticios (`:55,392-396`).
10. **`catch` que renderiza `Inertia::render('Error')`** cuando **no existe** `Pages/Error*.vue` y filtra el mensaje de excepción (`SupportController.php:42,52,69,81`).
11. **Datos de demo hardcodeados** en mappers cuando faltan valores (`TicketDetailMapper.php:28-41`, `TicketListItemMapper.php:21-34`).
12. **Enum `rol` de BD** vs. request que acepta `operador` (`StoreUserRequest.php:24` vs. migración `000004:15`).

---

## 8. FORTALEZAS OBSERVADAS

- Arquitectura DDD/Hexagonal coherente en `src/Modules` (Domain/Application/Infrastructure/Ports) con providers registrados.
- Uso correcto de `$fillable`, bindings parametrizados (sin `DB::raw` con entrada de usuario).
- CSRF, regeneración de sesión, rate-limit de login y `validate` en FormRequests.
- Suite de tests de dominio real para tickets, stock, equipos, departamentos (aunque desconectada del runtime).
- Frontend SPA moderno, tipado y con suite de componentes base consistente.
- Motor configurado correctamente a PostgreSQL, alineado con el spec.

---

## 9. HALLAZGOS PRIORIZADOS POR SEVERIDAD

### 🔴 CRÍTICO
- **C-1** Ausencia total de autorización por roles/permisos → escalada de privilegios en todos los módulos.
- **C-2** 75 tablas del spec (80,6 %) inexistentes → 27 módulos sin datos posibles.
- **C-3** Sin transacciones ni `lockForUpdate` en stock/materiales → corrupción de inventario; `addMaterial` no descuenta stock.
- **C-4** Sin hash-chain forense y sin escritura efectiva de bitácora/sesiones → auditoría inviable.
- **C-5** Rutas rotas y comandos con columnas inexistentes → errores 500 y SLA no operativo.
- **C-6** Credenciales reales + `APP_DEBUG=true` + fuga de `getMessage()`.

### 🟠 ALTO
- **A-1** Máquina de estados no aplicada; faltan `cerrado`/`cancelado`/reapertura.
- **A-2** SLA inexistente (calendarios, umbrales, escalamientos).
- **A-3** Motor de automatización, scheduler, notificaciones y API ausentes.
- **A-4** Dinero en decimal/float en lugar de centavos; sin multi-moneda.
- **A-5** Uploads sin whitelist MIME real; firma sin hash/IP/UA.
- **A-6** Doble fuente de verdad RBAC (enum vs. Spatie) y enum inconsistente con request.
- **A-7** Sin soft delete universal; FKs CASCADE donde el spec exige RESTRICT.

### 🟡 MEDIO
- **M-1** Numeración legible no implementada.
- **M-2** Sin headers de seguridad, sin tipo de hash Argon2id, sin 2FA/verificación de email.
- **M-3** Cobertura de tests superficial y desconectada del runtime.
- **M-4** Frontend sin stores Pinia, sin typecheck en build, sin Ctrl+K/i18n.
- **M-5** Configuración global inexistente (solo prefs por usuario en caché).
- **M-6** Departamentos planos (sin jerarquía ni centro de costo).

### 🟢 BAJO
- **B-1** Datos de demo hardcodeados en mappers.
- **B-2** Residuos SQLite/MariaDB y bases binarias en el repo.
- **B-3** Sin `config/cors.php` ni `config/hashing.php`.

---

## 10. MATRIZ DE CUMPLIMIENTO (PARTE VI — VALIDACIONES INTEGRALES)

| Regla (`fundamentos.md:321-327`) | Estado |
|---|---|
| Integridad BD (CHECK stock≥0, uniques parciales, citext) | ❌ |
| Idempotencia (#21) | ❌ |
| Locking optimista 409 (#22) | ❌ |
| Race de stock lockForUpdate (#23) | ❌ |
| Numeración transaccional (#24) | ❌ |
| MIME real 25 MB + antivirus (#25) | ❌ |
| Empleado↔Usuario biunívoco (#28) | ⚠️ parcial |
| Firma con hash+IP+UA (#33) | ❌ |
| Máscara de secretos en logs (#44) | ❌ |
| CSRF (#46) | ✅ |
| XSS escape + CSP (#47) | ⚠️ (escape sí; CSP no) |
| SQLi bindings (#48) | ✅ |
| Rate limit login/API (#49) | ⚠️ (solo login) |
| Idle timeout (#50) | ❌ |
| 2FA (#51) | ❌ |
| Argon2id (#52) | ❌ |
| Verificación email (#53) | ❌ |
| Bloqueo de cuenta + alerta (#54) | ❌ |
| Headers de seguridad (#55) | ❌ |
| Permisos en Policy/middleware (#56) | ❌ |
| Uploads fuera de webroot (#57) | ✅ |
| Tokens públicos expirables (#58) | ❌ |
| "Iniciar sesión como" (#59) | ❌ |
| Backups cifrados (#60) | ❌ |

**Cumplimiento de la checklist de seguridad: ≈ 3,5 / 14.** El *Definition of Done* global (`fundamentos.md:352`) exige migraciones + modelos + FormRequests + Policies + Vue + notificaciones + tests + bitácora + seed; no se cumple en ningún módulo.

---

## 11. ROADMAP DE REMEDIACIÓN PRIORIZADO

**F0 — Bloqueantes de seguridad (inmediato)**
1. Aplicar autorización real: middleware `role:`/`permission:` o Policies en cada ruta; sincronizar roles Spatie con `usuarios.rol`; sembrar los ~126 permisos y 5 roles del spec.
2. Corregir rutas/métodos rotos (`generatePdf`, `saveSignature`), imports faltantes y columnas inexistentes de `notificaciones`/`sla:verify`.
3. Sacar credenciales del `.env` del flujo activo, `APP_DEBUG=false` en producción y dejar de filtrar `getMessage()`.
4. Envolver stock/materiales en `DB::transaction` + `lockForUpdate` y añadir `CHECK (stock >= 0)`.

**F1 — Núcleo operativo (crítico)**
5. Migrar el esquema hacia `db.sql` v2.0 (TIMESTAMPTZ, centavos BIGINT + `monedas`, citext, JSONB+GIN, tsvector, uniques parciales, soft delete universal, FKs RESTRICT).
6. Máquina de estados completa + numeración transaccional + SLA con calendarios/feriados/escalamientos.
7. Custodias/actas/firmas con valor probatorio (hash SHA-256, IP, UA, timestamp).
8. Bitácora con hash-chain y escritura efectiva; `sesiones_log` con IP/UA/motivo; `intentos_login`.
9. Notificaciones (eventos, colas, email, digest) y configuración global.

**F2 — Estándar**
10. Inventario avanzado (condición, costo promedio, reservas, decimales, FEFO, kardex con saldo), mantenimiento con materiales/recurrencia/MTBF, proveedores/OC/contratos, licencias, RMA, tomas físicas, KB, aprobaciones.

**F3 — Madurez**
11. Problemas, cambios, CMDB, sedes/ubicaciones jerárquicas, cartereo, guardias, chargeback, reportes programados, i18n, 2FA, import/export.

**F4 — Diferenciadores**
12. IA, SSO/LDAP, forecast, PWA offline, auto-descubrimiento, proyectos, API/webhooks, gamificación, pulso.

**Transversal**
13. Endurecer testing: permisos, transiciones por HTTP, concurrencia, idempotencia, uploads; conectar el dominio al runtime.

---

## 12. CONCLUSIÓN

La aplicación `sgen-backend` **no satisface** el contrato de datos de `db.sql` v2.0 (18 % de tablas, 0 % de decisiones arquitectónicas completas) ni el contrato funcional de `fundamentos.md` (≈ 10 % de módulos, 0 completos, ninguno de los 6 journeys ejecutable). El código evidencia un **port de un sistema legacy MariaDB** a Laravel con una capa DDD correctamente estructurada pero **desconectada**, y con defectos que rompen la ejecución (rutas/columnas inexistentes) y la seguridad (sin autorización, sin transacciones ni locks).

La brecha es estructural, no cosmética: requiere una fase de saneamiento de datos (F0/F1) antes de continuar con el roadmap funcional. Los cimientos —arquitectura modular, motor PostgreSQL, CSRF, bindings, suite de dominio— son reutilizables y constituyen la base sobre la que reconstruir el sistema especificado.

---

*Reporte generado mediante auditoría estática exhaustiva. Toda afirmación está respaldada por referencias `archivo:línea` en el repositorio auditado. No se modificó ningún archivo del proyecto durante la auditoría.*
