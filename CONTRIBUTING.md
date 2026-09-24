# 🤝 Guía de Contribución a SGEN-Support

Gracias por tu interés en contribuir al ecosistema **SGEN-Support**. Para mantener un código de calidad senior, escalable, robusto y mantenible, todos los colaboradores deben seguir estas directrices de ingeniería.

---

## 🌳 1. Estrategia de Ramas (Branching Model)

Utilizamos una estrategia basada en **Trunk-Based Development** con ramas cortas de características:

- **`main`**: Rama principal y siempre lista para producción. Todo cambio que ingrese a `main` debe haber pasado por un Pull Request y tener los tests en verde.
- **`develop`**: Rama de integración donde convergen las nuevas funcionalidades antes de preparar un release.
- **Ramas de trabajo**:
  - `feat/nombre-funcionalidad`: Nuevas características (ej. `feat/dashboard-export-pdf`).
  - `fix/descripcion-bug`: Corrección de incidencias (ej. `fix/kpi-counter-calc`).
  - `refactor/area-afectada`: Mejoras de arquitectura sin alterar el comportamiento.
  - `release/vX.Y.Z`: Preparación de versiones de lanzamiento.

---

## ✍️ 2. Convención de Commits (Conventional Commits)

Los mensajes de confirmación deben seguir estrictamente el estándar de [Conventional Commits](https://www.conventionalcommits.org/es/v1.0.0/):

```
<tipo>(<alcance_opcional>): <descripción breve en minúsculas>

[cuerpo explicativo opcional]

[pie con tickets o referencias opcional]
```

### Tipos Permitidos:
- **`feat`**: Una nueva funcionalidad para el usuario.
- **`fix`**: Una corrección de error.
- **`docs`**: Cambios exclusivos en documentación.
- **`style`**: Ajustes de formato o código limpio sin cambios en lógica.
- **`refactor`**: Refactorización de código productivo.
- **`perf`**: Optimización de rendimiento.
- **`test`**: Añadir o corregir pruebas automatizadas.
- **`build`**: Modificaciones al sistema de compilación (Vite, Electron-Builder, Composer).
- **`ci`**: Cambios en flujos de GitHub Actions o scripts de integración continua.
- **`chore`**: Tareas de mantenimiento o configuración general.

*Ejemplo:* `feat(kpi): add interactive 4px accent hover line`

---

## 📐 3. Reglas de Calidad y Estándares de Diseño

Cualquier contribución que modifique el frontend o backend debe respetar los siguientes principios innegociables:

1. **Cero Estilos Inline (`style="..."` o `:style="..."`)**:
   - Todo estilo debe residir en variables CSS semánticas (`variables.css`) o en bloques `<style scoped>` del componente.
2. **Cero Sombras No-None (`box-shadow: none !important;`)**:
   - La estética del sistema es de bordes limpios y vidrio plano ("Flat Glass"). No se permiten sombras duras.
3. **Componentes Base Centralizados**:
   - Reutiliza siempre los componentes de `resources/js/Components/UI` (`BaseKpiCard`, `BaseSearchToolbar`, `BaseViewModeToggle`, `BaseButton`, `BaseCard`, `BaseConfirmModal`, etc.). No dupliques estilos ni markup.
4. **TypeScript Estricto**:
   - Prohibido el uso de `any`. Declara siempre las interfaces y tipos necesarios en `resources/js/types/`.
5. **100% de Pruebas Verificadas**:
   - Antes de abrir un PR, ejecuta:
     ```bash
     cd sgen-backend
     php artisan test
     npm run build
     ```
   - Todas las pruebas deben aprobarse sin fallos ni advertencias.

---

## 🚀 4. Flujo de Trabajo para Pull Requests

1. **Clonar y Crear Rama**:
   ```bash
   git checkout -b feat/mi-mejora
   ```
2. **Realizar Cambios y Commits Atómicos**:
   ```bash
   git commit -m "feat(module): implement new capability"
   ```
3. **Ejecutar Pruebas Locales**:
   Asegúrate de que la suite pase al 100%.
4. **Publicar y Crear Pull Request**:
   - Llena cuidadosamente la plantilla de PR (`.github/PULL_REQUEST_TEMPLATE.md`).
   - El linter automatizado verificará el título del PR y el pipeline de CI validará los tests en GitHub.

---

## 📦 5. Versionado y Automatización de Lanzamientos

Para administradores del repositorio, el lanzamiento de una nueva versión está completamente automatizado:

```powershell
# Lanzar versión tipo patch (ej. 1.1.0 -> 1.1.1)
.\scripts\release.ps1 -Bump patch

# O lanzar versión menor (ej. 1.1.0 -> 1.2.0)
.\scripts\release.ps1 -Bump minor

# Publicar tag y activar el pipeline de compilación de escritorio
git push origin main --tags
```

El pipeline de GitHub Actions se encargará de compilar el instalador para Windows, calcular las sumas de verificación y habilitar la actualización automática para todos los clientes instalados.
