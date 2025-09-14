# ⚡ Contrato de Optimización de Rendimiento - LegoPHP Framework

> **⚠️ OBLIGATORIO:** Revisar antes de implementar funcionalidad que afecte el rendimiento

## 🎯 Propósito
Este contrato define las reglas para mantener el framework rápido, eficiente y con una experiencia de usuario fluida.

---

## ✅ QUE SÍ HACER

### 🎭 Transiciones Eficientes
- **Usar variables predefinidas:**
  ```css
  /* ✅ CORRECTO */
  .button {
    transition: var(--transition-fast);
  }

  .modal {
    transition: var(--transition-normal);
  }

  .page-change {
    transition: var(--transition-slow);
  }
  ```

- **Variables disponibles:**
  ```css
  --transition-fast: all 0.2s ease;     /* Botones, hovers */
  --transition-normal: all 0.3s ease;   /* Modales, menús */
  --transition-slow: all 0.5s ease;     /* Cambios de página */
  ```

### 🚀 Carga Optimizada
- **Lazy loading** para imágenes y componentes pesados
- **Minificación** de CSS y JavaScript en producción
- **Compresión** de recursos estáticos

```css
/* ✅ CORRECTO - Optimizar animaciones */
.animated-element {
  will-change: transform;
  transform: translateZ(0); /* Forzar hardware acceleration */
}
```

### 🎯 CSS Eficiente
- **Evitar selectores complejos**
- **Usar transform y opacity** para animaciones
- **Minimizar reflows y repaints**

```css
/* ✅ CORRECTO */
.element {
  transform: translateX(100px);
  opacity: 0;
}

/* En lugar de */
/* ❌ INCORRECTO */
.element {
  left: 100px;
  display: none;
}
```

---

## ❌ QUE NO HACER

### 🚫 Transiciones Costosas
```css
/* ❌ INCORRECTO */
.element {
  transition: all 1s ease; /* Muy lento */
  transition: width 0.3s, height 0.3s, left 0.3s; /* Múltiples reflows */
}
```

### 🚫 Animaciones Que Causan Reflow
```css
/* ❌ INCORRECTO - Causa reflow */
.element {
  transition: width 0.3s;
  transition: height 0.3s;
  transition: top 0.3s;
  transition: left 0.3s;
}
```

### 🚫 Selectores Ineficientes
```css
/* ❌ INCORRECTO */
div > ul > li > a:hover span.icon { ... }
#content .sidebar ul li a:nth-child(odd) { ... }
```

### 🚫 JavaScript Bloquante
```javascript
// ❌ INCORRECTO
for (let i = 0; i < 1000; i++) {
  document.getElementById('element').style.left = i + 'px';
}
```

---

## 📊 MÉTRICAS DE RENDIMIENTO

### 🎯 Objetivos Mínimos
- **First Contentful Paint:** < 2s
- **Time to Interactive:** < 3s
- **Cumulative Layout Shift:** < 0.1
- **Largest Contentful Paint:** < 2.5s

### ⚡ Tiempos de Transición
- **Micro-interacciones:** 0.1-0.2s (hover, click)
- **Transiciones normales:** 0.2-0.3s (menús, modales)
- **Cambios de contexto:** 0.3-0.5s (páginas, vistas)

---

## 📋 CHECKLIST ANTES DEL COMMIT

### ✅ Verificación de CSS
- [ ] ¿Usé solo las variables de transición predefinidas?
- [ ] ¿Las animaciones usan transform y opacity?
- [ ] ¿Evité animaciones de width, height, top, left?
- [ ] ¿Los selectores son simples y específicos?
- [ ] ¿No hay selectores innecesariamente complejos?

### ✅ Verificación de JavaScript
- [ ] ¿Evité manipular el DOM en bucles?
- [ ] ¿Usé requestAnimationFrame para animaciones?
- [ ] ¿Implementé debounce/throttle donde es necesario?
- [ ] ¿No hay console.log en el código final?

### ✅ Verificación General
- [ ] ¿La funcionalidad es responsive en 60fps?
- [ ] ¿Probé en dispositivos de gama baja?
- [ ] ¿No hay memory leaks en event listeners?

---

## 🔧 TÉCNICAS DE OPTIMIZACIÓN

### 🎭 Animaciones Hardware-Accelerated
```css
/* ✅ Forzar GPU acceleration */
.gpu-optimized {
  transform: translateZ(0);
  will-change: transform;
}

/* ✅ Animaciones optimizadas */
.slide-in {
  transform: translateX(-100%);
  transition: var(--transition-fast);
}

.slide-in.active {
  transform: translateX(0);
}
```

### ⚡ JavaScript Optimizado
```javascript
// ✅ CORRECTO - Batch DOM changes
function updateElements(elements, values) {
  requestAnimationFrame(() => {
    elements.forEach((el, i) => {
      el.style.transform = `translateX(${values[i]}px)`;
    });
  });
}

// ✅ CORRECTO - Debounce expensive operations
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}
```

### 🖼️ Carga de Recursos
```html
<!-- ✅ Lazy loading -->
<img loading="lazy" src="image.jpg" alt="Description">

<!-- ✅ Preload critical resources -->
<link rel="preload" href="critical.css" as="style">
```

---

## 🧪 HERRAMIENTAS DE TESTING

### 🔍 Análisis de Rendimiento
1. **Chrome DevTools** - Performance tab
2. **Lighthouse** - Web performance audit
3. **WebPageTest** - Real-world testing

### 📱 Testing en Dispositivos
- **Chrome DevTools** - CPU throttling
- **Dispositivos reales** - Especialmente gama baja
- **Diferentes conexiones** - 3G, WiFi lento

---

## 🚀 RECURSOS RÁPIDOS

### Variables de transición:
```css
--transition-fast: all 0.2s ease;
--transition-normal: all 0.3s ease;
--transition-slow: all 0.5s ease;
```

### Propiedades animables eficientes:
- `transform` (translateX, translateY, scale, rotate)
- `opacity`
- `filter` (blur, brightness, etc.)
- `clip-path`

### Archivos clave:
- `assets/css/core/base.css` - Variables de transición
- `assets/js/core/performance.js` - Utilidades de rendimiento

---

> **💡 Recuerda:** La optimización de rendimiento no es opcional. Una interfaz lenta es una interfaz rota.

**Última actualización:** 13 de Septiembre 2025