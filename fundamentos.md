Documento Maestro de Producto: módulos, procesos, flujos, validaciones, notificaciones, automatización e innovación, de punta a punta
Alcance: Este documento consolida y define TODO lo necesario para que la aplicación quede completa: 38 módulos, 6 journeys extremo a extremo, máquina de estados de 10 entidades, 120+ validaciones de negocio, matriz de 60+ notificaciones, motor de automatización, capa IA, capa de innovación diferencial, seguridad de punta a punta, API e integraciones. Cada módulo define: rutas, pantallas, flujo paso a paso, reglas, eventos y su enlace con el esquema PostgreSQL v2.0 (86 tablas) ya entregado.

PARTE I — FUNDAMENTOS
1. Actores y Roles (RBAC editable, tabla roles + usuario_roles + rol_permisos)
Rol	Definición operativa	Es sistema
admin	Control total. Gestiona roles/permisos, configuración global, sede, usuarios, y puede ejecutar cualquier acción en cualquier módulo.	✔
tecnico	Ciclo operativo completo: tickets asignados, consumos, mantenimientos, actas, inventario (movimientos), KB (escritura).	✔
consultor	Lectura + exportación en todos los módulos analíticos. No altera estados.	✔
solicitante	Portal de autoservicio: crea tickets/solicitudes, ve sus custodias, firma, califica, responde cartereo.	✔
jefe_departamento	Aprobaciones de su área (cascada de jerarquía), ve consumo TI (chargeback) de su departamento.	configurable
Coordinador de Mantenimiento, Auditor externo, etc.	Roles creados desde /roles con permisos a la carta.	creados por admin
Reglas RBAC: un usuario puede tener N roles (unión de permisos). Todo endpoint valida $user->tienePermiso('modulo.accion') vía Policy + middleware. El permiso * (superadmin) existe pero NO se otorga por UI normal. La denegación genera bitácora acceso_denegado con hash-chain.

Alcance por sede: técnicos y jefes tienen sedes de cobertura; los tickets/mantenimientos se asignan por sede del equipo; consultores sin sede ven todo.

2. Máquina de Estados Central (única fuente de verdad en Enums PHP + validación BD)
Ticket (estado_ticket):

pendiente → en_proceso | cancelado
en_proceso → en_espera | resuelto | cancelado
en_espera → en_proceso | cancelado
resuelto → cerrado (autocierre N días) | en_proceso (reapertura)
cerrado → en_proceso (reapertura tardía, requiere motivo + crea nuevo vínculo SLA)
cancelado → terminal
Toda transición ilegal → EstadoInvalidoException + registro de intento. Toda transición legal → evento + bitácora + recálculo SLA.

Mantenimiento: pendiente → en_proceso → completado; pendiente → pospuesto → pendiente; → cancelado desde cualquier no-terminal. Compra: borrador → enviada → parcialmente_recibida → recibida | cancelada. Cambio: borrador → en_evaluacion → aprobado → en_implementacion → implementado | fallido | rechazado. Solicitud: enviada → pendiente_aprobacion → aprobada → en_proceso → completada | rechazada. RMA: solicitado → enviado → en_proceso → reemplazado | rechazado → cerrado. Acta: borrador → emitida → firmada | anulada. Préstamo: activo → devuelto | vencido | extraviado. Toma física: abierta → en_conteo → conciliada → cerrada. Cartereo: pendiente → confirmada | discrepancia | sin_respuesta.

3. Convenciones universales del sistema
Numeración legible transaccional: TIC-2026-00001 (tickets), MAN-, OC-, ACT-, RMA-, SOL-, PRB-, CHG-, CAR-, TF-, PRE-, INC-. Generadas vía secuencia PostgreSQL bajo transacción (imposible duplicar).
Dinero: siempre BIGINT centavos + tabla monedas + tasas_cambio diaria. Presentación según configuracion('sistema.moneda'). Nunca float.
Tiempos: TIMESTAMPTZ UTC en BD; presentación America/Caracas (configurable por sede). SLA laborales calculados sobre calendarios + dias_festivos.
Soft delete universal (deleted_at) + papelera con ventana de 30 días + restauración auditada.
Auditoría hash-chain: TODO crear/actualizar/eliminar/reasignar/pausar/reanudar/firmar/exportar/login/logout/acceso_denegado/aprobacion genera fila en bitacora_acciones con hash_previo + hash_registro (SHA-256, advisory lock). Comando sgen:verificar-bitacora programado diariamente.
Archivos: tabla archivos polimórfica (validación MIME real server-side contra whitelist, máx 25MB, checksum SHA-256, antivirus ClamAV opcional). Nunca se loguea contenido binario.
Idempotencia: toda acción POST destructiva/creativa acepta Idempotency-Key (Middleware) — doble clic no duplica.
Locking optimista: columnas version en entidades editables (equipos, tickets, items): conflicto → HTTP 409 con diff visual.
Búsqueda global Ctrl+K: índice unificado (módulos, tickets, equipos, empleados, KB, acciones). Full-text tsvector en español para tickets y KB.
Tags libres (tags + entidad_tags) cruzando cualquier entidad.
i18n: es por defecto (archivos Laravel lang/ + vue-i18n), listo en.
Filtros guardados: cualquier vista filtrada se guarda con nombre; "cargar preset" por usuario.
Exportación universal: todo BaseDataTable tiene export CSV/XLSX con los filtros activos aplicados.
Operaciones masivas: selección múltiple → asignar/reasignar/cerrar/cambiar categoría/transferir, con confirmación tipo "Estás por cerrar 23 tickets".
PARTE II — MÓDULOS DE NÚCLEO OPERATIVO
MÓDULO 01 · Autenticación, Sesiones y Accesos
Rutas: GET /login · POST /login · POST /logout · GET /forgot-password · POST /forgot-password · GET /reset-password/{token} · POST /reset-password · GET /2fa · POST /2fa/verify · GET /sesiones · DELETE /sesiones/{id} · GET /kiosk/{token}

Flujo de login:

Usuario ingresa username + password. IntentoLogin::registrar() (IP + user agent) SIEMPRE.
Sistema valida lockout: intentos_fallidos >= configuracion('seguridad.intentos_maximos') y bloqueado_hasta futuro → rechazo con mensaje genérico ("Credenciales inválidas o cuenta bloqueada temporalmente" — nunca revelar cuál).
Credenciales válidas → si dosfa_activo: redirige a /2fa con código TOTP (ventana 30s, 3 códigos de respaldo de un solo uso).
Éxito → SesionLog::iniciar() (IP, user agent) + usuarios.registrarAcceso() + bitácora login + redirección por rol (tecnico→dashboard operativo; solicitante→portal).
Fallo → registrarIntentoFallido(); al 5º intento → bloqueo 15 min + email de alerta al usuario.
Recuperación de contraseña: email (tomado de empleados.email vinculado) → token firmado expira 60 min (1 uso) → política validada al setear: longitud ≥ config, complejidad (mayús/minús/número/símbolo), no en historial de últimas 5 (password_histories), no igual a username/email/nombre → password_cambiado_el actualizado + invalidación de todas las demás sesiones.

Gestión de sesiones (/sesiones): lista de sesiones activas del usuario con IP/UA/duración en vivo, "Cerrar esta sesión", "Cerrar todas las demás", límite configurable de sesiones simultáneas. Cierres registrados con motivo_cierre=forzado.

Idle timeout: middleware EnsureSessionIdle — última actividad > 30 min (config) → cierre con motivo_cierre=inactividad. Actividad = cualquier petición autenticada.

Modo Kiosk (/kiosk/{token}): token emitido por admin para una tablet (recepción/taller); sin login, solo permite: firmar actas de entrega pendientes de esa ubicación y escanear QR. Sesión restringida con expiración.

