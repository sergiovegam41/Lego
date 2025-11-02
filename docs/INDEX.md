# 📚 Documentación LEGO Framework

## 🎨 Sistema de Theming

El Sistema de Theming de LEGO Framework proporciona una solución moderna, automática y elegante para manejar temas dark/light en toda la aplicación.

---

## 🗂️ Documentos Disponibles

### 1. [THEMING_README.md](./THEMING_README.md) - **START HERE** 🚀

**Para:** Todos los desarrolladores (nuevos y existentes)

**Lee esto si:**
- ⭐ Es tu primera vez con el sistema de theming
- ⭐ Necesitas un resumen rápido (TL;DR)
- ⭐ Quieres entender la arquitectura en 2 minutos
- ⭐ Buscas las variables más usadas

**Contenido:**
- TL;DR con ejemplos mínimos
- Arquitectura general
- Inicio rápido (CSS y JS)
- Variables más comunes
- Checklist para nuevos componentes
- Índice a otros documentos

**Tiempo de lectura:** 10 minutos

---

### 2. [THEMING_SYSTEM_GUIDE.md](./THEMING_SYSTEM_GUIDE.md) - Referencia Completa 📖

**Para:** Desarrolladores que necesitan detalles técnicos

**Lee esto si:**
- Necesitas conocer TODAS las variables disponibles
- Vas a crear un componente JS con ThemeAwareComponent
- Tienes un problema específico (troubleshooting)
- Quieres entender a fondo el sistema

**Contenido:**
- Visión general y filosofía
- Arquitectura detallada
- Lista completa de variables (150+)
- ThemeAwareComponent API completa
- Guía de migración detallada
- Mejores prácticas (DO's y DON'Ts)
- Troubleshooting exhaustivo
- Ejemplos de código avanzados

**Tiempo de lectura:** 30-40 minutos

---

### 3. [MIGRATION_EXAMPLE.md](./MIGRATION_EXAMPLE.md) - Tutorial Paso a Paso 🔄

**Para:** Desarrolladores migrando componentes existentes

**Lee esto si:**
- Tienes un componente con colores hardcodeados
- Quieres ver un caso real de migración
- Necesitas una guía paso a paso
- Quieres aprender patrones útiles

**Contenido:**
- Caso real: ProductsTableDemo
- Análisis de 24 colores hardcodeados
- Proceso de migración paso a paso
- Tabla de mapeo (hardcoded → variable)
- Antes/Después con código completo
- Métricas de mejora
- Lecciones aprendidas
- Errores comunes y cómo evitarlos

**Tiempo de lectura:** 20-25 minutos

---

### 4. [THEMING_IMPLEMENTATION_SUMMARY.md](./THEMING_IMPLEMENTATION_SUMMARY.md) - Resumen Ejecutivo 📝

**Para:** Tech leads, architects, managers

**Lee esto si:**
- Necesitas una visión general del proyecto
- Quieres conocer los entregables
- Buscas métricas y beneficios
- Planeas próximos pasos (roadmap)

**Contenido:**
- Objetivo y alcance del proyecto
- Componentes entregados
- Arquitectura implementada
- Métricas de éxito
- Beneficios para developers, código y usuarios
- Recursos de capacitación
- Próximos pasos recomendados
- Checklist de deployment

**Tiempo de lectura:** 15-20 minutos

---

## 🎯 ¿Qué documento leer?

### Soy nuevo en el framework
→ **[THEMING_README.md](./THEMING_README.md)** (empieza aquí)

### Necesito crear un componente nuevo
→ **[THEMING_README.md](./THEMING_README.md)** (sección "Inicio Rápido")

### Tengo que migrar un componente existente
→ **[MIGRATION_EXAMPLE.md](./MIGRATION_EXAMPLE.md)** (tutorial completo)

### Necesito una variable específica
→ **[THEMING_SYSTEM_GUIDE.md](./THEMING_SYSTEM_GUIDE.md)** (sección "Variables Disponibles")

### Tengo un error o problema
→ **[THEMING_SYSTEM_GUIDE.md](./THEMING_SYSTEM_GUIDE.md)** (sección "Troubleshooting")

### Quiero crear un componente JS reactivo al tema
→ **[THEMING_SYSTEM_GUIDE.md](./THEMING_SYSTEM_GUIDE.md)** (sección "ThemeAwareComponent")

### Necesito entender el proyecto completo
→ **[THEMING_IMPLEMENTATION_SUMMARY.md](./THEMING_IMPLEMENTATION_SUMMARY.md)** (resumen ejecutivo)

### Quiero capacitar a mi equipo
→ Lee todos en orden: README → Guide → Migration Example

---

## 🛠️ Herramientas

### Script de Detección de Colores Hardcodeados

**Ubicación:** `/scripts/find-hardcoded-colors.sh`

**Uso:**
```bash
# Reporte resumido
./scripts/find-hardcoded-colors.sh

# Reporte detallado
./scripts/find-hardcoded-colors.sh --detailed

# Archivo específico
./scripts/find-hardcoded-colors.sh --file components/App/MyComponent/styles.css
```

**Qué hace:**
- Encuentra todos los colores hardcodeados en CSS
- Genera reporte con estadísticas
- Muestra top 10 archivos problemáticos
- Sugiere próximos pasos

---

## 📂 Código Fuente

### CSS Variables
**Archivo:** `/assets/css/core/theme-variables.css`
- 150+ variables CSS
- Organizadas por categoría
- Documentación inline

