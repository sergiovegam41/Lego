# LEGO CSS Theming Standard

## 📋 Resumen

Este documento define el estándar único para manejar temas (dark/light mode) en todo el framework LEGO.

---

## ✅ Patrón Correcto: `html.dark` / `html.light`

```css
/* Para estilos específicos de dark mode */
html.dark .mi-componente {
    background: var(--bg-surface);
    color: var(--text-primary);
}

/* Para estilos específicos de light mode */
html.light .mi-componente {
    background: var(--bg-surface);
    color: var(--text-primary);
}
```

---

## ❌ Patrones Prohibidos

### 1. NO usar `body.dark`
```css
/* ❌ INCORRECTO */
body.dark .mi-componente { ... }

/* ✅ CORRECTO */
html.dark .mi-componente { ... }
```

### 2. NO usar `@media (prefers-color-scheme: dark)`
```css
/* ❌ INCORRECTO - No respeta el toggle manual de tema */
@media (prefers-color-scheme: dark) {
    .mi-componente { ... }
}

/* ✅ CORRECTO */
html.dark .mi-componente { ... }
```

### 3. NO usar colores hardcodeados sin fallback
```css
/* ❌ INCORRECTO */
.mi-componente {
    color: #ffffff;
    background: #1a1a1a;
}

/* ✅ CORRECTO - Usar variables CSS */
.mi-componente {
    color: var(--text-primary);
    background: var(--bg-surface);
}

/* ✅ TAMBIÉN CORRECTO - Variable con fallback */
.mi-componente {
    color: var(--text-primary, #ffffff);
}
```

---

## 🎨 Variables Semánticas Disponibles

### Backgrounds
| Variable | Uso |
|----------|-----|
| `--bg-body` | Fondo principal de la app |
| `--bg-surface` | Superficies elevadas (cards, modals) |
| `--bg-surface-secondary` | Superficies secundarias |
| `--bg-surface-tertiary` | Superficies terciarias |
| `--bg-surface-hover` | Estado hover de superficies |
| `--bg-input` | Fondo de inputs |

### Texto
| Variable | Uso |
|----------|-----|
| `--text-primary` | Texto principal |
| `--text-secondary` | Texto secundario/muted |
| `--text-tertiary` | Texto terciario |
| `--text-on-accent` | Texto sobre colores de acento |
| `--text-on-focus` | Texto sobre elementos con focus |

### Bordes
| Variable | Uso |
|----------|-----|
| `--border-light` | Bordes sutiles |
| `--border-medium` | Bordes normales |
| `--border-dark` | Bordes fuertes |
| `--border-focus` | Bordes de focus |

### Colores de Estado (Alias)
| Variable | Uso |
|----------|-----|
| `--color-primary` | Color primario/acento |
| `--color-success` | Éxito/Verde |
| `--color-danger` | Error/Rojo |
| `--color-warning` | Advertencia/Amarillo |
| `--color-info` | Información/Azul |

### Estados Interactivos
| Variable | Uso |
|----------|-----|
| `--state-hover` | Color de hover genérico |
| `--state-active` | Color de active/pressed |
| `--state-focus` | Color de focus ring |

---

## 🔧 Ejemplo Completo de Componente

```css
/**
 * MiComponente - Estilos
 *
 * FILOSOFÍA LEGO:
 * Usa variables CSS semánticas y el patrón html.dark/html.light
 */

.mi-componente {
    /* Estilos base que funcionan en ambos temas */
    background: var(--bg-surface);
    color: var(--text-primary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: var(--space-lg);
    transition: var(--transition-fast);
}

.mi-componente:hover {
    background: var(--bg-surface-hover);
    border-color: var(--border-medium);
}

.mi-componente__title {
    color: var(--text-primary);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
}

.mi-componente__description {
    color: var(--text-secondary);
    font-size: var(--font-size-base);
}

/* Solo si necesitas estilos MUY específicos para un tema */
html.dark .mi-componente {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

html.light .mi-componente {
    box-shadow: var(--shadow-md);
}
```

---

## 📁 Referencia de Archivos

- **Variables Base**: `assets/css/core/theme-variables.css`
- **Estilos Base**: `assets/css/core/base.css`
- **Ejemplo Canónico**: `components/App/ExampleCrud/example-crud.css`

---

## ✨ Beneficios de Este Estándar

1. **Consistencia**: Un solo patrón en todo el proyecto
2. **Toggle Manual**: Respeta la preferencia del usuario almacenada en localStorage
3. **Rendimiento**: Sin re-renders de JS, solo CSS
4. **Mantenibilidad**: Cambios centralizados en theme-variables.css
5. **Escalabilidad**: Fácil añadir nuevas variables o temas

---

*Última actualización: 2025-11-29*