SSO/LDAP (Fase 4): OIDC/SAML (Microsoft Entra, Google Workspace) + sync de directorio (importa empleados de AD, mapea departamento por OU). Login local deshabilitable por sede.

MÓDULO 02 · Dashboard Ejecutivo
Ruta: GET /dashboard — El ya especificado en el HTML entregado (KPIs 8, 4 gráficos, feed, alertas v_alertas, guardia, incidente mayor activo, aprobaciones, mis tickets con SLA vivo, quick actions). Variantes por rol: admin ve todo + estado del sistema; técnico ve SU bandeja + agenda; consultor ve analítica sin acciones; jefe ve chargeback de su área. Refresco por polling 60s + Echo (websocket) en tiempo real cuando el canal está activo.

MÓDULO 03 · Portal de Autoservicio
Rutas: GET /portal · GET /portal/mis-tickets · GET /portal/mis-tickets/{id} · GET /portal/mis-custodias · GET /portal/mis-solicitudes · GET /portal/carnet · GET /portal/cartereo/{token} (sin login, token)

Pantallas:

Home del portal: mis tickets activos (timeline con estados públicos), mis solicitudes, mis custodias (carnet digital con valor y código QR verificable), accesos rápidos al catálogo de servicios, buscador KB.
Crear ticket asistido: 4 pasos — (1) seleccionar equipo: escaneo QR con cámara o búsqueda por código/serial (solo SUS equipos; botón "no encuentro mi equipo" → crea ticket genérico), (2) categoría (autodetectada por IA, ver Parte VII) + descripción con editor + adjuntos, (3) deflection KB en vivo: al escribir, búsqueda full-text muestra "3 artículos podrían resolver esto" con contadores de deflexión, (4) detección de duplicados: fuzzy match sobre tickets abiertos del mismo equipo con similitud >75% → "Ya existe TIC-2026-00094 con esta falla, ¿unirte como watcher o crear nuevo?".
Seguimiento: timeline pública (comentarios no-internos ocultos al solicitante), SLA visible ("Te responderemos antes de las 14:30"), botones: agregar comentario, agregar adjunto, cancelar (si pendiente), calificar y firmar al resolverse (canvas táctil + estrellitas 1–5), reabrir (si dentro de ventana de 7 días con motivo obligatorio).
Reglas: el solicitante solo ve tickets donde es solicitante_empleado_id o watcher. Los confidenciales no aparecen al solicitante salvo que él lo sea. Cartereo con token funciona sin sesión (empleado confirma desde su teléfono con firma).

MÓDULO 04 · Mesa de Soporte (Helpdesk) — el corazón
Rutas: CRUD /soportes + /soportes/{id}/asignar|pausar|reanudar|resolver|cerrar|cancelar|reabrir|calificar|firma|reasignar|comentarios|materiales|horas|watchers|merge|vincular-problema|publicar-kb|enlace-publico + GET /soportes/{id}/pdf + bandejones por rol.

4.1 Flujo de vida del ticket — PASO A PASO
PASO 0 · Ingreso multicanal: canal_ingreso ∈ portal | teléfono (técnico registra en nombre de, marcando creado_por≠solicitante) | email (email-to-ticket: alias soporte@empresa → parser extrae remitente→empleado match por email, adjuntos→archivos, firma automática de la conversación; respuesta por email agrega comentario público) | presencial | API | Teams/Slack bot | monitoreo (Zabbix webhook crea ticket crítico auto-vinculado al equipo por IP).

PASO 1 · Creación: Soporte::abrir() en transacción: (a) numeración secuencial, (b) prioridad DERIVADA de matriz Impacto×Urgencia (tabla matriz_prioridad; UI muestra la matriz al usuario y le deja anular con justificación bitacorada), (c) VIP check: empleado.es_vip → urgencia mínima media y flag, (d) SLA aplicado (SlaDefinicion::aplicar(prioridad, categoria)) → fecha_vencimiento_respuesta y fecha_vencimiento_resolucion calculadas SOBRE calendario laboral (salta noches/fin de semana/feriados), (e) detección de duplicados (fuzzy: trigram sobre títulos+descripción de tickets abiertos del mismo equipo), (f) auto-asignación por reglas de automatización (Parte V), fallback: guardia de turno, fallback 2: técnico con menos carga (v_kpi_tecnico.abiertos mínimo), (g) evento TicketCreado → notificaciones.

PASO 2 · Asignación: bandeja "Sin asignar" (solo técnicos/admin). asignar(tecnico) fija fecha_asignacion (si es primera → cuenta FRT), notifica al técnico con deeplink, comenta automáticamente en timeline. Reasignación posterior requiere motivo (bitácora reasignar con técnico anterior/nuevo), el SLA NO se resetea (el vencimiento es del ticket, no del técnico).

PASO 3 · Atención: técnico marca en_proceso → TicketHora inicia cronómetro manual o automático (EXCLUDE anti-solapamiento garantiza que un técnico no registre dos trabajos simultáneos). Diagnóstico en comentarios internos (invisibles al portal). Plantillas de respuesta con variables {{cliente}} {{equipo}} {{codigo}}. Panel "tickets similares de este equipo" (consultado por categoría+equipo histórico) para no rediagnosticar. Macro-acciones en 1 clic (estado+asignación+plantilla+comentario encadenados).

PASO 4 · Consumo de materiales: POST /materiales → InventarioMovimiento::registrar(tipo=consumo, lockForUpdate) ATÓMICO: valida stock con bloqueo de fila (race-condition imposible), descuenta ubicación+condición, filas espejo en inventario_consumos con costo promedio ponderado, actualiza costo del ticket. Confirmación muestra costo unitario y stock resultante. Si stock bajo resultante → dispara alerta.

PASO 5 · Pausas: pausar(motivo, minutos) → estado en_espera, tiempo_pausado_minutos acumula, fecha_vencimiento_resolucion se extiende exactamente los minutos pausados (si sla.cuenta_en_pausas=false). Motivo obligatorio, seleccionable de catálogo (en espera del cliente / pieza en RMA / escalado a proveedor / otro). El portal del solicitante ve "En espera del cliente" con aviso "tu respuesta es necesaria".

PASO 6 · Resolución: resolver(solucion) → estado resuelto, fecha_resolucion, tiempo_atencion_minutos recalculado neto de pausas. Botón "Publicar solución como artículo KB" → prefill de kb_articulos con origen vinculado. Follow-up programado: job a +7 días envía encuesta de persistencia ("¿La solución se mantuvo?") — si responde "No" → oferta de reapertura en 1 clic.

PASO 7 · Validación del cliente: portal: firma canvas (guardada como archivo + firmas con hash SHA-256, IP, UA, timestamp — valor probatorio) + calificación 1–5 + comentario. Ambos opcionales pero habilitan autocierre. Cualquier comentario del solicitante en ventana de reapertura la reabre automáticamente.

PASO 8 · Autocierre: job diario: resueltos sin reapertura por tickets.autocierre_dias (7) → cerrado + comentario sistema + notificación de cortesía.

PASO 9 · Cierre manual/administrativo: cerrado es terminal para SLA. Reapertura desde cerrado requiere permiso soportes.editar + motivo + abre SLA nuevo.

PASO 10 · Documento PDF: GET /pdf genera acta de servicio membretada (logo/moneda de configuracion): datos del equipo, diagnóstico, solución, materiales con costos, horas, firmas (imágenes hash-verificadas), QR de verificación del ticket, hash SHA-256 del PDF impreso al pie (verificable en /verificar/{hash}).

