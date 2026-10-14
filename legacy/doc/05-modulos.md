# Módulos (Windows)

## Conceptos

- **Módulo**: Instancia de un screen en memoria
- **ModuleStore**: Almacena estado de módulos abiertos
- **WindowManager**: API para manipular módulos

## ModuleStore

```javascript
// Estado interno
{
    modules: {
        'productos-list': {
            component: 'ProductosListComponent',
            isActive: true,
            params: { columnFilters: {...} },
            sourceModuleId: null
        },
        'productos-edit': {
            component: 'ProductosEditComponent',
            isActive: false,
            params: {},
            sourceModuleId: 'productos-list'
        }
    },
    activeModule: 'productos-list'
}
```

## WindowManager API

```javascript
// Abrir módulo
window.legoWindowManager.openModule('productos-list', '/component/productos');

// Abrir con item de menú dinámico
window.legoWindowManager.openModuleWithMenu({
    moduleId: 'productos-edit',
    // parentMenuId se obtiene automáticamente desde la BD (no se debe hardcodear)
    label: 'Editar',
    url: '/component/productos/edit?id=5',
    icon: 'create-outline'
});

// Cerrar módulo
window.legoWindowManager.closeModule('productos-edit');

// Cerrar módulo actual
window.legoWindowManager.closeCurrentWindow();
window.legoWindowManager.closeCurrentWindow({ refresh: true }); // Refresca el padre

// Recargar módulo activo
window.legoWindowManager.reloadActive();
```

## Parámetros Persistentes

Datos que sobreviven recargas pero se limpian al cerrar:

```javascript
// Guardar
window.legoWindowManager.setParam('columnFilters', { name: 'test' });

// Obtener
const filters = window.legoWindowManager.getParam('columnFilters');

// Todos los params
const params = window.legoWindowManager.getParams();

// Eliminar uno
window.legoWindowManager.removeParam('columnFilters');

// Limpiar todos
window.legoWindowManager.clearParams();
```

## sourceModuleId

Rastreo de navegación padre-hijo:

```javascript
// Abrir desde productos-list
openModuleWithMenu({ moduleId: 'productos-edit', ... });
// productos-edit.sourceModuleId = 'productos-list'

// Al cerrar productos-edit
closeCurrentWindow();
// Vuelve a productos-list automáticamente
```

## Eventos

```javascript
// Módulo activado
window.addEventListener('lego:module:activated', (e) => {
    console.log('Módulo activo:', e.detail.moduleId);
});

// Módulo cerrado
window.addEventListener('lego:module:closed', (e) => {
    console.log('Módulo cerrado:', e.detail.moduleId);
});

// Módulo recargado
window.addEventListener('lego:module:reloaded', (e) => {
    console.log('Módulo recargado:', e.detail.moduleId);
});
```

## Contenedores DOM

```html
<!-- Cada módulo tiene un contenedor -->
<div id="module-productos-list" class="module-container active">
    <!-- Contenido del componente -->
</div>
<div id="module-productos-edit" class="module-container">
    <!-- Contenido del componente -->
</div>
```

## ⚠️ Mejores Prácticas: Evitar Conflictos de Nombres

### Problema: Funciones Globales Compartidas

Cuando múltiples módulos definen funciones con el mismo nombre en `window`, se sobrescriben entre sí. Esto causa que al hacer clic en "Editar" en un módulo, se ejecute la función de otro módulo.

**❌ INCORRECTO:**
```javascript
// En roles-config.js
window.openEditModule = function(id) { ... };

// En auth-groups-config.js
window.openEditModule = function(id) { ... }; // ❌ Sobrescribe la anterior

// En users-config.js
window.openEditModule = function(id) { ... }; // ❌ Sobrescribe ambas anteriores
```

**✅ CORRECTO:**
```javascript
// En roles-config.js
window.openEditRoleModule = function(id) { ... };

// En auth-groups-config.js
window.openEditAuthGroupModule = function(id) { ... };

// En users-config.js
window.openEditUserModule = function(id) { ... };
```

### Patrón Recomendado

**1. Nombres Únicos por Módulo:**
- Usa el nombre del módulo en la función: `openEdit{ModuleName}Module`
- Ejemplos: `openEditRoleModule`, `openEditAuthGroupModule`, `openEditUserModule`

**2. Callbacks de TableComponent:**
- Los callbacks ya deben tener nombres únicos: `handleEditRole`, `handleEditAuthGroup`
- Dentro del callback, llama a la función única del módulo

**3. Ejemplo Completo:**
```javascript
// roles-config.js
const SCREEN_CONFIG = {
    screenId: 'roles-config-list',
    menuGroupId: 'roles-config',
    // ...
};

// Callback único para TableComponent
window.handleEditRole = function(rowData, tableId) {
    openEditRoleModule(rowData.id); // ✅ Nombre único
};

// Función de edición con nombre único
window.openEditRoleModule = function(id) {
    window.legoWindowManager.openModuleWithMenu({
        moduleId: SCREEN_CONFIG.children.edit,
        parentMenuId: SCREEN_CONFIG.menuGroupId,
        label: 'Editar Rol',
        url: childUrl('edit', { id: id }),
        icon: 'create-outline'
    });
};
```

**4. Verificación:**
- Busca en tu código: `grep -r "window\.openEditModule" components/`
- Si encuentras múltiples definiciones, renómbralas a nombres únicos

### Por Qué Ocurre

Cuando se carga un módulo dinámico, su JavaScript se ejecuta en el scope global (`window`). Si dos módulos definen `window.openEditModule`, el último módulo cargado sobrescribe la función del primero, causando que los clicks en "Editar" ejecuten la función incorrecta.

