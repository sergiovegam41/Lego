# =Ë Contrato de Implementación - LegoPHP Framework

> **  OBLIGATORIO:** Revisar este documento antes de implementar cualquier funcionalidad

## <¯ Propósito
Este contrato define las reglas y estándares que deben seguirse en todo desarrollo del framework LegoPHP para garantizar escalabilidad, mantenibilidad y consistencia.

---

## <¨ REGLAS DE CSS

###  COLORES - OBLIGATORIO
- **NUNCA usar colores hardcoded** (`#ffffff`, `rgb()`, etc.)
- **SIEMPRE usar variables CSS predefinidas:**
  ```css
  /*  CORRECTO */
  color: var(--text-primary);
  background: var(--bg-surface);
  border-color: var(--border-light);
  
  /* L INCORRECTO */
  color: #000000;
  background: white;
  border-color: #cccccc;
  ```

### =Ï TAMAÑOS - OBLIGATORIO  
- **NUNCA usar pixeles** (`px`) para tamaños
- **SIEMPRE usar rem** para escalabilidad:
  ```css
  /*  CORRECTO */
  width: 15rem;
  padding: var(--space-md);
  font-size: var(--font-size-base);
  
  /* L INCORRECTO */
  width: 240px;
  padding: 12px;
  font-size: 14px;
  ```

### = ESPACIADOS - OBLIGATORIO
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

---

## <× REGLAS DE COMPONENTES

### =Á Estructura de Archivos
- **Cada componente debe tener:** `ComponentName.php`, `component.css`, `component.js`
- **Ubicación:** `Views/[Feature]/Components/[ComponentName]/`
- **Nomenclatura:** PascalCase para componentes

### =' Variables CSS
- **Nuevas variables:** Añadir a `assets/css/core/base.css` en la sección correspondiente
- **Semánticas:** Usar nombres descriptivos (`--bg-surface` no `--gray-light`)
- **Escalabilidad:** Agrupar por categorías (colores, tamaños, espaciados)

---

## ¡ REGLAS DE RENDIMIENTO

### =€ Transiciones
- **Usar variables predefinidas:**
  ```css
  --transition-fast: all 0.2s ease;
  --transition-normal: all 0.3s ease;
  --transition-slow: all 0.5s ease;
  ```

### =ñ Responsive
- **Base rem:** Todo escalable desde `html { font-size: 16px }`
- **Mobile-first:** Diseñar primero para móviles
- **Breakpoints:** Usar variables de breakpoints cuando se definan

---

## = CHECKLIST DE IMPLEMENTACIÓN

Antes de hacer commit, verificar:

###  CSS
- [ ] ¿Usé solo variables CSS para colores?
- [ ] ¿Todo está en rem en lugar de px?
- [ ] ¿Usé variables de espaciado predefinidas?
- [ ] ¿Las transiciones usan las variables estándar?

###  Componentes
- [ ] ¿El componente sigue la estructura de carpetas?
- [ ] ¿Los archivos CSS/JS están en la carpeta del componente?
- [ ] ¿La nomenclatura es consistente?

###  Escalabilidad
- [ ] ¿El código funcionará en dark/light mode?
- [ ] ¿Es responsive desde mobile hasta desktop?
- [ ] ¿Otras personas pueden entender las variables usadas?

---

## =¨ CASOS ESPECIALES

### Cuando crear nuevas variables:
1. **Color:** Si necesitas un color que no existe en la paleta
2. **Tamaño:** Si necesitas un tamaño específico que se reutilizará
3. **Espaciado:** Si ninguna variable de espaciado actual funciona

### Proceso para nuevas variables:
1. Verificar que no existe una similar
2. Añadir a `base.css` con nombre semántico
3. Documentar en comentarios su propósito
4. Usar inmediatamente en lugar del valor hardcoded

---

## =Ú RECURSOS RÁPIDOS

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

### Archivos clave:
- `assets/css/core/base.css` - Variables globales
- `assets/css/core/sidebar/menu-style.css` - Estilos del menú
- `CLAUDE.md` - Documentación del framework

---

## = MANTENIMIENTO DEL CONTRATO

- **Actualizar** cuando se añadan nuevas variables o reglas
- **Revisar** mensualmente para nuevas necesidades
- **Comunicar** cambios a todo el equipo
- **Versionar** cambios importantes

---

> **=¡ Recuerda:** Este contrato existe para hacer el desarrollo MÁS RÁPIDO y CONSISTENTE, no más lento. Siguiendo estas reglas, el código será más fácil de mantener y escalar.

**Última actualización:** 28 de Agosto 2025