Incidente mayor (flag es_incidente_mayor): banner global rojo en TODA la app mientras activo, ticket padre que absorbe hijos (ticket_padre_id), timeline broadcast con actualizaciones masivas que notifican a todos los watchers de hijos de una vez, resolución del padre resuelve (o cierra) los hijos con plantilla de causa raíz, auto-link a problemas post-mortem. Comunicación a afectados: los watchers reciben cada broadcast por email + portal.

Fusionar: merge → hijos pasan a cancelado con comentario "fusionado en X", comentarios/adjuntos/consumos migran al padre, solicitantes se agregan como watchers. Vincular a problema: N tickets → 1 problemas con análisis de causa raíz.

Watchers/CC: agregar cualquier usuario como observador (notificado de todo, sin poder de edición). Enlace público: token_publico → URL para proveedor externo ve timeline pública filtrada (sin internos ni costos), expiración configurable.

Confidencial: visible solo para técnico asignado + admins (excluido de búsquedas, dashboards agregados y exportaciones de otros técnicos).

4.2 Validaciones del módulo
Equipo debe existir y NO estar de_baja (warning si fuera_de_servicio).
reabrir: solo dentro de tickets.dias_reapertura desde fecha_resolucion.
calificar: solo solicitante o jefe, una vez, 1–5.
Materiales: cantidad > 0 y ≤ stock disponible en ubicación elegida (lockForUpdate).
Horas: fin > inicio, no futuro, no solapadas (motor EXCLUDE), solo técnico asignado/admin.
Comentarios internos: nunca exportados en PDF del cliente, nunca visibles en portal ni token público.
Cambio de estado: siempre por máquina de estados; UI deshabilita botones inválidos y servidor lo revalida.
Email-to-ticket: rate limit por remitente (5/hora), adjuntos ≤ 25MB totales, remitente desconocido → cola de triage admin.
4.3 Notificaciones del módulo
creado→solicitante+guardia · asignado→técnico · primera respuesta→solicitante · comentario público→contraparte+watchers · comentario interno→solo técnicos · estado cambiado→solicitante+watchers · resuelto→solicitante (con link firma/calificación) · SLA al 50/80/100%→técnico+escalados · escalado→nivel superior · reapertura→técnico+supervisor · confidencial excluido de digests masivos.

MÓDULO 05 · Catálogo de Servicios y Solicitudes
Rutas: /catalogo (público al portal) · CRUD admin /catalogo/admin · /solicitudes (bandeja del solicitante) · /solicitudes/{id}/aprobar|rechazar|procesar · bandeña aprobadores /aprobaciones

Flujo: (1) Solicitante elige servicio ("Solicitar mouse inalámbrico"); formulario dinámico definido en formulario JSONB (validación server-side por tipo). (2) Si requiere_aprobacion → estado pendiente_aprobacion → aprobador(es): jefe de su departamento (regla aprobador_rol_id), con escalado si no responde en 48h (configurable) al nivel superior de la jerarquía. (3) Aprobada → procesar() transaccional: por cada catalogo_servicio_items verifica stock en bodega de la sede del solicitante (si insuficiente → estado en_proceso con nota de reposición y alerta de compra sugerida) y descuenta (salida con referencia solicitud); si crea_ticket materializa ticket de entrega vinculado; notifica al solicitante "pasa por almacén". (4) Rechazada → motivo visible al solicitante. (5) Todo con codigo SOL- y bitácora.

Casos especiales: solicitud de acceso (no descuenta stock, crea ticket de accesos); préstamo de equipo (crea prestamos); activo nuevo (crea flujo de adquisición). Pantalla del aprobador: tarjeta con respuestas del formulario, botones Aprobar/Rechazar con comentario, y vista de su bandeja agregada por jerarquía (jefe ve pendientes de todo su subárbol).

MÓDULO 06 · Base de Conocimiento
Rutas: /kb · /kb/{slug} · /kb/categoria/{id} · CRUD admin /kb/admin · /kb/{id}/calificar · búsqueda /kb/buscar?q=

Definición de operación: editor Markdown con preview, imágenes embebidas vía archivos, visibilidad publico (portal) o solo_tecnicos (documentación interna/procedimientos), versionado (kb_revisiones guarda diff por edición), estados borrador/publicado/archivado. Rating de utilidad (thumbs por artículo con agregado visible, ordena resultados). Deflection medido: cuando un usuario abre un artículo sugerido durante la creación de un ticket y NO crea el ticket en 30 min → cuenta como "deflexión" (KPI de autoatención). Sugerencia al técnico: durante diagnóstico, panel lateral con top-3 artículos de la categoría. Publicación desde cierre: 1 clic pre-llena con solucion. Envejecimiento: artículos sin vistas en 12 meses → alerta de revisión al owner.

MÓDULO 07 · Gestión de Problemas (ITIL)
Rutas: /problemas CRUD · /problemas/{id}/vincular-ticket|vincular-equipo|resolver

Flujo: patrón detectado (o detección automática: ≥3 tickets del mismo equipo+misma categoría en 30 días → sugerencia de problema al coordinador) → se crea PRB- → investigación (asignado a, RCA con editor, workaround documentado) → si tiene workaround y sigue abierto → es_error_conocido=true (aparece sugerido en tickets similares) → resolución cierra y ofrece generar cambio (para fix de fondo) + artículos KB. Vínculos N:1 con incidentes y N:M con equipos. KPI: nº de problemas, incidentes evitados por workaround, tiempo de RCA.

MÓDULO 08 · Gestión de Cambios (ITIL)
Rutas: /cambios CRUD · /cambios/{id}/aprobar|implementar|rollback|cancelar · /cambios/calendario

Tipos: estandar (pre-aprobado, plantillas: "reiniciar servicio X") sin aprobación; normal con ventana y aprobadores; emergencia (path rápido: 1 aprobador senior + retrospectiva obligatoria).

Flujo normal: RFC con plan de implementación, plan de rollback OBLIGATORIO, riesgo (bajo/medio/alto), equipos impactados (cambio_equipos — muestra análisis de dependencias del mini-CMDB: "impacta a 23 equipos conectados"), ventana de tiempo → evaluación por aprobadores (lista mínima por riesgo: 1 si bajo, 2 si medio, CAB si alto) → aprobado → en ventana: en_implementacion → checklist de pasos → implementado (+bitácora de cada paso) o fallido → ejecuta rollback documentado. Calendario de cambios: vista mensual con ventana de cambios congeladas (negras si conflicto con otra ventana sobre mismos equipos — validación de solapamiento de cambio_equipos).

MÓDULO 09 · SLA, Calidad y Escalamiento
Rutas: /sla (CRUD definiciones) · /sla/calendarios · /sla/feriados · /sla/escalamientos/{def}/... · /sla/cumplimiento (tablero)

Definido: por prioridad, refinable por categoría (la más específica gana). Dos umbrales: primera respuesta y resolución; pausable o no. Calendarios: horarios por día + feriados por país; una definición puede usar calendario global o por sede (multi-país soportado). Escalamiento: reglas por definición — al 80% de vencimiento: notificar técnico; al 100%: notificar jefe de técnicos + reasignar según regla; al 150%: notificar a dirección + marca de incumplimiento severo. Cada ejecución en escalamientos_log. Tablero de cumplimiento: % por prioridad/categoría/técnico/mes, tendencia, FRT promedio, MTRS, top vencidos. Los cierres con reincidencia no cuentan como cumplidos (regla de honestidad de métrica).

MÓDULO 10 · Guardias / On-Call
Rutas: /guardias CRUD + /guardias/calendario · API interna Guardia::deTurnoAhora(sede?)

