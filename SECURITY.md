# 🛡️ Política de Seguridad (Security Policy)

La seguridad y confiabilidad de **SGEN-Support** son de máxima prioridad. Agradecemos a los investigadores y desarrolladores que nos ayudan a mantener los datos e infraestructura protegidos.

---

## 📅 Versiones Soportadas

Actualmente se proporcionan parches de seguridad y correcciones para las siguientes versiones activas:

| Versión | Soportada | Estado de Soporte |
| :--- | :---: | :--- |
| `1.1.x` |  | Versión productiva actual (Laravel 12 / Vue 3 / Electron) |
| `1.0.x` | ⚠️ | Parches críticos de seguridad únicamente |
| `< 1.0.0` | ❌ | Obsoleta - Sin soporte |

---

## 🚨 Reporte de Vulnerabilidades

Si has descubierto una vulnerabilidad de seguridad en SGEN-Support:

1. **NO** crees un Issue público en GitHub.
2. Envía un reporte detallado de divulgación responsable directamente al responsable del proyecto:
   - **Correo electrónico**: `seguridad@sgen-support.local` / Mensaje privado al mantenedor en GitHub (`@Jonas-a1105`).
3. Por favor incluye:
   - Descripción técnica detallada del vector de ataque.
   - Pasos precisos o script de prueba de concepto (PoC) para reproducir el fallo.
   - Componente o archivo afectado (`sgen-backend`, `desktop`, rutas API, etc.).
   - Impacto estimado en confidencialidad, integridad o disponibilidad.

### Tiempos de Respuesta:
- **Confirmación inicial de recepción**: Menos de 48 horas.
- **Evaluación y triaje**: Menos de 5 días hábiles.
- **Liberación de parche de mitigación**: Según la severidad (crítico: $\le 7$ días; medio/bajo: siguiente versión programada).

---

## 🔒 Buenas Prácticas Implementadas en el Repositorio

- **Aislamiento de Secretos**: Ningún archivo `.env`, llave privada ni contraseña se almacena en el control de versiones.
- **Validación Estricta de Entradas**: Formularios respaldados por Form Requests con validación de tipos e inyecciones SQL neutralizadas mediante PDO/Eloquent ORM.
- **Protección Electron Desktop**:
  - Context isolation activado (`contextIsolation: true`).
  - Preload scripts restringidos para exponer únicamente los canales IPC necesarios.
  - Cierre forzado y limpio de procesos hijos al salir de la aplicación.
