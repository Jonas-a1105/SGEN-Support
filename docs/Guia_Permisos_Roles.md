# Guía de Permisos por Rol - SGEN-Support

> **Última actualización:** 2025-12-10 (v1.0.2)

Esta guía detalla los permisos y restricciones de cada rol en el sistema SGEN-Support.

---

## 📊 Matriz de Permisos

### Módulo: Dashboard
| Funcionalidad | Admin | Técnico | Consultor |
|--------------|:-----:|:-------:|:---------:|
| Ver estadísticas globales | ✅ | ❌ | ❌ |
| Ver estadísticas del departamento | ✅ | ✅ | ✅ |
| Ver tickets pendientes | ✅ | ✅ | ✅ |
| Ver tickets en proceso | ✅ | ✅ | ✅ |

### Módulo: Tickets de Soporte
| Funcionalidad | Admin | Técnico | Consultor |
|--------------|:-----:|:-------:|:---------:|
| Ver lista de tickets | ✅ | ✅ | ✅ |
| Crear nuevo ticket | ✅ | ✅ | ✅ |
| Ver detalle de ticket | ✅ | ✅ | ✅ |
| Asignar técnico a ticket | ✅ | ✅ | ❌ |
| Reasignar técnico | ✅ | ❌ | ❌ |
| Cambiar estado de ticket | ✅ | ✅ | ❌ |
| Agregar comentarios | ✅ | ✅ | ✅ |
| Agregar notas internas | ✅ | ✅ | ❌ |
| Subir archivos | ✅ | ✅ | ✅ |
| Eliminar ticket | ✅ | ❌ | ❌ |

### Módulo: Equipos (Inventario)
| Funcionalidad | Admin | Técnico | Consultor |
|--------------|:-----:|:-------:|:---------:|
| Ver lista de equipos | ✅ | ✅ | ✅ |
| Ver detalle de equipo | ✅ | ✅ | ✅ |
| Registrar nuevo equipo | ✅ | ❌ | ❌ |
| Editar equipo | ✅ | ❌ | ❌ |
| Eliminar equipo | ✅ | ❌ | ❌ |
| Eliminación masiva | ✅ | ❌ | ❌ |
| Crear mantenimiento | ✅ | ✅ | ❌ |
| Marcar mantenimiento realizado | ✅ | ✅ | ❌ |

### Módulo: Mantenimientos
| Funcionalidad | Admin | Técnico | Consultor |
|--------------|:-----:|:-------:|:---------:|
| Ver lista de mantenimientos | ✅ | ✅ | ✅ |
| Ver detalle de mantenimiento | ✅ | ✅ | ✅ |
| Crear nuevo mantenimiento | ✅ | ✅ | ❌ |
| Editar mantenimiento | ✅ | ✅ | ❌ |
| Marcar como realizado | ✅ | ✅ | ❌ |
| Eliminar mantenimiento | ✅ | ❌ | ❌ |

### Módulo: Departamentos
| Funcionalidad | Admin | Técnico | Consultor |
|--------------|:-----:|:-------:|:---------:|
| Ver lista de departamentos | ✅ | ✅ | ✅ |
| Ver detalle de departamento | ✅ | Solo su dpto. | Solo su dpto. |
| Crear departamento | ✅ | ❌ | ❌ |
| Editar departamento | ✅ | ❌ | ❌ |
| Eliminar departamento | ✅ | ❌ | ❌ |
| Asignar equipo a departamento | ✅ | ❌ | ❌ |
| Editar empleados del departamento | ✅ | ❌ | ❌ |

### Módulo: Empleados
| Funcionalidad | Admin | Técnico | Consultor |
|--------------|:-----:|:-------:|:---------:|
| Acceso al menú "Empleados" | ✅ | ❌ | ❌ |
| Ver lista de empleados | ✅ | ❌ | ❌ |
| Ver detalle de empleado | ✅ | ❌ | ❌ |
| Crear empleado | ✅ | ❌ | ❌ |
| Editar empleado | ✅ | ❌ | ❌ |
| Eliminar empleado | ✅ | ❌ | ❌ |

### Módulo: Usuarios
| Funcionalidad | Admin | Técnico | Consultor |
|--------------|:-----:|:-------:|:---------:|
| Ver lista de usuarios | ✅ | ❌ | ❌ |
| Crear usuario | ✅ | ❌ | ❌ |
| Editar usuario | ✅ | ❌ | ❌ |
| Cambiar roles | ✅ | ❌ | ❌ |
| Eliminar usuario | ✅ | ❌ | ❌ |

### Módulo: Logs y Auditoría
| Funcionalidad | Admin | Técnico | Consultor |
|--------------|:-----:|:-------:|:---------:|
| Ver bitácora de acciones | ✅ | ❌ | ❌ |
| Ver logs de sesión | ✅ | ❌ | ❌ |
| Exportar reportes | ✅ | ❌ | ❌ |

### Módulo: Reportes
| Funcionalidad | Admin | Técnico | Consultor |
|--------------|:-----:|:-------:|:---------:|
| Generar reporte de tickets | ✅ | ✅ | ❌ |
| Generar reporte de mantenimientos | ✅ | ✅ | ❌ |
| Generar reporte de equipos | ✅ | ✅ | ❌ |

---

## 🔐 Descripción de Roles

### 👑 Administrador (admin)
El rol con mayor nivel de privilegios. Tiene acceso completo a todas las funcionalidades del sistema:
- Gestión completa de usuarios y empleados
- Asignación y reasignación de técnicos
- Creación y eliminación de recursos
- Acceso a logs y auditorías
- Configuración del sistema

### 🔧 Técnico (tecnico)
Rol operativo enfocado en la resolución de tickets y mantenimientos:
- Puede gestionar tickets asignados
- Puede crear y realizar mantenimientos
- Puede generar reportes de su trabajo
- **No puede**: gestionar empleados, usuarios ni eliminar registros críticos

### 👁️ Consultor (consultor)
Rol de solo lectura/visualización con permisos limitados:
- Puede ver información de su departamento
- Puede crear tickets de soporte
- Puede agregar comentarios a tickets
- **No puede**: asignar técnicos, crear mantenimientos ni acceder a módulos administrativos

---

## ⚠️ Notas Importantes

1. **Restricción por Departamento:** Los roles `tecnico` y `consultor` solo pueden ver información de su departamento asignado en el detalle de departamentos.

2. **Creación de Tickets:** Todos los roles pueden crear tickets, pero solo `admin` y `tecnico` pueden asignar técnicos.

3. **Eliminación:** Solo el rol `admin` puede eliminar registros del sistema. Esto incluye tickets, equipos, mantenimientos y usuarios.

4. **Logs de Sesión:** Se registran todas las acciones de inicio y cierre de sesión automáticamente, independientemente del rol.

---

## 📝 Historial de Cambios en Permisos

| Versión | Fecha | Cambios |
|---------|-------|---------|
| v1.0.2 | 2025-12-10 | Menú "Empleados" restringido solo a admin. Botones de eliminación masiva restringidos a admin. Botón de editar empleado en departamento restringido a admin. Botón "Nuevo Mantenimiento" restringido a admin/técnico. Botón "Asignar" en tickets restringido a admin/técnico. |
| v1.0.0 | 2025-12-01 | Versión inicial del sistema de permisos |