Definido: escala rotativa (UI de asignación rápida semanal con drag&drop), niveles tier1/tier2, por sede, EXCLUDE anti-solapamiento a nivel BD (un técnico jamás tiene dos guardias solapadas — error 23P01 traducido a mensaje claro). Consumo: el auto-assign de tickets críticos respeta la guardia de la sede del equipo; si no hay guardia → técnico con menor carga. Delegación: técnico ausente delega su bandeja a colega (rango de fechas): durante ese periodo sus tickets nuevos le llegan al delegado, con nota visible. Confirmación de relevo: el entrante debe "aceptar el turno" (evita guardias fantasma). Dashboard y topbar muestran guardia activa + countdown de relevo. Integración conTeams/Slack opcional.

PARTE III — MÓDULOS DE ACTIVOS
MÓDULO 11 · Equipos TI (Activos) — ciclo de vida patrimonial completo
Rutas: CRUD /equipos + /equipos/{id}/reasignar|baja|etiqueta-qr|duplicar|historial · /scan/{codigo} · /equipos/importar · /equipos/exportar · /equipos/{id}/fotos · /equipos/{id}/relaciones · /equipos/{id}/licencias

Ficha (Show.vue) — pestañas: Resumen (hero: código patrimonial QR, serial, tipo, marca/modelo, ubicación jerárquica rutaCompleta(), sede, custodio actual con acta vinculada, estado, condición) · Especificaciones (plantilla JSONB por tipo_equipo: CPU/RAM/disco/MAC/IP/OS + extras por tipo — toner en impresoras, puertos en switches) · Custodias (historial completo con actas y firmas) · Tickets (todos, con conteo de reincidencias) · Mantenimientos (histórico + próximas + MTBF/MTTR + costo acumulado vs valor — indicador "reparar o reemplazar" cuando costo acumulado >50% del valor de reposición) · Licencias instaladas · Compras (de qué OC salió) · Relaciones CMDB (grafo interactivo de dependencias, con análisis inverso: "si este switch cae, afecta a…") · Archivos y fotos (facturas, manuales, galería con lightbox) · Historial (bitácora filtrada a la entidad).

Reasignación de custodia (flujo): modal → busca empleado destino (warning cruzado si departamento distinto al del equipo: requiere confirmación explícita) → cierra custodia anterior + crea nueva + genera acta entrega_custodia → flujos de firma: (a) inmediata: el empleado firma en el portal; (b) presencial: técnico firma en tablet/kiosk y el empleado firma después; (c) ambas firmas → acta.estado=firmada → PDF archivado. Kits: entregar paquete (laptop+dock+monitor+mouse) genera UNA acta con N equipos.

Baja de activo (flujo irreversible controlado): (1) validaciones: sin custodia activa (debe recuperarse antes), sin mantenimientos pendientes, sin licencias activas (se sugiere liberarlas), préstamos devueltos. (2) wizard: motivo (robo/obsolescencia/donación/venta/desecho), valor de recuperación, destino. (3) Borrado certificado de datos: checklist de saneamiento (DoD 5220.22-M simple de 1 pasada) + responsable técnico + firma. (4) acta baja_equipo firmada por TI y por el responsable patrimonial → estado de_baja, fecha_baja, motivo_baja; el serial queda libre para reingreso con NUEVO código patrimonial; el equipo desaparece de vistas operativas pero la historia se conserva íntegra (jamás se borra). (5) Certificado de borrado PDF con hash.

Leasing: flag + fecha fin contrato → alertas 90/60/30 días + flujo de devolución con checklist. Remotos: ubicación "domicilio del empleado" (tipo especial) + flujo logístico con acta de envío y guía.

Etiquetas QR: GET /etiqueta-qr → PNG (código patrimonial + serial + URL /scan/...); impresión masiva por lote filtrado. /scan/{codigo}: móvil → reconoce equipo → ficha compacta con botones: reportar falla (prefill), iniciar mantenimiento, transferir (con permiso).

Importación CSV: wizard de 4 pasos: subir → mapeo de columnas con auto-detección → validación completa en memoria (reporte de errores descargable, fila a fila: IP repetida, serial duplicado, depto inexistente) → commit por lotes transaccionales con resumen (creados/omitidos/advertencias). Plantillas descargables. Lo mismo para empleados e inventario.

Auto-descubrimiento ✨: agente opcional o escaneo SNMP programado por subred → "equipos detectados no registrados" con datos propuestos (IP, MAC, hostname, fabricante) → aprobar → pre-llena el alta. Deduplicación por MAC.

MÓDULO 12 · Custodias y Actas
Ya definido en el corazón de Equipo + cartereo. Tablas: custodias (única fuente de verdad del custodio actual, índice parcial única por equipo), actas + acta_detalles, firmas (hash + IP + UA + timestamp = valor probatorio: cualquier acta puede verificarse en /verificar-acta/{codigo} recomputando el hash del archivo contra el registro).

MÓDULO 13 · Cartereo Digital de Custodias ✨ (diferenciador)
Rutas: /cartereo CRUD · /cartereo/{id}/lanzar|recordatorio|cerrar · /portal/cartereo/{token} · /cartereo/{id}/reporte

Flujo anual completo: (1) Admin crea campaña CAR-2026-001 con alcance (sede/departamento). (2) Lanzar: para cada empleado con custodias activas → cartero_asignaciones con token único + notificación email/portal "Confirma tus equipos antes del 31/10". (3) Empleado responde sin login (token): lista de sus activos con foto y specs → confirma cada uno o marca discrepancia ("ya lo devolví", "está dañado", "no lo reconozco") + firma digital de la declaración jurada. (4) Discrepancias → cola de investigación para TI (resolución con acta correctiva). (5) Recordatorios automáticos a no respondientes (7d, 3d, 1d). (6) Cierre: reporte de campaña: % confirmado, % discrepancia, % sin respuesta; actas masivas generadas y firmadas quedan archivadas. KPI institucional listo para auditoría externa.

MÓDULO 14 · IPAM y Red
Rutas: /red/subredes CRUD · /red/ips (mapa de ocupación) · conflictos.

Definido: subredes por sede con CIDR/VLAN/gateway; IPs ocupadas se derivan de equipos.direccion_ip (unique parcial — duplicado imposible a nivel BD); vista de subred: grid de IPs con estado (libre/asignada/reservada/según equipo con deeplink); reservas manuales con motivo; detección de conflicto en importaciones; sugerencia de próxima IP libre al dar de alta. Opcional: sweep ping programado con reporte de IPs "vivas" no registradas.

MÓDULO 15 · Mini-CMDB
/equipos/{id}/relaciones CRUD + grafo. Tipos: conectado_a (PC→switch), contenido_en (módulo→chasis), usa (servidor→SAN), respaldo_de, depende_de. Análisis de impacto (2 direcciones): hacia abajo (a qué afecta que este caiga — BFS sobre conectado_a+depende_de), hacia arriba (de qué depende). Se usa en: incidentes mayores (identificar blasting radius), cambios (validar impacto), planificación. Validaciones: no auto-referencia, no duplicado, no ciclos simples directos.

MÓDULO 16 · Licencias y Software (SAM)
Rutas: /licencias CRUD · /licencias/{id}/asignar|liberar · alertas de expiración en v_alertas.

Definido: licencias con tipo (perpetua/suscripción/volumen/OEM), total de asientos, asignación a equipo o empleado (checado a nivel BD), control de sobre-asignación: asientosDisponibles() y la UI + BD impiden asignar más allá de lo comprado; estado excedida si el total baja bajo lo asignado. Alertas 90/60/30 días a vencimiento con responsable. Offboarding libera asientos automáticamente (paso del checklist). Costos agregados por departamento (chargeback). Campo clave de producto cifrado a nivel app (visible solo a admin con permiso licencias.claves).

MÓDULO 17 · Suscripciones SaaS ✨
Rutas: /suscripciones CRUD + asignaciones de seats.

