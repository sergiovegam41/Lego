# 🎨 Sistema de Theming LEGO Framework

## TL;DR - Para Desarrolladores Impacientes

```css
/* ❌ NUNCA HAGAS ESTO */
.my-component {
    background: #ffffff;
    color: #000000;
}

/* ✅ SIEMPRE HAZ ESTO */
.my-component {
    background: var(--bg-surface);
    color: var(--text-primary);
}
```

**Resultado:** Tu componente responderá automáticamente a cambios de tema dark/light. **Sin JavaScript necesario.**

---

## 📚 Documentación Completa

Este directorio contiene toda la documentación del Sistema de Theming:

### 1. [Guía Completa del Sistema](./THEMING_SYSTEM_GUIDE.md) 📖
**Para:** Todos los desarrolladores
**Contenido:**
- Visión general y arquitectura
- Lista completa de variables disponibles
- Guía rápida para componentes CSS
- ThemeAwareComponent para componentes JS
- Mejores prácticas y troubleshooting

### 2. [Ejemplo de Migración Paso a Paso](./MIGRATION_EXAMPLE.md) 🔄
**Para:** Desarrolladores migrando componentes existentes
**Contenido:**
- Caso real: ProductsTableDemo
- Proceso completo de migración
- Antes y después con código real
- Lecciones aprendidas
- Patrones útiles

---

## 🏗️ Arquitectura en 2 Minutos

### Archivos Clave

```
Lego/
├── assets/
│   ├── css/core/
│   │   ├── base.css                    ← Importa theme-variables.css
│   │   └── theme-variables.css         ← ⭐ Todas las variables CSS
│   └── js/core/
│       ├── modules/theme/
│       │   └── theme-manager.js        ← Gestor de cambio de tema
│       └── base/
│           └── ThemeAwareComponent.js  ← Clase base para componentes JS
└── docs/
    ├── THEMING_README.md               ← Este archivo
    ├── THEMING_SYSTEM_GUIDE.md         ← Guía completa
    └── MIGRATION_EXAMPLE.md            ← Ejemplo de migración
```

### Flujo de Funcionamiento

```
Usuario Toggle Tema
        ↓
ThemeManager agrega/quita clase .dark en <html>
        ↓
   ┌────────┴─────────┐
   ↓                  ↓
CSS Variables      JS Components
cambian AUTO       reciben evento
(90% casos)        (10% casos)
```

---

## 🚀 Inicio Rápido

### Para Componentes CSS (Lo más común)

```css
/* Tu componente: my-card.css */

.my-card {
    /* Backgrounds */
    background: var(--card-bg);

    /* Text */
    color: var(--text-primary);

    /* Borders */
    border: 1px solid var(--card-border);

    /* Spacing */
    padding: var(--card-padding);
    margin-bottom: var(--space-xl);

    /* Typography */
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-medium);

    /* Border Radius */
    border-radius: var(--card-radius);

    /* Shadows */
    box-shadow: var(--card-shadow);

    /* Transitions */
    transition: var(--transition-fast);
}

.my-card:hover {
    background: var(--card-bg-hover);
    box-shadow: var(--card-shadow-hover);
}
```

**¡Eso es todo!** Tu componente ahora funciona en dark y light mode.

### Para Componentes JS (Solo cuando sea necesario)

```javascript
// my-chart.js
import ThemeAwareComponent from '/assets/js/core/base/ThemeAwareComponent.js';

class ChartComponent extends ThemeAwareComponent {
    constructor(id) {
        super();
        this.chart = new Chart(id);
    }

    // Este método se llama automáticamente cuando cambia el tema
    onThemeChange(theme) {
        super.onThemeChange(theme);

        this.chart.update({
            backgroundColor: this.themeValue('#fff', '#1a1a1a'),
            textColor: this.themeValue('#000', '#fff')
        });
    }

    destroy() {
        this.chart.destroy();
        super.destroy(); // ← Importante: limpia suscripciones
    }
}
```

---

## 📊 Variables Más Usadas

### Backgrounds (Fondos)

```css
--bg-body               /* Fondo de página */
--bg-surface            /* Cards, paneles */
--bg-surface-hover      /* Hover state */
--bg-input              /* Inputs, textareas */
```

### Text (Texto)

