# 🎨 Contrato de Estilos CSS - LegoPHP Framework

> **⚠️ OBLIGATORIO:** Revisar antes de escribir cualquier CSS

## 🎯 Propósito
Este contrato define las reglas estrictas para escribir CSS escalable y mantenible en el framework LegoPHP.

---

## ✅ QUE SÍ HACER

### 🎨 COLORES
- **SIEMPRE usar variables CSS predefinidas:**
  ```css
  /* ✅ CORRECTO */
  color: var(--text-primary);
  background: var(--bg-surface);
  border-color: var(--border-light);
  ```

### 📏 TAMAÑOS
- **SIEMPRE usar rem** para escalabilidad:
  ```css
  /* ✅ CORRECTO */
  width: 15rem;
  padding: var(--space-md);
  font-size: var(--font-size-base);
  ```

### 📐 ESPACIADOS
- **Usar variables de espaciado predefinidas:**
  ```css
  /* Variables disponibles */
  --space-xs: 0.25rem;   /* 4px */
  --space-sm: 0.5rem;    /* 8px */
  --space-md: 0.75rem;   /* 12px */
  --space-lg: 1rem;      /* 16px */
  --space-xl: 1.5rem;    /* 24px */
  --menu-item-spacing: 0.375rem; /* Para elementos de menú */
  ```

### ⚡ TRANSICIONES
- **Usar variables predefinidas:**
  ```css
  --transition-fast: all 0.2s ease;
  --transition-normal: all 0.3s ease;
  --transition-slow: all 0.5s ease;
  ```

---

## ❌ QUE NO HACER

### 🚫 COLORES HARDCODED
```css
/* ❌ INCORRECTO */
color: #000000;
background: white;
border-color: #cccccc;
```

### 🚫 PIXELES
```css
/* ❌ INCORRECTO */
width: 240px;
padding: 12px;
font-size: 14px;
```

### 🚫 VALORES MÁGICOS
```css
/* ❌ INCORRECTO */
margin: 13px;
padding: 7px;
border-radius: 5px;
```

---

## 📋 CHECKLIST ANTES DEL COMMIT

### ✅ Verificación CSS
- [ ] ¿Usé solo variables CSS para colores?
- [ ] ¿Todo está en rem en lugar de px?
- [ ] ¿Usé variables de espaciado predefinidas?
- [ ] ¿Las transiciones usan las variables estándar?
- [ ] ¿El código funcionará en dark/light mode?
- [ ] ¿Es responsive desde mobile hasta desktop?

---

## 🆕 CREAR NUEVAS VARIABLES

### Cuándo crear:
1. **Color:** Si necesitas un color que no existe en la paleta
2. **Tamaño:** Si necesitas un tamaño específico que se reutilizará
3. **Espaciado:** Si ninguna variable de espaciado actual funciona

### Proceso:
1. Verificar que no existe una similar
2. Añadir a `assets/css/core/base.css` con nombre semántico
3. Documentar en comentarios su propósito
4. Usar inmediatamente en lugar del valor hardcoded

---

## 🚀 RECURSOS RÁPIDOS

### Variables más usadas:
```css
/* Colores */
--text-primary, --text-secondary
--bg-body, --bg-sidebar, --bg-surface
--accent-primary, --accent-hover

/* Tamaños */
--sidebar-width, --menu-item-height
--font-size-base, --font-size-lg

/* Espacios */
--space-sm, --space-md
--menu-item-spacing
```

### Archivo clave:
- `assets/css/core/base.css` - Variables globales

---

> **💡 Recuerda:** Estas reglas hacen el desarrollo MÁS RÁPIDO y CONSISTENTE. Un cambio global se hace cambiando una variable.

**Última actualización:** 13 de Septiembre 2025