Definido: cada SaaS (Microsoft 365, Adobe, antivirus) con costo/usuario/mes, ciclo, renovación, seats totales vs asignados (por empleado), facturación proyectada (seats × costo × meses), costo por departamento, alerta de renovación 60/30/7, detección de "seats huérfanos" (empleados con seat asignado sin actividad — si hay integración) y del offboarding: al desvincular empleado, sus suscripciones aparecen en el checklist para revocar. KPI: gasto SaaS total mensual, top servicios, evolución.

MÓDULO 18 · Préstamos de Activos
Rutas: /prestamos CRUD · /prestamos/{id}/devolver|extraviar · /prestamos/calendario.

Flujo: reservar equipo disponible → acta prestamo con firma → estado del equipo prestado → devolución con acta de devolución y verificación de condición (daño → genera cargo/acta de daño + posible ticket de reparación) → recordatorio automático 1 día antes del vencimiento; al vencer sin devolución → estado vencido + escalamiento al jefe. Extraviado → flujo de baja por robo/pérdida vinculado.

MÓDULO 19 · Ciclo de Vida del Personal (On/Offboarding)
Rutas: /personal/{id}/onboarding · /personal/{id}/offboarding · /checklists/plantillas CRUD · /checklists (tablero).

Plantillas por tipo (onboarding/offboarding) y opcionalmente por departamento. Tareas tipadas (preparar_equipo, crear_usuario, asignar_licencia, asignar_suscripcion, entregar_credencial, recuperar_custodias, revocar_accesos, retirar_equipos, otro) con dias_offset respecto a fecha efectiva (pre-boarding de -5 días permitido).

Onboarding: fecha de ingreso → instancia materializada con todas las tareas → tablero kanban para TI → cada tarea puede ejecutarse con deeplink (asignar custodia → modal de reasignación prellenado; crear usuario → form prellenado) → vínculo automático a la entidad creada (referencia_tipo/referencia_id) → al completar todas → checklist firmado por el nuevo empleado (recibe sus equipos con acta).

Offboarding (crítico): se dispara al fijar fecha_salida (o manual) → instancia con fechas de vencimiento por tarea → regla dura: no se puede desvincular (soft-delete) al empleado hasta que custodias activas = 0 (la BD y el modelo lo bloquean con mensaje accionable), usuarios desactivados, licencias/suscripciones liberadas, accesos revocados → acta de devolución global firmada → empleado pasa a inactivo con historial conservado. Tablero de offboardings en riesgo (a X días de la salida sin progreso) con alerta al jefe.

MÓDULO 20 · Mantenimiento (CMMS)
Rutas: CRUD /mantenimientos + {id}/completar|posponer|cancelar|iniciar|materiales · /mantenimientos/dashboard · /mantenimientos/calendario · /mantenimientos/{id}/pdf · /mantenimientos/{id}/desde-ticket/{ticket}.

Tablero: salud de flota (anillo % operativo), próximos 30 días (agenda + countdown), vencidos en rojo, completados del mes, costo acumulado, MTBF/MTTR por tipo, Pareto de fallas por categoría/equipo, "equipos enfermos" (top por costo acumulado/valor con recomendación de reemplazo).

Calendario: vista mes/semana con drag&drop para reprogramar (actualiza fecha_programada + bitácora), filtros por técnico/sede/tipo, export ICS para Google/Outlook.

Creación: equipo + tipo (preventivo/correctivo/predictivo) + título + checklist JSON (tareas con checkbox) + recurrencia + técnico + presupuesto. Conversión desde ticket: botón en ticket "Programar correctivo" → prefill con origen vinculado (origen_ticket_id); al completar, comenta en el ticket con resumen.

Recurrentes: al completar() una orden se materializa automáticamente la siguiente (serie_padre_id encadena la genealogía) con la fecha calculada según recurrencia — nadie debe crear manualmente las mensuales; además un job de seguridad regenera si faltan 7 días para la fecha y no existe siguiente orden.

Ejecución: estado en_proceso al iniciar → checklist interactivo en móvil (offline-ready: encola y sincroniza) → materiales consumidos (mantenimiento_materiales descuenta stock idéntico al flujo de tickets con inventario_consumos.entidad=mantenimiento) → completar(): valida checklist completo (o requiere flag explícito "omitir con justificación"), costo real mano de obra, suma materiales automáticamente, equipo → operativo/en_uso automático, garantía de reparación 90 días (garantia_hasta): si en 90 días aparece un ticket del mismo equipo con la misma categoría → banner "REINCIDENCIA de MAN-xxxx" + vinculación automática (métrica de calidad del trabajo interno). Si fue tercerizado: proveedor + técnico externo que visitó + costo facturado vs presupuesto.

Orden de trabajo PDF: hoja de campo imprimible: equipo, ubicación, checklist, materiales, espacio para notas y firma del usuario del equipo.

PARTE IV — INVENTARIO Y ADQUISICIONES
MÓDULO 21 · Inventario y Almacén
Rutas: CRUD /inventario + /inventario/ajustar|transferir|reservar · /inventario/ubicaciones · /inventario/kardex/{id} · import/export.

Definido (sobre el esquema v2.0): stock por ítem con segmentación ubicación × condición (inventario_stock_ubicacion); condiciones: nuevo/bueno/regular/dañado/obsoleto — el stock "bueno" es el vendible; unidades decimales (2.5 m de cable); costo promedio ponderado móvil recalculado en cada entrada; moneda por ítem con tasa del día; vencimiento de consumibles con alerta FEFO (primero en vencer); reservas con expiración (stock reservado excluido del disponible); KPIs: SKUs, stock total, stock bajo (vs stock_minimo), valorizado por bodega/condición.

Movimientos (kardex único): InventarioMovimiento::registrar() es el ÚNICO camino de mutación de stock — transacción + lockForUpdate + CHECK stock >= 0 + snapshot saldo_despues por fila (kardex real de saldos) + fila espejo en consumos cuando referencia a ticket/mantenimiento. Tipos: entrada (con costo → recalc promedio), salida, ajuste (regularización con motivo obligatorio + responsable), baja (con motivo; bajas_inventario legado absorbido), consumo, transferencia (valida origen≠destino, descuenta un lado y suma el otro, ambos con condición), devolución, toma_física.

Transferencias: entre ubicaciones de cualquier sede; el receptor puede "confirmar recepción" (estado del movimiento) — pendiente de confirmar visible en dashboard del destino. Swap-RMA: acción única: sale pieza nueva + entra pieza dañada a condición dañado + crea RMA en un paso.

Compra sugerida: reporte: consumo promedio mensual por ítem (de movimientos últimos 6 meses) vs stock actual vs mínimo → cantidad sugerida con botón "Generar borrador de OC" (prellena el módulo 25).

Escaneo: alta/ajuste/consumo escaneando código de barras/QR del ítem (cámara móvil) → lookup → modal de confirmación rápida. Requiere impresión de etiquetas por ítem (/inventario/{id}/etiqueta).

MÓDULO 22 · Toma Física (conteo cíclico)
Rutas: /inventario/toma-fisica · /inventario/toma-fisica/{id} · /inventario/toma-fisica/{id}/contar|conciliar|cerrar.

Flujo: abrir toma (alcance: bodega/departamento/todo) → congelar los ítems en alcance (movimientos bloqueados con mensaje claro, checkpoint de saldo) → conteo en móvil por ítem (escaneo o manual; doble conteo ciego opcional para items de alto valor) → conciliación: diferencia = contado - sistema mostrada con semáforo; cada ajuste requiere motivo + genera movimiento toma_fisica → acta firmada por el responsable de almacén → cerrar (descongela). KPI: valor de descuadre absoluto y %, ítems con divergencia recurrente (detección de fugas).