```css
--text-primary          /* Texto principal */
--text-secondary        /* Texto secundario */
--text-tertiary         /* Texto terciario */
--text-disabled         /* Texto deshabilitado */
```

### Borders (Bordes)

```css
--border-light          /* Bordes sutiles */
--border-medium         /* Bordes normales */
--border-focus          /* Estado focus */
--border-error          /* Estado error */
```

### Spacing (Espaciado)

```css
--space-xs              /* 4px */
--space-sm              /* 8px */
--space-md              /* 12px */
--space-lg              /* 16px */
--space-xl              /* 24px */
--space-2xl             /* 32px */
```

### Components (Componentes Específicos)

```css
/* Cards */
--card-bg
--card-border
--card-padding
--card-radius
--card-shadow

/* Buttons */
--button-bg-primary
--button-bg-secondary
--button-padding-md

/* Inputs */
--input-bg
--input-border
--input-padding

/* Badges */
--badge-success-bg
--badge-error-bg
--badge-warning-bg
```

**[Ver lista completa →](./THEMING_SYSTEM_GUIDE.md#variables-disponibles)**

---

## ✅ Checklist para Nuevos Componentes

Antes de hacer commit:

- [ ] **No hay colores hardcodeados** (`#fff`, `white`, `#000`, etc.)
- [ ] **Usa variables de espaciado** (no `16px`, sino `var(--space-lg)`)
- [ ] **Usa variables de tipografía** (no `14px`, sino `var(--font-size-base)`)
- [ ] **Probado en dark mode** (toggle el tema y verifica)
- [ ] **Probado en light mode** (toggle el tema y verifica)
- [ ] Si es componente JS: **Extiende ThemeAwareComponent** (si necesita reaccionar al tema)
- [ ] Si es componente JS: **Llama `super.destroy()`** en el método destroy

---

## 🔍 Cómo Identificar Componentes Problemáticos

### Síntomas Visuales

- ❌ Texto invisible en dark mode
- ❌ Fondos que no cambian con el tema
- ❌ Bordes que desaparecen o se ven mal
- ❌ Sombras inapropiadas para el tema
- ❌ Código con colores fijos

### Búsqueda Programática

```bash
# Buscar colores hex en archivos CSS
find components/ -name "*.css" -exec grep -l "#[0-9a-fA-F]\{3,6\}" {} \;

# Buscar colores con nombre
find components/ -name "*.css" -exec grep -l -E ":\s*(white|black);" {} \;

# Contar ocurrencias en un archivo específico
grep -c "#[0-9a-fA-F]\{3,6\}" components/App/MyComponent/my-component.css
```

---

## 🛠️ Migración de Componentes Existentes

### Proceso de 5 Pasos

1. **Identificar** colores hardcodeados
2. **Mapear** a variables semánticas
3. **Reemplazar** sistemáticamente
4. **Probar** en ambos temas
5. **Documentar** el componente migrado

**[Ver ejemplo completo →](./MIGRATION_EXAMPLE.md)**

### Tabla de Mapeo Rápida

| Si encuentras... | Reemplaza con... | Razón |
|-----------------|------------------|-------|
| `#ffffff` o `white` (fondo) | `var(--bg-surface)` | Fondo de superficie |
| `#ffffff` (texto) | `var(--text-primary)` | Texto principal |
| `#000000` o `black` (texto) | `var(--text-primary)` | Texto principal |
| `#f5f5f5` | `var(--bg-surface)` | Fondo alternativo |
| `#e5e5e5` | `var(--border-light)` | Borde sutil |
| `#3ba1ff` | `var(--accent-primary)` | Color de marca |
| `16px` (padding) | `var(--space-lg)` | Espaciado consistente |
| `14px` (font) | `var(--font-size-base)` | Tipografía consistente |

---

## 📈 Estado Actual del Framework

### ✅ Completado

- [x] Sistema de variables CSS (`theme-variables.css`)
- [x] ThemeManager con patrón Observer
- [x] ThemeAwareComponent clase base
- [x] Integración en `base.css`
- [x] Documentación completa
- [x] Ejemplo de migración (ProductsTableDemo)

### 🔄 En Progreso

- [ ] Migración de componentes restantes
- [ ] CSS Linter para prevención
- [ ] Tests automatizados de theming

### 📋 Por Hacer

- [ ] Migrar todos los componentes del directorio `/components`
- [ ] Crear pre-commit hook para validar variables
- [ ] Agregar Storybook con toggle de tema
- [ ] Crear tema custom (ej: high contrast)

---

## 🎓 Capacitación del Equipo

### Para Nuevos Desarrolladores

1. Lee el [TL;DR](#tldr---para-desarrolladores-impacientes)
2. Revisa las [Variables Más Usadas](#-variables-más-usadas)
3. Sigue el [Checklist](#-checklist-para-nuevos-componentes)
4. **Nunca uses colores hardcodeados**

### Para Desarrolladores Existentes

1. Lee la [Guía Completa](./THEMING_SYSTEM_GUIDE.md)
2. Estudia el [Ejemplo de Migración](./MIGRATION_EXAMPLE.md)
3. Migra tus componentes existentes
4. Comparte conocimiento con el equipo

---

## 🤝 Contribuir

### Reglas de Oro

1. **No commits con colores hardcodeados** (será rechazado en code review)
2. **Probar en ambos temas** antes de PR
3. **Documentar variables nuevas** si creas alguna
4. **Seguir nomenclatura existente** (`--component-property`)

### Code Review Checklist

Cuando revises PR de otros:

```markdown
## Theming Check

- [ ] No hay colores hardcodeados (#hex, white, black, etc.)
- [ ] Usa variables CSS del sistema
- [ ] Probado en dark mode (screenshot adjunto)
- [ ] Probado en light mode (screenshot adjunto)
- [ ] Si es JS: extiende ThemeAwareComponent correctamente
```

---

## 📞 Soporte

### Tengo una Pregunta

1. Revisa [THEMING_SYSTEM_GUIDE.md](./THEMING_SYSTEM_GUIDE.md) - sección Troubleshooting
2. Busca en el código de [ProductsTableDemo](../components/App/ProductsTableDemo/) como ejemplo
3. Pregunta al equipo en Slack #frontend-help

### Encontré un Bug

1. Verifica que `theme-variables.css` esté importado
2. Verifica que estés usando `var(--variable)` no colores directos
3. Abre un issue en GitHub con:
   - Componente afectado
   - Screenshot en dark mode
   - Screenshot en light mode
   - Código CSS relevante

### Quiero Agregar Nueva Variable

```css
/* En theme-variables.css */

/* 1. Agregar en sección apropiada */
html.dark {
    --my-new-variable: valor-dark;
}

html.light {
    --my-new-variable: valor-light;
}

/* 2. Documentar en este README */
/* 3. Agregar en THEMING_SYSTEM_GUIDE.md */
/* 4. Crear PR con justificación */
```

---

## 🎯 Objetivos del Sistema

### Corto Plazo (1-2 meses)

- ✅ Sistema implementado y documentado
- 🔄 Migrar componentes críticos (en progreso)
- 📝 Capacitar al equipo

### Mediano Plazo (3-6 meses)

- ⏳ Todos los componentes migrados
- ⏳ CSS Linter implementado
- ⏳ Pre-commit hooks activos

### Largo Plazo (6+ meses)

- ⏳ Cero colores hardcodeados en el codebase
- ⏳ Temas custom (high contrast, brand themes)
- ⏳ Design tokens exportables
- ⏳ Documentación interactiva (Storybook)

---

## 🎉 Conclusión

El Sistema de Theming de LEGO Framework te permite:

✨ **Desarrollar componentes una vez**, funcionar en todos los temas
🚀 **Sin JavaScript** para la mayoría de componentes
🎨 **Consistencia visual** automática
🔧 **Fácil mantenimiento** y escalabilidad
♿ **Mejor accesibilidad** con contraste apropiado

---

## 📚 Índice de Documentación

- **[README Principal](./THEMING_README.md)** ← Estás aquí
- **[Guía Completa del Sistema](./THEMING_SYSTEM_GUIDE.md)** - Referencia técnica detallada
- **[Ejemplo de Migración](./MIGRATION_EXAMPLE.md)** - Caso real paso a paso

---

**Última actualización:** 2025-11-02
**Versión del sistema:** 1.0.0
**Mantenido por:** LEGO Framework Team