### ThemeAwareComponent (JS)
**Archivo:** `/assets/js/core/base/ThemeAwareComponent.js`
- Clase base para componentes JS
- Auto-suscripción al ThemeManager
- Helpers útiles

### ThemeManager
**Archivo:** `/assets/js/core/modules/theme/theme-manager.js`
- Gestor de cambio de tema
- Patrón Observer
- Persistencia en localStorage

### Integración
**Archivo:** `/assets/css/core/base.css`
- Importa `theme-variables.css`
- Disponible globalmente

---

## 📊 Ejemplo Migrado

### ProductsTableDemo
**Ubicación:** `/components/App/ProductsTableDemo/products-table-demo.css`

**Antes:**
- ❌ 24 colores hardcodeados
- ❌ No responde a cambio de tema
- ❌ Problemas en dark mode

**Después:**
- ✅ 0 colores hardcodeados
- ✅ 35+ variables CSS
- ✅ Theming automático
- ✅ Funciona perfecto en dark/light

**Documentación:** [MIGRATION_EXAMPLE.md](./MIGRATION_EXAMPLE.md)

---

## 🎓 Plan de Capacitación

### Nivel 1: Básico (1 hora)
1. Leer [THEMING_README.md](./THEMING_README.md) (TL;DR y Quick Start)
2. Ver ejemplo migrado: ProductsTableDemo
3. Practicar con un componente simple

### Nivel 2: Intermedio (2-3 horas)
1. Leer [THEMING_SYSTEM_GUIDE.md](./THEMING_SYSTEM_GUIDE.md) completo
2. Seguir [MIGRATION_EXAMPLE.md](./MIGRATION_EXAMPLE.md) paso a paso
3. Migrar un componente real

### Nivel 3: Avanzado (4-5 horas)
1. Estudiar ThemeAwareComponent API
2. Crear un componente JS complejo
3. Contribuir con nuevas variables al sistema
4. Revisar PRs de otros developers

---

## 📈 Próximos Pasos

### Inmediato
- [ ] Capacitar al equipo (usar este índice como guía)
- [ ] Identificar componentes para migrar
- [ ] Ejecutar `find-hardcoded-colors.sh` para baseline

### Corto Plazo (1-2 meses)
- [ ] Migrar componentes críticos
- [ ] Establecer reglas de code review
- [ ] Integrar linter CSS

### Mediano Plazo (3-6 meses)
- [ ] Migrar todos los componentes
- [ ] Crear pre-commit hooks
- [ ] Implementar Storybook

### Largo Plazo (6+ meses)
- [ ] Cero colores hardcodeados
- [ ] Temas custom (high contrast, etc.)
- [ ] Design tokens exportables

---

## ✅ Checklist Rápido

### Para Crear Componente Nuevo

```
[ ] No usar colores hardcodeados (#fff, white, etc.)
[ ] Usar var(--variable-semantica)
[ ] Usar variables de spacing (--space-*)
[ ] Usar variables de typography (--font-*)
[ ] Probar en dark mode
[ ] Probar en light mode
[ ] Si es JS: extender ThemeAwareComponent (solo si necesario)
```

### Para Migrar Componente Existente

```
[ ] Leer MIGRATION_EXAMPLE.md
[ ] Ejecutar find-hardcoded-colors.sh --file
[ ] Crear tabla de mapeo (hardcoded → variable)
[ ] Reemplazar sistemáticamente
[ ] Probar en ambos temas
[ ] Documentar cambios
[ ] Ejecutar find-hardcoded-colors.sh --file (verificar 0)
```

---

## 🔗 Links Rápidos

| Documento | Link | Tiempo |
|-----------|------|--------|
| README (Start Here) | [THEMING_README.md](./THEMING_README.md) | 10 min |
| Guía Completa | [THEMING_SYSTEM_GUIDE.md](./THEMING_SYSTEM_GUIDE.md) | 30 min |
| Tutorial Migración | [MIGRATION_EXAMPLE.md](./MIGRATION_EXAMPLE.md) | 20 min |
| Resumen Ejecutivo | [THEMING_IMPLEMENTATION_SUMMARY.md](./THEMING_IMPLEMENTATION_SUMMARY.md) | 15 min |

| Código | Link |
|--------|------|
| Variables CSS | [theme-variables.css](../assets/css/core/theme-variables.css) |
| ThemeAwareComponent | [ThemeAwareComponent.js](../assets/js/core/base/ThemeAwareComponent.js) |
| ThemeManager | [theme-manager.js](../assets/js/core/modules/theme/theme-manager.js) |

| Herramienta | Link |
|-------------|------|
| Detector de Colores | [find-hardcoded-colors.sh](../scripts/find-hardcoded-colors.sh) |

---

## 💬 Soporte

### ¿Preguntas?
1. Revisa la documentación (probablemente esté respondida)
2. Busca en el código de ejemplo (ProductsTableDemo)
3. Pregunta en Slack #frontend-help

### ¿Encontraste un bug?
1. Verifica que estés usando el sistema correctamente
2. Revisa la sección Troubleshooting del Guide
3. Abre un issue en GitHub con detalles

### ¿Tienes una sugerencia?
1. Discute en Slack o issue de GitHub
2. Si es una nueva variable: justifica el caso de uso
3. Crea PR con documentación actualizada

---

## 🎉 Bienvenido al Sistema de Theming

Este sistema fue diseñado para hacer tu vida más fácil. **Simplemente usa las variables CSS y obtendrás theming automático.**

¡Feliz desarrollo! 🚀

---

**Última actualización:** 2025-11-02
**Versión:** 1.0.0
**Estado:** ✅ Producción Ready