MÓDULO 23 · RMA (Garantías con proveedor)
Rutas: /rma CRUD · {id}/enviar|recibir|cerrar · desde ticket/equipo/inventario.
Flujo: abrir (equipo o pieza + descripción + fotos + nº de serie + prueba de compra adjunta) → proveedor → enviar (nº guía) → en_proceso → reemplazado (entra pieza nueva como entrada de stock o equipo reparado reingresa con validación) o rechazado (regresa como danado, flujo de baja/evaluación) → cerrado con costo final. Alerta si sin respuesta >15 días. Vinculado a tickets/mantenimientos como referencia. Dashboard de RMA abiertos por proveedor con antigüedad.

MÓDULO 24 · Proveedores
CRUD con RIF, contacto, categorías que surte, evaluación (rating post-compra/post-RMA: estrellas + comentario), historial completo: OCs, RMA, contratos, equipos comprados, tiempo promedio de entrega. Inactivación con validación de OCs abiertas.

MÓDULO 25 · Compras (OC)
Rutas: /compras CRUD · {id}/enviar|recibir|cancelar · /compras/{id}/pdf.

Flujo: borrador con ítems hacia inventario_items (reposición) o equipos (activo nuevo — al recibir se dispara wizard de alta de equipo prellenado con proveedor/costo/fecha de la OC) → validaciones de presupuesto (alerta si supera límite de la categoría, aprobación requerida según monto vía aprobaciones) → enviada (PDF de OC membretado con condiciones) → recepción parcial permitida (cantidad_recibida por ítem; estado parcialmente_recibida) → cada recepción de ítems de inventario ejecuta entrada con costo → recalcula promedio ponderado → equipo items piden alta antes de marcar recibido → cerrada con comparación pedido vs recibido. Multi-moneda con tasa del día congelada en la OC. Historial de costos por ítem (evolución de precios del proveedor).

MÓDULO 26 · Contratos
CRUD: proveedor, tipo (soporte/licencia/leasing/telefonía/internet), vigencia, valor mensual, auto-renovable, documento adjunto, responsable. Alertas escalonadas 60/30/7 antes de fin (a responsable + admin) con semáforo en dashboard; auto-renovable marca "se renovará automáticamente" y pide confirmación 30 días antes. Costo contractual anual agregado (KPI TI).

PARTE V — ORGANIZACIÓN, GESTIÓN Y ANÁLISIS
MÓDULO 27 · Sedes y Ubicaciones
CRUD sedes (código, dirección, principal única — índice parcial). Jerarquía de ubicaciones: Sede→Edificio→Piso→Oficina/Rack (árbol navegable, rutaCompleta() para display). Todo lo físico referencia ubicación (equipos, stock, empleados por defecto). Baja solo si no hay dependencias (FK RESTRICT con mensaje accionable).

MÓDULO 28 · Departamentos (jerárquicos)
padre_id con árbol visual (org-chart), centro de costo único, jefe = empleado del árbol (autovalidación), jerarquía usada por: aprobaciones (subárbol), chargeback (consumo agregado de descendientes), reportes consolidados. Migración o baja de departamento requiere reasignar hijos/empleados/equipos primero.

MÓDULO 29 · Directorio de Personal
CRUD + expediente: datos, credencial digital con QR verificable, custodias activas + históricas con actas, tickets, licencias/suscripciones, checklists de ciclo de vida, organigrama (jefe_directo), VIP flag (efecto en tickets), historial en ficha. Desvinculación protegida (regla de custodias del módulo 19). Foto para credencial con recorte en UI.

MÓDULO 30 · Usuarios y Roles (editor RBAC)
CRUD usuarios (link biunívoco a empleado — BD lo garantiza), activar/desactivar (nunca borrar si tiene historia; FKs RESTRICT), reset de 2FA, forzar cambio de contraseña, "iniciar sesión como" para soporte interno (bitacorado acceso_denegado-like con banner permanente "Estás actuando como X" y bloqueo de acciones sensibles). Editor de roles: lista roles, crear duplicando, toggle de 120 permisos agrupados por módulo (24 módulos × ver/crear/editar/eliminar/exportar + permisos puntuales), roles de sistema no editables en su núcleo. Cambios auditados con diff de permisos.

MÓDULO 31 · Automatización (motor de reglas) ✨
Rutas: /automatizacion CRUD · /automatizacion/{id}/probar (dry-run sobre un registro real) · logs por regla.

Definido: regla = disparador + condiciones (AND de predicados campo/operador/valor sobre la entidad) + acciones (secuencia) + orden + activo. Disparadores: ticket_creado, ticket_estado_cambiado, sla_umbral, stock_bajo, mantenimiento_proximo, garantia_por_vencer, empleado_ingreso, empleado_salida, manual. Acciones: asignar (a usuario fijo / guardia / menor carga), cambiar prioridad/estado, notificar (usuario/rol), agregar watchers, crear mantenimiento, crear ticket, webhook saliente. Cada ejecución en automatizacion_logs con payload y error si aplica; contador ejecuciones visible. Reglas de ejemplo preinstaladas (seed): crítico→guardia; redes→equipo de redes; stock bajo→jefe de almacén; empleado ingresa→crear onboarding; empleado sale→offboarding; garantía vence→alerta al custodio.

MÓDULO 32 · Tareas Programadas (transparencia operativa)
Rutas: /tareas-programadas (lista con próximo run, último resultado, toggle) + logs.
Catálogo completo de jobs (Laravel Scheduler):

Cron	Job
* * * * *	Cola de notificaciones/correos (queue worker)
5 0 * * *	Autocierre de tickets resueltos (7d) · vencimiento de reaperturas
*/15 * * * *	SLA: recalcular vencimientos con pausas · disparar umbrales 50/80/100% · ejecutar escalamientos
0 1 * * *	Alertas: stock bajo, garantías 30d, contratos 60/30/7, licencias, suscripciones, leasing, vencimientos de consumibles, préstamos por vencer/vencidos, reservas expiradas
0 6 * * *	Materializar mantenimientos recurrentes faltantes · agenda de mantenimientos próximos
0 7 * * 1	Digest semanal por técnico (sus métricas) · pulso del equipo TI (encuesta 1 pregunta)
0 6 1 * *	Chargeback mensual por departamento (snapshot de consumo) · ranking mensual · forecast
0 3 * * *	Backup BD (pg_dump cifrado) + verificación de integridad de hash-chain de bitácora + retención (purga de notificaciones leídas >90d, logs >1a según config)
0 4 * * 1	Toma física cíclica sugerida (reporte de ítems con última toma >90d)
23 * * * *	Follow-ups post-resolución (7d) · recordatorios de cartereo · recordatorios de aprobar solicitudes (48h escalado)
MÓDULO 33 · Notificaciones y Preferencias
Motor: evento → Notificacion::enviar(usuario, tipo, ...) → respeta preferencias_notificacion (app/email por tipo, con defaults razonables por rol) → in-app (campana, leido_el) + email con cola con reintentos (correos_enviados con estado/intentos/error, backoff). Agregación anti-spam: digest configurable ("máximo 1 email por tipo cada 30 min" o "resumen diario") — un stock bajo que dispara 20 veces envía 1. Canal WebSocket (Echo/Reverb): toasts en vivo + actualización de contadores sin refresh cuando está habilitado. Canales futuros: Teams/Slack/Telegram (interfaz de canal ya definida).

MÓDULO 34 · Reportes y Analíticas
Generador: filtros paramétricos (rango con presets, sede, departamento, categoría, técnico, prioridad), salida PDF (membretado, firmado con hash al pie, QR de verificación) / XLSX / CSV; historial en reportes_historial con re-descarga; programados por cron con destinatarios.

