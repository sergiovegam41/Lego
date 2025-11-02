# CHECKLIST DE LIMPIEZA CSS/SCSS - PROYECTO LEGO

**Fecha:** 2 de Noviembre 2024
**Prioridad:** ALTA
**Impacto:** Mantenibilidad, Performance, Code Quality

---

## RESUMEN DEL PROBLEMA

- **297 variables CSS** definidas, pero **135 (45%)** nunca se usan
- **37 archivos CSS**, pero **33 (89%)** son huérfanos sin referencia
- Sistema de theming conflictivo entre `theme-variables.css` y `base.css`
- Error crítico: Valor hexadecimal inválido en `--color-gray-800`
- Estimado: **~54 KB de código CSS muerto** en el proyecto

---

## TAREAS URGENTES (Esta semana)

### 1. Corregir error hexadecimal
- [ ] Abrir archivo: `/assets/css/core/base.css`
- [ ] Ir a línea 151
- [ ] Cambiar: `--color-gray-800: #120120120;`
- [ ] Por: `--color-gray-800: #121212;`
- [ ] Verificar que el navegador interpreta correctamente el valor

**Archivo afectado:** 
```
/Users/serioluisvegamartinez/Documents/GitHub/Lego/assets/css/core/base.css
```

---

### 2. Consolidar archivos de tema (DECISIÓN CRÍTICA)

**Opción A: Mantener theme-variables.css (RECOMENDADO)**
- [ ] Verificar que `theme-variables.css` cubre todas las variables necesarias
- [ ] Eliminar todas las redefiniciones de `base.css`
- [ ] Mover estilos generales de `base.css` a `theme-variables.css`
- [ ] Eliminar `base.css` completamente
- [ ] Garantizar que dark/light mode funciona

**Opción B: Mantener base.css**
- [ ] Eliminar `theme-variables.css`
- [ ] Renombrar variables a patrón semántico
- [ ] Implementar cambio de tema sin conflictos

**Decisión a tomar:** _________________ (Opción A o B)

---

## TAREAS IMPORTANTES (Este sprint)

### 3. Auditar 135 variables CSS sin usar

Agrupar por categoría y decidir qué eliminar:

#### Variables de Componentes (nunca usadas):
- [ ] Button (16 variables): `--button-bg-primary`, `--button-padding-*`, etc.
- [ ] Input (13 variables): `--input-bg`, `--input-border`, etc.
- [ ] Modal (4 variables): `--modal-bg`, `--modal-border`, etc.
- [ ] Dropdown (5 variables): `--dropdown-bg`, `--dropdown-shadow`, etc.
- [ ] Table (3 variables): `--table-header-bg`, `--table-row-hover`, etc.
- [ ] Badge (6 variables): `--badge-*-bg`, `--badge-*-text`, etc.

**Acción:** Eliminar todas estas (nunca se usan)

#### Paleta de colores base:
- [ ] Blues: `--blue-50`, `--blue-200`, `--blue-300`, etc. (6 variables)
- [ ] Greens: `--green-50`, `--green-200`, `--green-500`, etc. (4 variables)
- [ ] Reds: `--red-50`, `--red-500`, `--red-800` (3 variables)
- [ ] Oranges: `--orange-50` a `--orange-800` (7 variables)
- [ ] Grays: `--color-gray-500`, `--color-gray-800`, etc. (3 variables)

**Acción:** Evaluar si se usarán en el futuro, si no: eliminar

#### Sistema de Z-index:
- [ ] Completo sin usar: `--z-dropdown`, `--z-sticky`, `--z-fixed`, `--z-modal-backdrop`, `--z-modal`, `--z-popover`, `--z-tooltip`

**Acción:** Eliminar si no hay plan de uso a corto plazo

#### Otros:
- [ ] `--font-family-base` (usa directamente 'Poppins')
- [ ] `--font-weight-*` (todos los weights)
- [ ] `--line-height-tight`, `--line-height-normal`
- [ ] `--state-disabled`, `--state-focus`
- [ ] `--border-width-2`, `--border-width-4`
- [ ] `--space-0`, `--space-3xl`, `--space-4xl`

**Acción:** Revisar uso real en componentes

---

### 4. Auditar 33 archivos CSS huérfanos

Revisar cada archivo y determinar su estado:

```bash
# Para cada archivo, hacer:
1. Abrir archivo
2. Revisar contenido
3. Buscar referencias en componentes JS
4. Decidir: MANTENER o ELIMINAR
5. Marcar en checklist
```

#### Core Assets (3):
- [ ] `assets/css/core/alert-service.css` - ¿En uso? Buscar referencias de AlertService
- [ ] `assets/css/core/windows-manager.css` - ¿En uso? Buscar WindowsManager
- [ ] `assets/css/core/sidebar/menu-style.css` - ¿En uso? Buscar referencias del menú

