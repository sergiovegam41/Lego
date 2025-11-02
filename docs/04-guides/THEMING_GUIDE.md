# Guía de Theming - Framework LEGO

**Filosofía:** "Las distancias importan más que los valores absolutos"

Esta guía establece las reglas y patrones para implementar theming correcto en el framework LEGO.

## 📋 Índice

1. [Principios Fundamentales](#principios-fundamentales)
2. [Estructura Correcta](#estructura-correcta)
3. [Errores Comunes](#errores-comunes)
4. [Variables CSS](#variables-css)
5. [Ejemplos Prácticos](#ejemplos-prácticos)
6. [Validación](#validación)

---

## 🎯 Principios Fundamentales

### Regla de Oro

**✅ USAR:** `html.dark` y `html.light` como selectores base
**❌ NO USAR:** `@media (prefers-color-scheme: dark)`

### ¿Por qué?

- `@media prefers-color-scheme` **solo responde al sistema operativo**
- **NO responde a toggles manuales** del usuario en la app
- `html.dark` y `html.light` son **controlables por JavaScript**
- Permite **preferencia del usuario independiente del sistema**

---

## ✅ Estructura Correcta

### Patrón Básico

```css
/* ═══════════════════════════════════════════════════════════════════
   THEMING - LIGHT MODE
   ═══════════════════════════════════════════════════════════════ */

html.light .my-component {
    --bg-surface: #ffffff;
    --text-primary: #1a1a1a;
    --border-default: #e0e0e0;
}

/* ═══════════════════════════════════════════════════════════════════
   THEMING - DARK MODE
   ═══════════════════════════════════════════════════════════════ */

html.dark .my-component {
    --bg-surface: #1a1a1a;
    --text-primary: #f5f5f5;
    --border-default: #404040;
}

/* ═══════════════════════════════════════════════════════════════════
   ESTILOS BASE (usando variables)
   ═══════════════════════════════════════════════════════════════ */

.my-component {
    background: var(--bg-surface);
    color: var(--text-primary);
    border: 1px solid var(--border-default);
}
```

### Consistencia Dimensional

**Las distancias importan:** Mantén las mismas proporciones entre modos.

```css
/* ✅ CORRECTO: Misma estructura, diferentes valores */
html.light .button {
    --btn-padding: 8px 16px;
    --btn-radius: 6px;
    --btn-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

html.dark .button {
    --btn-padding: 8px 16px;      /* ✅ Misma distancia */
    --btn-radius: 6px;             /* ✅ Mismo radio */
    --btn-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);  /* ✅ Misma estructura, diferente opacidad */
}
```

---

## ❌ Errores Comunes

### Error 1: Usar @media prefers-color-scheme

```css
/* ❌ INCORRECTO */
@media (prefers-color-scheme: dark) {
    .my-component {
        background: #1a1a1a;
    }
}

/* ✅ CORRECTO */
html.dark .my-component {
    background: var(--bg-surface);
}
```

**Por qué es incorrecto:**
- No responde a toggle manual
- Ignora preferencia del usuario en la app
- Causa bugs cuando el usuario prefiere dark mode independiente del sistema

---

### Error 2: Usar body.dark en lugar de html.dark

```css
/* ❌ INCORRECTO */
body.dark .my-component {
    background: #1a1a1a;
}

/* ✅ CORRECTO */
html.dark .my-component {
    background: var(--bg-surface);
}
```

**Por qué es incorrecto:**
- El sistema usa `html.dark` como estándar
- Inconsistente con el resto del framework
- Puede causar problemas de especificidad CSS

---

### Error 3: Colores hardcodeados sin variables

```css
/* ❌ INCORRECTO */
.my-component {
    background: #ffffff;  /* Hardcodeado */
    color: #1a1a1a;       /* Hardcodeado */
}

html.dark .my-component {
    background: #1a1a1a;  /* Duplicación */
    color: #f5f5f5;       /* Duplicación */
}

/* ✅ CORRECTO */
html.light .my-component {
    --bg-surface: #ffffff;
    --text-primary: #1a1a1a;
}

html.dark .my-component {
    --bg-surface: #1a1a1a;
    --text-primary: #f5f5f5;
}

.my-component {
    background: var(--bg-surface);
    color: var(--text-primary);
}
```

---

### Error 4: Solo definir un modo (dark o light)

```css
/* ❌ INCORRECTO - Solo dark mode */
html.dark .my-component {
    --bg-surface: #1a1a1a;
}

/* ❌ Falta light mode! */

/* ✅ CORRECTO - Ambos modos */
html.light .my-component {
    --bg-surface: #ffffff;
}

html.dark .my-component {
    --bg-surface: #1a1a1a;
}
```

---

## 🎨 Variables CSS

### Nomenclatura Estándar

```css
/* Backgrounds */
--bg-surface              /* Fondo principal */
--bg-surface-secondary    /* Fondo secundario */
--bg-overlay              /* Overlay/modal background */

/* Text */
--text-primary            /* Texto principal */
--text-secondary          /* Texto secundario */
--text-disabled           /* Texto deshabilitado */

/* Borders */
--border-default          /* Borde por defecto */
--border-hover            /* Borde en hover */
--border-focus            /* Borde en focus */

/* Colors */
--color-primary           /* Color primario */
--color-primary-hover     /* Primary en hover */
--color-danger            /* Color de error/peligro */
--color-success           /* Color de éxito */
--color-warning           /* Color de advertencia */

/* Spacing */
--space-xs: 4px
--space-sm: 8px
--space-md: 16px
--space-lg: 24px
--space-xl: 32px
```

### Ejemplo Completo

```css
html.light .products-crud-v3 {
    /* Backgrounds */
    --bg-surface: #ffffff;
    --bg-surface-secondary: #f5f5f5;

    /* Text */
    --text-primary: #1a1a1a;
    --text-secondary: #666666;

    /* Borders */
    --border-default: #e0e0e0;
    --border-hover: #bdbdbd;

    /* Colors */
    --color-primary: #2563eb;
    --color-primary-hover: #1d4ed8;
    --color-danger: #dc2626;
}

html.dark .products-crud-v3 {
    /* Backgrounds */
    --bg-surface: #1a1a1a;
    --bg-surface-secondary: #2a2a2a;

    /* Text */
    --text-primary: #f5f5f5;
    --text-secondary: #a0a0a0;

    /* Borders */
    --border-default: #404040;
    --border-hover: #525252;

    /* Colors */
    --color-primary: #3b82f6;
    --color-primary-hover: #2563eb;
    --color-danger: #ef4444;
}
```

---

## 💡 Ejemplos Prácticos

### Ejemplo 1: Componente Simple

```css
/* ═══════════════════════════════════════════════════════════════════
   BUTTON COMPONENT
   ═══════════════════════════════════════════════════════════════ */

/* LIGHT MODE */
html.light .lego-button {
    --btn-bg: #2563eb;
    --btn-bg-hover: #1d4ed8;
    --btn-text: #ffffff;
    --btn-border: transparent;
}

/* DARK MODE */
html.dark .lego-button {
    --btn-bg: #3b82f6;
    --btn-bg-hover: #2563eb;
    --btn-text: #ffffff;
    --btn-border: transparent;
}

/* ESTILOS BASE */
.lego-button {
    background: var(--btn-bg);
    color: var(--btn-text);
    border: 1px solid var(--btn-border);
    padding: var(--space-sm) var(--space-md);
    border-radius: 6px;
    transition: all 0.2s ease;
}

.lego-button:hover {
    background: var(--btn-bg-hover);
}
```

---

### Ejemplo 2: Componente con Estados

```css
/* LIGHT MODE */
html.light .input-field {
    --input-bg: #ffffff;
    --input-border: #e0e0e0;
    --input-border-focus: #2563eb;
    --input-border-error: #dc2626;
    --input-text: #1a1a1a;
}

/* DARK MODE */
html.dark .input-field {
    --input-bg: #2a2a2a;
    --input-border: #404040;
    --input-border-focus: #3b82f6;
    --input-border-error: #ef4444;
    --input-text: #f5f5f5;
}

/* ESTILOS BASE */
.input-field {
    background: var(--input-bg);
    color: var(--input-text);
    border: 1px solid var(--input-border);
}

.input-field:focus {
    border-color: var(--input-border-focus);
}

.input-field--error {
    border-color: var(--input-border-error);
}
```

---

### Ejemplo 3: Componente con Sombras

```css
/* LIGHT MODE */
html.light .card {
    --card-bg: #ffffff;
    --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    --card-shadow-hover: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* DARK MODE */
html.dark .card {
    --card-bg: #1a1a1a;
    --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    --card-shadow-hover: 0 4px 12px rgba(0, 0, 0, 0.5);
}

/* ESTILOS BASE */
.card {
    background: var(--card-bg);
    box-shadow: var(--card-shadow);
    transition: box-shadow 0.2s ease;
}

.card:hover {
    box-shadow: var(--card-shadow-hover);
}
```

---

## 🔍 Validación

### Script Automático

Ejecuta el validador de theming para detectar errores:

```bash
node scripts/validate-theming.js
```

### Errores que Detecta

1. ❌ Uso de `@media (prefers-color-scheme)`
2. ❌ Uso de `body.dark` / `body.light`
3. ⚠️ Colores hardcodeados sin variables
4. ⚠️ Falta de modo dark o light

### Integración en CI/CD

```yaml
# .github/workflows/validate-theming.yml
name: Validate Theming

on: [push, pull_request]

jobs:
  validate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-node@v2
      - run: npm install
      - run: npm run validate:theming
```

---

## 🎓 Checklist de Theming

Antes de hacer commit, verifica:

- [ ] ✅ Usa `html.dark` y `html.light` (NO `@media prefers-color-scheme`)
- [ ] ✅ Define variables CSS para todos los colores
- [ ] ✅ Ambos modos (dark y light) están implementados
- [ ] ✅ Mantiene las mismas "distancias" (proporciones) entre modos
- [ ] ✅ Usa nomenclatura estándar de variables (`--bg-*`, `--text-*`, etc.)
- [ ] ✅ Pasa validación: `node scripts/validate-theming.js`

---

## 📚 Referencias

- [PROPUESTA_PRODUCTSCRUDV3.md](../PROPUESTA_PRODUCTSCRUDV3.md) - Sección de Theming
- [ProductsCrudV3 CSS](../components/App/ProductsCrudV3/products-crud-v3.css) - Ejemplo de referencia
- [Validador](../scripts/validate-theming.js) - Script de validación

---

**Recuerda:** "Las distancias importan más que los valores absolutos"

Mantén consistencia en proporciones, spacing y estructura visual entre dark y light mode.
