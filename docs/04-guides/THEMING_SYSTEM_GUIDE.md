# 🎨 Guía del Sistema de Theming LEGO Framework

## 📋 Tabla de Contenidos

1. [Visión General](#visión-general)
2. [Arquitectura](#arquitectura)
3. [Guía Rápida para Desarrolladores](#guía-rápida-para-desarrolladores)
4. [Variables Disponibles](#variables-disponibles)
5. [Migración de Componentes Existentes](#migración-de-componentes-existentes)
6. [Componentes JS con ThemeAwareComponent](#componentes-js-con-themeawarecomponent)
7. [Mejores Prácticas](#mejores-prácticas)
8. [Troubleshooting](#troubleshooting)

---

## Visión General

El **Sistema de Theming de LEGO Framework** es una solución moderna, automática y elegante para manejar temas dark/light en toda la aplicación.

### ✨ Características Principales

- **Automático**: Los componentes CSS responden automáticamente al cambio de tema sin JavaScript
- **Semántico**: Variables con nombres descriptivos (`--text-primary`, `--bg-surface`, etc.)
- **Consistente**: Sistema unificado de colores, espaciado y tipografía
- **Escalable**: Fácil de mantener y extender
- **Type-safe**: Variables bien documentadas y organizadas

### 🎯 Filosofía

> **"Simplemente usa las variables CSS y obtendrás reactividad al cambio de tema automáticamente"**

Similar a sistemas como Angular Material o Chakra UI, nuestro objetivo es que el theming sea **transparente** para el desarrollador.

---

## Arquitectura

### Estructura de Archivos

```
assets/
├── css/
│   └── core/
│       ├── base.css                  # Punto de entrada principal
│       └── theme-variables.css       # ⭐ Sistema de variables de tema
└── js/
    └── core/
        ├── modules/
        │   └── theme/
        │       └── theme-manager.js  # Gestor de cambio de tema
        └── base/
            └── ThemeAwareComponent.js # Clase base para componentes JS
```

### Flujo de Funcionamiento

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Usuario hace clic en toggle de tema                     │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. ThemeManager.toggle() → agrega/quita clase .dark en html│
└────────────────────┬────────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
        ▼                         ▼
┌──────────────────┐    ┌────────────────────────┐
│ 3A. CSS Variables│    │ 3B. JS Components      │
│ cambian AUTO     │    │ reciben notificación   │
│ (no JS needed)   │    │ via ThemeManager       │
└──────────────────┘    └────────────────────────┘
```

---

## Guía Rápida para Desarrolladores

### Para Componentes CSS (90% de los casos)

**✅ HACER:**

```css
/* my-component.css */
.my-card {
    background: var(--bg-surface);
    color: var(--text-primary);
    border: 1px solid var(--border-light);
    padding: var(--space-lg);
    border-radius: var(--radius-lg);
}

.my-card:hover {
    background: var(--bg-surface-hover);
}

.my-card__title {
    color: var(--text-primary);
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
}
```

**❌ EVITAR:**

```css
/* ❌ Colores hardcodeados - NO responden a cambio de tema */
.my-card {
    background: #ffffff;  /* ← MALO */
    color: #000000;       /* ← MALO */
    border: 1px solid #e5e5e5;  /* ← MALO */
}
```

### Para Componentes JS que necesitan lógica de tema

```javascript
// my-chart-component.js
import ThemeAwareComponent from '/assets/js/core/base/ThemeAwareComponent.js';

class ChartComponent extends ThemeAwareComponent {
    constructor(containerId) {
        super();
        this.container = document.getElementById(containerId);
        this.chart = null;
        this.initChart();
    }

    initChart() {
        // Configuración inicial con tema actual
        const options = this.getChartOptions();
        this.chart = new Chart(this.container, options);
    }

    // Override del método de la clase base
    onThemeChange(theme) {
        super.onThemeChange(theme);

        // Actualizar chart con nuevo tema
        if (this.chart) {
            this.chart.updateOptions(this.getChartOptions());
        }
    }

    getChartOptions() {
        return {
            backgroundColor: this.themeValue('#ffffff', '#1a1a1a'),
            textColor: this.themeValue('#000000', '#ffffff'),
            gridColor: this.themeValue('#e5e5e5', '#404040'),
        };
    }

    destroy() {
        if (this.chart) {
            this.chart.destroy();
        }
        super.destroy(); // Importante: limpia suscripciones
    }
}
```

---

## Variables Disponibles

### 📦 Categorías de Variables

#### 1. **Backgrounds**

| Variable | Uso | Light | Dark |
|----------|-----|-------|------|
| `--bg-body` | Fondo de la página | `#ffffff` | `#1a1a1a` |
| `--bg-surface` | Tarjetas, paneles | `#f5f5f5` | `#3a3b3c` |
| `--bg-surface-secondary` | Fondos secundarios | `#f9f9f9` | `#2d2e2f` |
| `--bg-surface-hover` | Estado hover | `#e5e5e5` | `#404040` |
| `--bg-input` | Inputs, textareas | `#ffffff` | `#2d2e2f` |

#### 2. **Text Colors**

| Variable | Uso | Light | Dark |
|----------|-----|-------|------|
| `--text-primary` | Texto principal | `#000000` | `#ffffff` |
| `--text-secondary` | Texto secundario | `#707070` | `#dddddd` |
| `--text-tertiary` | Texto terciario | `#909090` | `#b0b0b0` |
| `--text-disabled` | Texto deshabilitado | `#a0a0a0` | `#808080` |

#### 3. **Borders**

| Variable | Uso |
|----------|-----|
| `--border-light` | Bordes sutiles |
| `--border-medium` | Bordes normales |
| `--border-dark` | Bordes prominentes |
| `--border-focus` | Estado focus |
| `--border-error` | Estado error |

#### 4. **Spacing** (Inmutable - no cambia con tema)

| Variable | Valor | Uso |
|----------|-------|-----|
| `--space-xs` | `0.25rem` (4px) | Espaciado mínimo |
| `--space-sm` | `0.5rem` (8px) | Espaciado pequeño |
| `--space-md` | `0.75rem` (12px) | Espaciado medio |
| `--space-lg` | `1rem` (16px) | Espaciado grande |
| `--space-xl` | `1.5rem` (24px) | Espaciado extra grande |
| `--space-2xl` | `2rem` (32px) | Espaciado doble |
| `--space-3xl` | `3rem` (48px) | Espaciado triple |

#### 5. **Typography** (Inmutable)

| Variable | Valor |
|----------|-------|
| `--font-size-xs` | `0.75rem` (12px) |
| `--font-size-sm` | `0.8125rem` (13px) |
| `--font-size-base` | `0.875rem` (14px) |
| `--font-size-lg` | `1rem` (16px) |
| `--font-size-xl` | `1.125rem` (18px) |
| `--font-size-2xl` | `1.5rem` (24px) |
| `--font-weight-normal` | `400` |
| `--font-weight-medium` | `500` |
| `--font-weight-semibold` | `600` |
| `--font-weight-bold` | `700` |

#### 6. **Componentes Específicos**

##### Buttons

```css
--button-bg-primary
--button-bg-primary-hover
--button-text-primary
--button-bg-secondary
--button-bg-ghost
--button-padding-sm / md / lg
--button-radius
```

##### Inputs

```css
--input-bg
--input-bg-hover
--input-bg-focus
--input-text
--input-text-placeholder
--input-border
--input-border-focus
--input-padding
--input-radius
```

##### Cards

```css
--card-bg
--card-bg-hover
--card-border
--card-shadow
--card-shadow-hover
--card-padding
--card-radius
```

##### Badges

```css
--badge-success-bg
--badge-success-text
--badge-error-bg
--badge-error-text
--badge-warning-bg
--badge-info-bg
```

[Ver lista completa en `/assets/css/core/theme-variables.css`]

---

## Migración de Componentes Existentes

### Paso 1: Identificar Colores Hardcodeados

Busca en tu CSS:
```bash
# Buscar colores hex
grep -r "#[0-9a-fA-F]\{3,6\}" components/

# Buscar colores con nombre
grep -rE "(white|black|gray|red|blue)(?![a-z])" components/
```

### Paso 2: Mapear a Variables Semánticas

| Hardcoded | Variable Semántica | Razón |
|-----------|-------------------|-------|
| `#ffffff` | `var(--bg-body)` o `var(--text-primary)` | Depende del contexto |
| `#000000` | `var(--text-primary)` o `var(--bg-body)` | Depende del contexto |
| `#f5f5f5` | `var(--bg-surface)` | Fondo de superficie |
| `#e5e5e5` | `var(--border-light)` | Bordes sutiles |
| `#1a1a1a` | `var(--text-primary)` | Texto oscuro |
| `#4F46E5` | `var(--accent-primary)` | Color de marca/acento |

### Paso 3: Reemplazar

**ANTES:**

```css
.info-card {
    background: white;
    border: 1px solid #e5e7eb;
    color: #1a1a1a;
    padding: 1.5rem;
}

.info-card h3 {
    color: #1a1a1a;
    font-size: 1.1rem;
}
```

**DESPUÉS:**

```css
.info-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    color: var(--text-primary);
    padding: var(--card-padding);
}

.info-card h3 {
    color: var(--text-primary);
    font-size: var(--font-size-xl);
}
```

### Ejemplo Completo de Migración

Ver: `/components/App/ProductsTableDemo/products-table-demo.css`

**Cambios realizados:**
- ✅ 24 colores hardcodeados → Variables semánticas
- ✅ Valores de padding → Variables de espaciado
- ✅ Font sizes → Variables de tipografía
- ✅ Border radius → Variables de radius
- ✅ Resultado: **Theming automático sin JavaScript**

---

## Componentes JS con ThemeAwareComponent

### Cuándo Usar ThemeAwareComponent

**✅ USAR cuando:**
- Necesitas actualizar librerías de terceros (AG Grid, Chart.js, etc.)
- Tienes rendering en Canvas que no usa CSS
- Necesitas calcular valores dinámicamente basados en el tema
- Manejas SVG dinámicos o gráficos

**❌ NO USAR cuando:**
- Solo necesitas cambiar colores/estilos CSS (usa variables CSS directamente)
- El componente es puramente HTML/CSS

### API de ThemeAwareComponent

#### Métodos para Override

```javascript
class MyComponent extends ThemeAwareComponent {
    // Llamado cuando cambia el tema
    onThemeChange(theme) {
        super.onThemeChange(theme);
        // Tu lógica aquí
    }

    // Llamado al destruir el componente
    destroy() {
        // Tu cleanup aquí
        super.destroy(); // Importante: limpia suscripciones
    }
}
```

#### Métodos Helper

```javascript
// Obtener tema actual
this.getCurrentTheme(); // → 'light' | 'dark'

// Verificaciones booleanas
this.isDarkMode();  // → true | false
this.isLightMode(); // → true | false

// Ejecutar callbacks condicionales
this.whenDark(() => {
    // Solo se ejecuta en dark mode
});

this.whenLight(() => {
    // Solo se ejecuta en light mode
});

// Pattern matching para valores
const backgroundColor = this.themeValue('#ffffff', '#1a1a1a');
// Si light → '#ffffff'
// Si dark → '#1a1a1a'
```

### Ejemplo: Integración con AG Grid

```javascript
import ThemeAwareComponent from '/assets/js/core/base/ThemeAwareComponent.js';

class TableManager extends ThemeAwareComponent {
    constructor(tableId) {
        super();
        this.tableId = tableId;
        this.gridApi = null;
        this.initGrid();
    }

    initGrid() {
        const gridOptions = {
            // ... opciones de AG Grid
        };

        this.gridApi = agGrid.createGrid(
            document.getElementById(this.tableId),
            gridOptions
        );

        // Aplicar tema inicial
        this.syncTheme();
    }

    onThemeChange(theme) {
        super.onThemeChange(theme);
        this.syncTheme();
    }

    syncTheme() {
        const container = document.getElementById(this.tableId);

        this.whenDark(() => {
            container.className = 'ag-theme-alpine-dark';
        });

        this.whenLight(() => {
            container.className = 'ag-theme-alpine';
        });
    }

    destroy() {
        if (this.gridApi) {
            this.gridApi.destroy();
        }
        super.destroy();
    }
}
```

---

## Mejores Prácticas

### ✅ DO's

1. **Siempre usa variables semánticas**
   ```css
   color: var(--text-primary);
   background: var(--bg-surface);
   ```

2. **Usa variables de espaciado para consistencia**
   ```css
   padding: var(--space-lg);
   margin-bottom: var(--space-xl);
   ```

3. **Usa variables de componente cuando existan**
   ```css
   background: var(--card-bg);
   border-radius: var(--card-radius);
   ```

4. **Llama a `super.destroy()` en componentes JS**
   ```javascript
   destroy() {
       // Tu cleanup
       super.destroy(); // ← Importante
   }
   ```

5. **Documenta componentes migrados**
   ```css
   /* ✅ MIGRADO AL SISTEMA DE THEMING */
   .my-component { ... }
   ```

### ❌ DON'Ts

1. **Nunca uses colores hardcodeados**
   ```css
   /* ❌ MALO */
   color: #000000;
   background: white;
   border: 1px solid #e5e5e5;
   ```

2. **No uses `@media (prefers-color-scheme)` directamente**
   ```css
   /* ❌ MALO - no responde a toggle manual */
   @media (prefers-color-scheme: dark) {
       .card { background: #1a1a1a; }
   }
   ```

3. **No olvides desuscribirse**
   ```javascript
   // ❌ MALO - memory leak
   destroy() {
       // Cleanup pero sin llamar super.destroy()
   }
   ```

4. **No extiendas ThemeAwareComponent si no lo necesitas**
   ```javascript
   // ❌ MALO - usa CSS variables directamente
   class SimpleCard extends ThemeAwareComponent { }
   ```

---

## Troubleshooting

### Problema: Los colores no cambian al hacer toggle

**Diagnóstico:**
```javascript
// En console del browser
console.log(document.documentElement.classList);
// Debe mostrar "dark" o "light"

console.log(getComputedStyle(document.documentElement).getPropertyValue('--text-primary'));
// Debe cambiar cuando haces toggle
```

**Soluciones:**
1. Verifica que `theme-variables.css` esté importado en `base.css`
2. Verifica que estés usando `var(--text-primary)` no `#000000`
3. Limpia caché del browser (Ctrl+Shift+R)

### Problema: ThemeManager no está disponible

**Error:**
```
Cannot read property 'subscribe' of undefined
```

**Solución:**
```javascript
// ThemeAwareComponent maneja esto automáticamente
// Espera hasta 5 segundos a que ThemeManager cargue

// Si necesitas acceso manual:
if (window.themeManager) {
    window.themeManager.subscribe(callback);
} else {
    console.warn('ThemeManager no disponible');
}
```

### Problema: Variables no definidas

**Error en CSS:**
```
Cannot read property of undefined (var(--my-variable))
```

**Solución:**
1. Verifica el nombre de la variable en `/assets/css/core/theme-variables.css`
2. Asegúrate de que `base.css` esté importando `theme-variables.css`
3. Usa fallback: `var(--my-variable, #fallback)`

---

## Recursos Adicionales

- 📄 **Código Fuente**: `/assets/css/core/theme-variables.css`
- 📄 **Clase Base JS**: `/assets/js/core/base/ThemeAwareComponent.js`
- 📄 **ThemeManager**: `/assets/js/core/modules/theme/theme-manager.js`
- 📝 **Ejemplo de Migración**: `/components/App/ProductsTableDemo/`

---

## Contribuir

Al crear nuevos componentes:

1. ✅ **Usa variables semánticas desde el inicio**
2. ✅ **No uses colores hardcodeados**
3. ✅ **Documenta variables custom si creas nuevas**
4. ✅ **Prueba en ambos temas (light/dark)**

---

## Resumen

```
┌──────────────────────────────────────────────────────────────┐
│  REGLA DE ORO DEL THEMING                                    │
│                                                               │
│  "Si usas colores hardcodeados, estás haciendo algo mal"    │
│                                                               │
│  Usa var(--variable-semantica) y obtendrás theming          │
│  automático sin esfuerzo adicional.                          │
└──────────────────────────────────────────────────────────────┘
```

**Beneficios del Sistema:**
- 🎨 Theming automático
- 🔧 Fácil mantenimiento
- 📏 Consistencia visual
- 🚀 Escalable
- ♿ Mejor accesibilidad
- 💡 Developer experience superior

¡Feliz desarrollo con LEGO Framework! 🎉