Catálogo completo: Tickets (consolidado, por técnico, por categoría, por sede, FRT/SLA, backlog aging, reincidencias, deflexión KB) · Inventario (valorizado por bodega/condición, kardex, movimientos, compra sugerida, vencimientos) · Mantenimientos (costos, MTBF/MTTR, completados/pendientes, por proveedor) · Activos (inventario patrimonial, custodias activas por departamento, garantías por vencer, leasing, depreciación contable) · Rendimiento (productividad por técnico con horas, CSAT, leaderboard) · Chargeback (consumo TI por centro de costo: horas + materiales + mantenimientos + share de licencias) · Cumplimiento SLA mensual · Auditoría (sesiones, bitácora) · SaaS (gasto por servicio/departamento). Constructor ad-hoc (fase 4): elegir entidad, columnas, filtros, agrupación → guarda la definición reutilizable.

MÓDULO 35 · Auditoría Forense
Pestañas: Sesiones (IP, UA, duración generada en BD, motivo de cierre, filtro por usuario/fecha, cierre remoto por admin, export) · Bitácora (timeline universal con filtros entidad/usuario/acción/fecha, modal con diff visual antes/después con resaltado de campos cambiados, búsqueda en payload JSON, export) · Verificación (estado de la hash-chain: verde "cadena íntegra, 808 registros verificados" / rojo con la fila exacta donde se rompió) · Intentos de login (detección de fuerza bruta: >10 fallos/15 min de una IP → bloqueo de IP temporal + alerta admin) · Retención (política de purga configurable con archivado previo).

MÓDULO 36 · Configuración
Personal (/configuracion): perfil, tema (claro/oscuro/sistema persistente), radio de bordes, idioma, zona horaria, contraseña (política activa), 2FA (setup con QR + códigos de respaldo), sesiones, preferencias de notificación (matriz tipo×canal).

Global (/configuracion/sistema, admin): identidad (nombre, RIF, logo, membrete), moneda de presentación + tasas (edición manual o API BCV), numeración (prefijos), SLA por defecto, autocierre, reapertura, seguridad (intentos, bloqueo, idle, longitud password, expiración opcional, historial), inventario (método de costeo, defaults de mínimo), alertas (días de anticipación por tipo), notificaciones (retención, digest), multi-sede (default), backup (programa + retención + botón "probar respaldo" que restaura en sandbox). Todo cambio con antes/después bitacorado.

MÓDULO 37 · Escáner QR y Estatus
/scan/{codigo}: universal — resuelve equipo (ficha + acciones), empleado (credencial verificable), item de inventario (ajuste rápido), acta (verificación de firmas). /estado: página pública interna (sin login en LAN) con incidentes mayores activos, servicios degradados por sede ("Impresora Piso 2: fuera de servicio, ETA 15:00") — reduce tickets de "¿qué pasa con…?". /acerca: versión, changelog, salud del sistema (cola, disco, jobs fallidos, uptime).

MÓDULO 38 · API e Integraciones
API REST (Sanctum, /api/v1): tokens personales con scopes por módulo; recursos espejo de los módulos con las mismas validaciones (FormRequests compartidos); rate limiting por token; documentación OpenAPI autogenerada; versionado. Webhooks salientes (webhooks CRUD admin): eventos ticket.creado, ticket.resuelto, stock.bajo, cambio.aprobado, firma HMAC-SHA256, reintentos con backoff, log de entregas. Entrantes: email-to-ticket, Teams/Slack bot (comandos: crear/comentar/cerrar desde el chat), Zabbix/PRTG (webhook autenticado crea incidente crítico con auto-vinculación por IP), LDAP/OIDC sync. Forecast ✨ (fase 4): extrapolación de series (media móvil estacional simple, transparente — sin blackbox): proyección de volumen de tickets, consumo de repuestos, con banda de confianza; presentado como "planificación de capacidad".

