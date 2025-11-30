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
    parentMenuId: 'productos',
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

