# WindowManager API

API global disponible en `window.legoWindowManager`.

## Navegación de Módulos

```javascript
// Cerrar módulo actual y volver al origen
legoWindowManager.closeCurrentWindow();

// Cerrar con refresh del módulo destino
legoWindowManager.closeCurrentWindow({ refresh: true });

// Cerrar y forzar destino específico
legoWindowManager.closeCurrentWindow({ returnTo: 'module-id' });

// Abrir módulo con menú dinámico
legoWindowManager.openModuleWithMenu({
    moduleId: 'example-crud-edit',
    parentMenuId: 'example-crud',
    label: 'Editar',
    url: '/component/example-crud/edit?id=123',
    icon: 'create-outline',
    sourceModuleId: 'example-crud-list'  // Opcional
});

// Recargar módulo activo (preserva params)
legoWindowManager.reloadActive();
```

## Parámetros Persistentes

Persisten durante reloads, se eliminan al cerrar el módulo.

```javascript
// Módulo activo (default)
legoWindowManager.setParam('key', value);
legoWindowManager.getParam('key', defaultValue);
legoWindowManager.getParams();
legoWindowManager.removeParam('key');
legoWindowManager.clearParams();

// Módulo específico (último parámetro opcional)
legoWindowManager.setParam('key', value, 'module-id');
legoWindowManager.getParam('key', defaultValue, 'module-id');
legoWindowManager.getParams('module-id');
legoWindowManager.removeParam('key', 'module-id');
legoWindowManager.clearParams('module-id');
```

## Flujo de Retorno Automático

Al abrir un módulo, se guarda `sourceModuleId` automáticamente.

```
Ver → Editar → Guardar → Cierra → Vuelve a Ver ✓
```

Prioridad al cerrar:
1. `returnTo` explícito
2. `sourceModuleId` guardado
3. Primer módulo disponible

## Ejemplo CRUD Típico

```javascript
// Abrir edición desde tabla
function openEdit(id) {
    legoWindowManager.openModuleWithMenu({
        moduleId: 'products-edit',
        parentMenuId: 'products',
        label: 'Editar',
        url: `/component/products/edit?id=${id}`
    });
}

// Al guardar en el formulario de edición
async function save() {
    await api.update(data);
    legoWindowManager.closeCurrentWindow({ refresh: true });
    // Vuelve automáticamente a la tabla y la refresca
}
```

