# Reporte de Variables CSS No Utilizadas

**Fecha**: 2025-11-02
**Análisis**: Automático via grep
**Total Variables**: 323
**Variables Usadas**: 169 (52%)
**Variables NO Usadas**: 154 (48%)

---

## Resumen Ejecutivo

Se encontraron **154 variables CSS** que no tienen ninguna referencia `var(--nombre)` en el código. Estas variables están definidas pero nunca se utilizan.

### Categorías de Variables No Usadas:

1. **AG-Grid** (~20 variables) - Sistema de tablas no utilizado
2. **Badges** (~6 variables) - Componentes de insignias
3. **Botones** (~12 variables) - Estilos de botones alternativos
4. **Inputs** (~15 variables) - Estilos de formularios alternativos
5. **Colores** (~50 variables) - Paleta extendida no usada
6. **Espaciado** (~10 variables) - Espacios no utilizados
7. **Sombras** (~8 variables) - Efectos no utilizados
8. **Z-index** (~6 variables) - Capas no utilizadas
9. **Otros** (~27 variables) - Misceláneos

---

## Variables No Usadas (Listado Completo)

### AG-Grid (Tablas)
```css
--ag-background-color
--ag-border-color
--ag-foreground-color
--ag-header-background-color
--ag-header-foreground-color
--ag-input-border-color
--ag-input-focus-border-color
--ag-odd-row-background-color
--ag-row-hover-color
--ag-secondary-foreground-color
--ag-selected-row-background-color
--ag-font-family
--ag-font-size
```

### Badges (Insignias)
```css
--badge-info-bg
--badge-info-border
--badge-info-text
--badge-warning-bg
--badge-warning-border
--badge-warning-text
```

### Botones
```css
--button-bg-danger
--button-bg-danger-hover
--button-bg-ghost
--button-bg-ghost-hover
--button-bg-primary
--button-bg-primary-hover
--button-bg-secondary
--button-bg-secondary-hover
--button-padding-lg
--button-padding-md
--button-padding-sm
--button-radius
--button-text-danger
--button-text-ghost
--button-text-primary
--button-text-secondary
```

### Inputs / Formularios
```css
--input-bg
--input-bg-disabled
--input-bg-focus
--input-bg-hover
--input-border
--input-border-error
--input-border-focus
--input-border-hover
--input-padding
--input-radius
--input-text
--input-text-disabled
--input-text-placeholder
```

### Colores
```css
--color-focus
--color-focus-30
--color-focus-60
--color-focus-hover
--color-gray-500
--color-gray-800
--color-gray-active
--color-gray-hover
--color-green-500
--color-red-500
--color-red-700
--color-neutral-50
--color-neutral-100
--color-neutral-200
--color-neutral-300
--color-neutral-400
--color-neutral-500
--color-neutral-600
--color-neutral-700
--color-neutral-800
--color-neutral-900
--blue-50
--blue-200
--blue-300
--blue-400
--blue-700
--blue-800
--green-50
--green-200
--green-500
--green-800
--orange-50
--orange-100
--orange-200
--orange-400
--orange-600
--orange-700
--orange-800
--red-50
--red-500
--red-800
--yellow-50
--yellow-500
```

### Dropdown / Menús
```css
--dropdown-bg
--dropdown-border
--dropdown-item-active
--dropdown-item-hover
--dropdown-shadow
```

### Modal / Diálogos
```css
--modal-backdrop
--modal-bg
--modal-border
--modal-shadow
```

### Sidebar
```css
--sidebar-bg
--sidebar-border
--sidebar-item-active
--sidebar-item-hover
```

### Tabla
```css
--table-header-bg
--table-row-hover
--table-row-selected
```

### Espaciado
```css
--space-0
--space-3xl
--space-4xl
```

### Border Radius
```css
--radius-2xl
--radius-3xl
--radius-button
--radius-input
--radius-none
```

### Sombras
```css
--shadow-xs
--shadow-xl
--shadow-hover
--shadow-focus
```

### Z-Index (Capas)
```css
--z-dropdown
--z-fixed
--z-modal
--z-modal-backdrop
--z-popover
--z-sticky
--z-tooltip
```

### Tipografía
```css
--font-family-base
--font-weight-normal
--font-weight-medium
--font-weight-bold
--font-weight-light
--line-height-normal
--line-height-tight
```

### Bordes
```css
--border-success
--border-width-2
--border-width-4
```

### Estados
```css
--state-disabled
--state-focus
```

### Otros
```css
--accent-primary-light
--bg-toggle
--card-bg-hover
--icon-size-sm
--search-box-collapsed-size
--text-inverse
--text-on-primary
--toggle-bg
```

---

## Recomendaciones

### Acción Inmediata (Bajo Riesgo)
Eliminar variables de bibliotecas no utilizadas:
- **AG-Grid** completo (no se usa AG-Grid en el proyecto)
- Variables duplicadas (algunas aparecen 2 veces)

### Acción con Precaución (Medio Riesgo)
Variables que podrían ser usadas dinámicamente o en temas futuros:
- Colores extendidos (blue-*, green-*, orange-*, red-*)
- Variables de componentes (badges, modal, dropdown)

### Mantener (Posible Uso Futuro)
Variables del sistema de diseño que podrían ser útiles:
- Z-index organizados
- Espaciado consistente
- Tipografía estandarizada

---

## Próximos Pasos

1. **Revisar manualmente** este reporte
2. **Verificar** si alguna variable se usa dinámicamente (JavaScript)
3. **Eliminar por categorías**:
   - Primero: AG-Grid (definitivamente no usado)
   - Segundo: Duplicados
   - Tercero: Variables obsoletas confirmadas
4. **Probar visualmente** después de cada eliminación
5. **Hacer commit** entre eliminaciones para poder revertir

---

## Comando para Eliminar Variables

Para eliminar una variable específica de todos los archivos CSS:

```bash
# Ejemplo: eliminar --ag-background-color
find /ruta/proyecto -name "*.css" -exec sed -i '' '/--ag-background-color/d' {} \;
```

**⚠️ IMPORTANTE**: Siempre hacer backup antes de ejecutar eliminaciones masivas.

---

## Archivos CSS a Revisar

Archivos que contienen variables no usadas:
- `assets/css/core/base.css`
- `assets/css/core/theme-variables.css`
- Archivos CSS de componentes individuales

Total: 35 archivos CSS en el proyecto
