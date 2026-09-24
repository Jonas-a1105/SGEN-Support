## 📋 Descripción del Cambio
<!-- Proporciona una explicación concisa y clara de qué introduce o soluciona este Pull Request. -->

## 🔗 Incidencia Relacionada
<!-- Vincula el issue con 'Closes #123' o 'Fixes #456' si aplica. -->

## 🛠️ Tipo de Cambio
- [ ] 🚀 **feat**: Nueva funcionalidad para el usuario
- [ ] 🐛 **fix**: Corrección de bug o fallo operativo
- [ ] 🎨 **style**: Mejoras puramente visuales o de formato (sin impacto en lógica)
- [ ] ♻️ **refactor**: Refactorización de código sin alterar comportamiento externo
- [ ] ⚡ **perf**: Optimización de rendimiento
- [ ] 🧪 **test**: Adición o actualización de pruebas automatizadas
- [ ] 📝 **docs**: Actualización de documentación o diagramas
- [ ] 📦 **build** / 🤖 **ci**: Cambios en dependencias, scripts o GitHub Actions

## ✅ Criterios de Calidad y Verificación (Senior Checklist)
- [ ] **Pruebas Automatizadas**: `php artisan test` ejecutado y con 100% de aserciones aprobadas.
- [ ] **Compilación Frontend**: `npm run build` ejecutado limpiamente en `sgen-backend`.
- [ ] **Cero Estilos Inline**: No se añadieron atributos `style="..."` ni `:style="..."` en componentes Vue.
- [ ] **Cero Sombras Duras**: Ninguna propiedad de sombra no-none (`box-shadow: none !important;` verificado).
- [ ] **Tipado Estricto**: Cero uso de `any` en TypeScript.
- [ ] **Diseño Responsivo**: Comprobado en viewport móvil ($\le 768\text{px}$) y de escritorio.
- [ ] **Changelog**: Se actualizó `CHANGELOG.md` si el cambio afecta la versión pública.

## 📸 Capturas de Pantalla / Evidencia (Si Aplica)
<!-- Adjunta capturas del antes y después para cambios visuales -->