#### Componentes Core (7):
- [ ] `components/Core/Home/home.css` - Revisar home.js
- [ ] `components/Core/Login/login.css` - Revisar login.js
- [ ] `components/Core/Automation/automation.css` - Revisar automation.js
- [ ] `components/Core/Home/Components/HeaderComponent/header-component.css` - Revisar header-component.js
- [ ] `components/Core/Home/Components/MainComponent/main-component.css` - Revisar main-component.js
- [ ] `components/Core/Home/Components/MenuComponent/menu-component.css` - Revisar menu-component.js
- [ ] `components/Core/Home/Components/MenuComponent/features/MenuItemComponent/menu-item-component.css` - Revisar menu-item-component.js

#### Componentes Shared (15):
- [ ] Forms: button.css, input-text.css, select.css, textarea.css, radio.css, checkbox.css, form.css, form-row.css, filepondcomponent.css
- [ ] Essentials: div.css, row.css, column.css, grid.css, table.css, image-gallery.css
- [ ] Navigation: breadcrumb.css
- [ ] Buttons: icon-button.css

#### Componentes App (6):
- [ ] `components/App/FormsShowcase/forms-showcase.css` - Revisar forms-showcase.js
- [ ] `components/App/ProductsCrudV3/products-crud-v3.css` - Revisar products-crud-v3.js
- [ ] `components/App/ProductsCrudV3/childs/ProductCreate/product-form.css` - Revisar product-create.js
- [ ] `components/App/ProductsCrudV3/childs/ProductEdit/product-form.css` - Revisar product-edit.js
- [ ] `components/App/ProductsTableDemo/products-table-demo.css` - Revisar products-table-demo.js
- [ ] `components/App/TableShowcase/table-showcase.css` - Revisar table-showcase.js

---

## TAREAS IMPLEMENTACIÓN (Próximos sprints)

### 5. Implementar carga de CSS en componentes

Opción A: Inline styles en JS
```javascript
// En cada componente JS
const styles = `
  .lego-button { ... }
`;
const styleTag = document.createElement('style');
styleTag.textContent = styles;
document.head.appendChild(styleTag);
```

Opción B: Importar CSS desde JS (si uses bundler)
```javascript
import './button.css';
```

Opción C: Shadow DOM con CSS scoped
```javascript
const shadow = this.attachShadow({ mode: 'open' });
shadow.innerHTML = `<style>...</style><div class="..."></div>`;
```

**Decidir estrategia:** _________________

- [ ] Implementar en primer componente como POC
- [ ] Documentar patrón
- [ ] Migrar resto de componentes

---

### 6. Crear documentación de variables CSS

Crear archivo: `docs/CSS_VARIABLES_GUIDE.md`

Incluir:
- [ ] Lista de variables actualmente usadas
- [ ] Propósito de cada variable
- [ ] Cuándo usar cada una
- [ ] Ejemplos de uso
- [ ] Variables deprecadas
- [ ] Cómo agregar nuevas variables

---

### 7. Automatizar validación en CI/CD

Crear script: `scripts/validate-css.js`

Debe verificar:
- [ ] No hay variables CSS sin usar
- [ ] No hay archivos CSS sin referencia
- [ ] No hay valores hexadecimales inválidos
- [ ] No hay conflictos de valores entre archivos

Agregar a `.github/workflows/` o tu sistema de CI

---

## TAREAS REFACTOR (Mediano-Largo plazo)

### 8. Migrar a SCSS o Tailwind CSS

- [ ] Evaluar SCSS (variables, mixins, nesting)
- [ ] Evaluar Tailwind CSS (utility-first)
- [ ] Evaluar CSS-in-JS (styled-components, emotion)
- [ ] Decidir estrategia
- [ ] Plan de migración gradual

---

### 9. Modernizar estructura de componentes

- [ ] Implementar Web Components estándar
- [ ] Shadow DOM para encapsulación
- [ ] Scoped styles por componente
- [ ] Eliminar conflictos CSS globales

---

## MÉTRICAS DE ÉXITO

Después de completar todas las tareas:

| Métrica | Antes | Objetivo | Después |
|---------|-------|----------|---------|
| Variables CSS | 297 | <150 | _____ |
| Variables sin usar | 135 (45%) | 0 | _____ |
| Archivos CSS huérfanos | 33 (89%) | 0-5 | _____ |
| Tamaño CSS muerto | ~54 KB | 0 KB | _____ |
| Sistema de theming | Conflictivo | Funcional | _____ |
| Errores CSS | 1 crítico | 0 | _____ |

---

## NOTAS FINALES

1. **Código legado:** Es probable que muchos archivos sean código obsoleto. Preguntar al equipo antes de eliminar.

2. **Componentes dinámicos:** Algunos componentes podrían cargar CSS dinámicamente. Verificar con grep adicional.

3. **Variables futuras:** Algunas variables se crearon "por si acaso". Discutir si eliminar o documentar para uso futuro.

4. **Performance:** Eliminar 54 KB de CSS podría mejorar performance de carga.

5. **Mantenibilidad:** El sistema de theming debe funcionar para poder cambiar dark/light mode sin problemas.

---

## CONTACTOS

- **Responsable del análisis:** [Tu nombre]
- **Propietario de CSS:** [Nombre del dev CSS]
- **Líder técnico:** [Nombre del TL]

---

**Status:** ⬜ No iniciado | 🟡 En progreso | ✅ Completado

Última actualización: 2 de Noviembre 2024

