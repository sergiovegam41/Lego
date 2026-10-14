# 📱 Contrato de Diseño Responsive - LegoPHP Framework

> **⚠️ OBLIGATORIO:** Revisar antes de implementar cualquier interfaz

## 🎯 Propósito
Este contrato define las reglas para crear interfaces que funcionen perfectamente en todos los dispositivos, desde móviles hasta desktop.

---

## ✅ QUE SÍ HACER

### 📱 Enfoque Mobile-First
- **Diseñar primero para móviles**
- **Escalar hacia arriba** con media queries
- **Base rem escalable** desde `html { font-size: 16px }`

```css
/* ✅ CORRECTO - Mobile-first */
.component {
  width: 100%;
  padding: var(--space-sm);
}

@media (min-width: 768px) {
  .component {
    width: 50%;
    padding: var(--space-md);
  }
}

@media (min-width: 1024px) {
  .component {
    width: 33.333%;
    padding: var(--space-lg);
  }
}
```

### 📏 Unidades Escalables
- **rem para tamaños** que deben escalar con el navegador
- **em para espaciados** relativos al elemento padre
- **% para anchos** flexibles
- **vw/vh para viewports** cuando sea necesario

```css
/* ✅ CORRECTO */
.container {
  width: 100%;
  max-width: 75rem;
  padding: var(--space-md);
  font-size: var(--font-size-base);
}
```

### 🔧 Flexibilidad en Layouts
- **Flexbox** para alineaciones y distribución
- **CSS Grid** para layouts complejos
- **Variables** para breakpoints consistentes

---

## ❌ QUE NO HACER

### 🚫 Diseño Desktop-First
```css
/* ❌ INCORRECTO */
.component {
  width: 33.333%;
}

@media (max-width: 768px) {
  .component {
    width: 100%;
  }
}
```

### 🚫 Tamaños Fijos
```css
/* ❌ INCORRECTO */
.sidebar {
  width: 250px;
  height: 600px;
}
```

### 🚫 Breakpoints Hardcoded
```css
/* ❌ INCORRECTO */
@media (max-width: 767px) { ... }
@media (min-width: 768px) and (max-width: 1023px) { ... }
@media (min-width: 1024px) { ... }
```

---

## 📐 BREAKPOINTS ESTÁNDAR

### 📱 Dispositivos Base
```css
/* Mobile */
/* Base styles - no media query needed */

/* Tablet */
@media (min-width: 48rem) { /* 768px */ }

/* Desktop */
@media (min-width: 64rem) { /* 1024px */ }

/* Large Desktop */
@media (min-width: 80rem) { /* 1280px */ }
```

### 🎯 Puntos de Quiebre Semánticos
- **Mobile:** 0 - 47.9rem (0 - 767px)
- **Tablet:** 48rem - 63.9rem (768px - 1023px)
- **Desktop:** 64rem - 79.9rem (1024px - 1279px)
- **Large:** 80rem+ (1280px+)

---

## 📋 CHECKLIST ANTES DEL COMMIT

### ✅ Verificación Responsive
- [ ] ¿Probé en móvil (320px-768px)?
- [ ] ¿Probé en tablet (768px-1024px)?
- [ ] ¿Probé en desktop (1024px+)?
- [ ] ¿Los textos son legibles en todos los tamaños?
- [ ] ¿Los botones tienen tamaño táctil adecuado (44px mínimo)?
- [ ] ¿No hay scroll horizontal en ningún dispositivo?

### ✅ Verificación Técnica
- [ ] ¿Usé rem en lugar de px?
- [ ] ¿Seguí mobile-first approach?
- [ ] ¿Los breakpoints son consistentes?
- [ ] ¿Las imágenes son responsive?
- [ ] ¿Los menús funcionan en touch y desktop?

---

## 🎨 PATRONES RESPONSIVE COMUNES

### 📊 Grid Adaptativo
```css
.grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-md);
}

@media (min-width: 48rem) {
  .grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 64rem) {
  .grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
```

### 📱 Navegación Adaptativa
```css
.nav {
  display: none; /* Mobile: hidden by default */
}

.nav.open {
  display: block;
}

@media (min-width: 48rem) {
  .nav {
    display: flex; /* Tablet+: always visible */
  }
}
```

### 📏 Tipografía Escalable
```css
.title {
  font-size: var(--font-size-lg);
}

@media (min-width: 48rem) {
  .title {
    font-size: var(--font-size-xl);
  }
}

@media (min-width: 64rem) {
  .title {
    font-size: var(--font-size-2xl);
  }
}
```

---

## 🧪 TESTING RESPONSIVE

### 🔍 Herramientas de Prueba
1. **DevTools del navegador** - Vista responsive
2. **Dispositivos reales** - iPhone, Android, iPad
3. **Diferentes navegadores** - Chrome, Firefox, Safari

### 📱 Dispositivos Críticos
- **iPhone SE** (375px) - Móvil pequeño
- **iPhone 12/13** (390px) - Móvil estándar
- **iPad** (768px) - Tablet vertical
- **iPad Landscape** (1024px) - Tablet horizontal
- **MacBook** (1440px) - Desktop estándar

---

## 🚀 RECURSOS RÁPIDOS

### Variables útiles:
```css
/* Para crear cuando sea necesario */
--breakpoint-mobile: 48rem;
--breakpoint-tablet: 64rem;
--breakpoint-desktop: 80rem;

--touch-target-min: 2.75rem; /* 44px */
--content-max-width: 75rem;   /* 1200px */
```

---

> **💡 Recuerda:** Un diseño responsive no es solo "que se vea bien", debe ser usable y eficiente en cada dispositivo.

**Última actualización:** 13 de Septiembre 2025