PARTE VI — VALIDACIONES INTEGRALES (checklist maestro)
6.1 Integridad de datos (BD, nivel motor)
IP única entre activos vigentes (índice parcial) · 2. Serial único vigente (soft-delete aware) · 3. Código patrimonial único vigente · 4. Cédula y email de empleado únicos vigentes · 5. Email/username únicos (citext) · 6. Una custodia activa por equipo (índice parcial) · 7. Un préstamo activo por equipo · 8. Asientos de licencia no excedidos (índices parciales) · 9. Stock ≥ 0 (CHECK) · 10. Reserva ≤ stock (CHECK) · 11. Guardias sin solapamiento (EXCLUDE) · 12. Horas técnicas sin solapamiento (EXCLUDE) · 13. Ventanas de cambio fin>inicio y sin conflicto de equipos · 14. Fechas coherentes (salida≥ingreso, garantía≥compra, SLA fin>inicio) · 15. Movimientos: referencia tipo+id siempre juntos, transferencia con origen≠destino, cantidad>0 · 16. Consumos con exactamente un destino (ticket XOR mantenimiento) · 17. Cambio de compra: item apunta a inventario XOR equipo · 18. Tasa de cambio con origen≠destino · 19. Hash de firmas y archivos: formato SHA-256 (domain) · 20. JSONB válido (CHECK json_valid→jsonb nativo).
6.2 Reglas de aplicación (FormRequests + Servicios)
Doble submit → idempotency key · 22. Locking optimista (409 con diff) · 23. Race de stock → lockForUpdate + revalidación server-side · 24. Numeración bajo transacción · 25. MIME real con finfo contra whitelist (jpg/png/pdf/xlsx/docx/mp4 limitado), máx 25MB, antivirus opcional · 26. Usuario inactivo o con tickets activos: no desactivar sin reasignar (mostrando la lista con 1 clic de reasignación masiva) · 27. Empleado con custodias: no desvincular sin offboarding · 28. Empleado↔Usuario biunívoco (BD) · 29. Custodio de departamento distinto al equipo → warning de confirmación explícita · 30. Tickets a equipos de_baja bloqueados · 31. Reapertura solo en ventana + solo solicitante/jefe (o admin) · 32. Calificación 1–5 única y solo del solicitante · 33. Firma canvas: no vacía (al menos 1 trazo), se guarda con hash+IP+UA+timestamp · 34. Checklist de mantenimiento: cierre bloqueado con tareas pendientes salvo omisión justificada · 35. Completar cambio: checklist implementación completo · 36. Horas: no futuro, fin>inicio, no solapadas (motor) · 37. Cantidades decimales precisión 3 dígitos · 38. Políticas de contraseña (config global) · 39. Toma física congela ítems (movimientos bloqueados en alcance) · 40. Contratos/OC: no recibir sin items, no cancelar con recepciones parciales sin confirmación · 41. Cartereo: token único, una respuesta por asignación · 42. Departamentos: jefe debe pertenecer al subárbol; centro de costo único · 43. Bajas de maestros bloqueadas con dependencias (RESTRICT traducido a "reasigna antes: N equipos, M empleados") · 44. Máscara en logs: nunca bitacorar password/secret/token/binarios (lista $ocultoAuditoria) · 45. Emails de sistema rate-limited.
6.3 Seguridad de aplicación
CSRF en todos los POST · 47. XSS: escape por defecto + CSP estricta · 48. SQLi: solo Query Builder/Eloquent + bindings · 49. Rate limit login (5/min por IP + por usuario) y API por token · 50. Sesión idle timeout + regeneración de ID en login/escalada · 51. 2FA TOTP + respaldo · 52. Password hashing Argon2id · 53. Verificación email de usuarios nuevos · 54. Cuenta bloqueo temporal tras N intentos + alerta · 55. Headers de seguridad (HSTS, X-Frame-Options DENY, etc.) · 56. Permisos verificados en Policy por endpoint + middleware por módulo · 57. Uploads fuera de webroot con nombres aleatorios · 58. Tokens públicos expirables y de un solo propósito · 59. "Iniciar sesión como" restringido + banner + auditoría reforzada · 60. Backups cifrados + prueba de restauración mensual programada.
PARTE VII — CAPA DE INNOVACIÓN ✨ (mecanismo de funcionamiento)
#	Innovación	Cómo funciona
1	Cartereo digital	Módulo 13 completo: campaña → tokens → confirmación con firma → actas masivas → reporte de discrepancias. Sustituye el inventario patrimonial anual en Excel.
2	Chargeback TI	Job mensual snapshot por centro de costo: horas técnicas (de ticket_horas valorizadas a tarifa configurable) + materiales + mantenimientos + amortización de licencias/suscripciones asignadas. El jefe ve su "factura TI" en el portal y el director la comparativa entre áreas. Cambia la conversación: de "gasto TI" a "consumo por área".
3	IA embebida (con fallback SIEMPRE — si la API falla, todo funciona sin IA)	(a) Clasificación al crear: el texto se envía a LLM con las categorías definidas → sugerencia categoría+prioridad con % confianza, usuario confirma (2 clics vs. navegar árbol). (b) Resumen de ticket: para técnicos que entran a tickets largos: "Este ticket trata de…" en 1 párrafo. (c) Sugerencia KB durante diagnóstico (embeddings de artículos, top-3). (d) Redacción asistida de solución/orden: borrador con tono institucional a partir de notas del técnico. (e) Detección de urgencia/frustración en texto del solicitante → sugiere subir urgencia con justificación. (f) Bot de autoservicio en el portal: RAG sobre KB pública → responde nivel 1 → si no resuelve en 3 intentos ofrece crear ticket CON el contexto transcripto adjunto. (g) Detección de idioma para respuestas. Todo con trazas de costo por request y presupuesto mensual configurable; desactivable por configuracion('ia.activo').
4	Proyectos de despliegue	Alta de N equipos como proyecto: checklist por unidad (etiquetar→especificar→cargar SO→asignar→entregar), barra de progreso por sede, imputación de horas/materiales, acta global final. Elimina el caos de flotas nuevas.
5	Firmas probatorias	Todo lo firmado (actas, tickets, cartereo, tomas, préstamos) guarda hash SHA-256 del binario + IP + UA + timestamp, y /verificar-acta/{codigo} permite a un tercero (auditor, abogado) validar integridad en 10 segundos.
6	Kiosk	Módulo 01: tablet de taller/recepción firma actas sin cuentas.
7	Auto-descubrimiento de red	Módulo 11: SNMP sweep → cola de "encontrados" → alta semiautomática.
8	Forecast	Módulo 34: series históricas → proyección 90d con banda → planificación de compras de repuestos y de capacidad.
9	Gamificación del equipo	Badges automáticos (resolvedor del mes, racha de CSAT ≥4.5, 0 reincidencias en 30d, 100% SLA semana) + tablero visible + mención en digest semanal. Opt-out individual (respeto).
10	Pulso del equipo TI	Encuesta anónima de 1 pregunta semanal (emoji 1–5) → tendencia del equipo en dashboard admin con alerta si cae 2 semanas seguidas. Cuida al que cuida.
11	PWA offline	Service worker: fichas de tickets y checklist de mantenimiento en cacheada; acciones de campo (firmas, fotos, checklist, consumos) se encolan en IndexedDB y sincronizan al recuperar red, con resolución de conflictos last-write-wins + aviso.
12	Ticket por voz	Web Speech API en el portal y móvil: dictado → transcripción → clasificación.
13	Credencial QR verificable	QR del empleado/carnet que cualquiera escanea y valida nombre-cargo-estado (control de acceso físico ligero).
14	Carnet de custodia digital	El empleado ve el valor patrimonial bajo su resguardo en su portal (responsabilidad personal visible).
15	Página de estatus interna	Módulo 37.
16	Incidente mayor con broadcast	Módulo 04 — los usuarios afectados se enteran por el sistema, no por rumores de WhatsApp.
PARTE VIII — JOURNEYS EXTREMO A EXTREMO (integración de todo)
J1 · Vida completa de un ticket → teléfono → técnico registra → clasificación IA + matriz → SLA por calendario → asignación por guardia → diagnóstico con KB + similares → consumo con lock → pausa por RMA → RMA swap → reanudar → resolver → cliente firma (hash) + 5★ → autocierre → PDF verificable → KPIs (FRT, SLA, CSAT) → si reincide en 90d → reabierto con flag. J2 · Onboarding → RRHH fija ingreso → plantilla materializa tareas → TI prepara equipo de bodega (pide via OC si no hay) → custodia con acta → usuario + licencias + M365 seat → credencial → firma de entrega → checklist completo → el primer lunes todo está listo y auditado. J3 · Offboarding → fecha_salida → checklist → recuperación de TODAS las custodias (bloqueo duro) con acta de devolución → liberar licencias/seats → desactivar cuenta y sesiones → revocar accesos → empleado inactivo con historia intacta → la empresa no pierde ni un activo. J4 · Vida de un activo → OC recibida → alta prellenada → QR impreso → custodia firma → 3 años de tickets/mantenimientos con costos acumulados vs valor → alerta "reparar ya no conviene" → decisión de reemplazo → nuevo proyecto de despliegue → baja: recuperación → borrado certificado con acta → historial eterno. J5 · Incidente mayor → Zabbix webhook → INC declarado → banner global + estatus page → hijos fusionados → broadcast a afectados → cambio de emergencia con rollback → resolución → post-mortem → problema con RCA → KB → automatización para detectarlo antes la próxima vez. J6 · Cartereo anual → campaña → 50 tokens → 40 confirmaciones firmadas en 3 días → 6 discrepancias resueltas con actas correctivas → 4 sin respuesta escalados a jefatura → reporte auditoría-listo en PDF firmado con hash.

PARTE IX — COMPLETITUD Y ENTREGA
Fases de implementación: F0 saneamiento BD (ya especificado) → F1 crítico (auth completo, portal, estados/SLA/calendario, notificaciones, QR, import/export, actas custodia, offboarding, config global, multi-sede) → F2 estándar (KB, escalamiento, horas, plantillas, automatización, aprobaciones, proveedores/OC, calendario mantenimientos, licencias, RMA, tomas físicas, webhooks) → F3 madurez (problemas, cambios, CMDB, reportes programados, i18n, 2FA, chargeback, cartereo, guardias) → F4 diferenciadores (IA, SSO/AD, forecast, PWA offline, Teams, auto-descubrimiento, proyectos, constructor de reportes, gamificación, pulso).

Definition of Done global por módulo: migraciones + modelos + FormRequests + Policies + controladores Inertia + componentes Vue tipados + notificaciones + tests (funcionales + estados + validaciones + race-conditions) + export incluido + bitácora cubierta + documentación en este formato + seed de demo.

Verificación de completitud ITIL 4: Incidentes ✔ Solicitudes ✔ Problemas ✔ Cambios ✔ Activos ✔ Conocimiento ✔ Niveles de servicio ✔ Proveedores ✔ Eventos/Monitoreo ✔ Continuidad ✔ Seguridad de accesos ✔ Mejora continua ✔ — cero prácticas ITIL de nivel 1-2 sin cubrir.

Con esto, la aplicación queda definida de punta a fin: cada módulo con sus rutas, pantallas, flujo paso a paso, reglas, validaciones y eventos; cada proceso transversal mecanizado; cada journey integrado; el esquema de BD (v2.0, 86 tablas), los modelos Laravel, el ETL, los seeders y el dashboard ya entregados en esta conversación corresponden 1:1 con este documento.