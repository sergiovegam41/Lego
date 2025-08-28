# =� Contrato de Implementaci�n - LegoPHP Framework

> **� OBLIGATORIO:** Revisar este documento antes de implementar cualquier funcionalidad

## <� Prop�sito
Este contrato define las reglas y est�ndares que deben seguirse en todo desarrollo del framework LegoPHP para garantizar escalabilidad, mantenibilidad y consistencia.

---

## <� REGLAS DE CSS

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

### =� TAMA�OS - OBLIGATORIO  
- **NUNCA usar pixeles** (`px`) para tama�os
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
  --menu-item-spacing: 0.375rem; /* Para elementos de men� */
  ```

---

## <� REGLAS DE COMPONENTES

### =� Estructura de Archivos
- **Cada componente debe tener:** `ComponentName.php`, `component.css`, `component.js`
- **Ubicaci�n:** `Views/[Feature]/Components/[ComponentName]/`
- **Nomenclatura:** PascalCase para componentes

### =' Variables CSS
- **Nuevas variables:** A�adir a `assets/css/core/base.css` en la secci�n correspondiente
- **Sem�nticas:** Usar nombres descriptivos (`--bg-surface` no `--gray-light`)
- **Escalabilidad:** Agrupar por categor�as (colores, tama�os, espaciados)

---

## � REGLAS DE RENDIMIENTO

### =� Transiciones
- **Usar variables predefinidas:**
  ```css
  --transition-fast: all 0.2s ease;
  --transition-normal: all 0.3s ease;
  --transition-slow: all 0.5s ease;
  ```

### =� Responsive
- **Base rem:** Todo escalable desde `html { font-size: 16px }`
- **Mobile-first:** Dise�ar primero para m�viles
- **Breakpoints:** Usar variables de breakpoints cuando se definan

---

## = CHECKLIST DE IMPLEMENTACI�N

Antes de hacer commit, verificar:

###  CSS
- [ ] �Us� solo variables CSS para colores?
- [ ] �Todo est� en rem en lugar de px?
- [ ] �Us� variables de espaciado predefinidas?
- [ ] �Las transiciones usan las variables est�ndar?

###  Componentes
- [ ] �El componente sigue la estructura de carpetas?
- [ ] �Los archivos CSS/JS est�n en la carpeta del componente?
- [ ] �La nomenclatura es consistente?

###  Escalabilidad
- [ ] �El c�digo funcionar� en dark/light mode?
- [ ] �Es responsive desde mobile hasta desktop?
- [ ] �Otras personas pueden entender las variables usadas?

---

## =� CASOS ESPECIALES

### Cuando crear nuevas variables:
1. **Color:** Si necesitas un color que no existe en la paleta
2. **Tama�o:** Si necesitas un tama�o espec�fico que se reutilizar�
3. **Espaciado:** Si ninguna variable de espaciado actual funciona

### Proceso para nuevas variables:
1. Verificar que no existe una similar
2. A�adir a `base.css` con nombre sem�ntico
3. Documentar en comentarios su prop�sito
4. Usar inmediatamente en lugar del valor hardcoded

---

## =� RECURSOS R�PIDOS

### Variables m�s usadas:
```css
/* Colores */
--text-primary, --text-secondary
--bg-body, --bg-sidebar, --bg-surface
--accent-primary, --accent-hover

/* Tama�os */
--sidebar-width, --menu-item-height
--font-size-base, --font-size-lg

/* Espacios */  
--space-sm, --space-md
--menu-item-spacing
```

### Archivos clave:
- `assets/css/core/base.css` - Variables globales
- `assets/css/core/sidebar/menu-style.css` - Estilos del men�
- `CLAUDE.md` - Documentaci�n del framework

---

## = MANTENIMIENTO DEL CONTRATO

- **Actualizar** cuando se a�adan nuevas variables o reglas
- **Revisar** mensualmente para nuevas necesidades
- **Comunicar** cambios a todo el equipo
- **Versionar** cambios importantes

---

> **=� Recuerda:** Este contrato existe para hacer el desarrollo M�S R�PIDO y CONSISTENTE, no m�s lento. Siguiendo estas reglas, el c�digo ser� m�s f�cil de mantener y escalar.

**�ltima actualizaci�n:** 28 de Agosto 2025