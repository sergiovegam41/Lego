# Tablas

## TableComponent

```php
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;

$columns = new ColumnCollection(
    new ColumnDto(
        field: "id",
        headerName: "ID",
        width: DimensionValue::px(80),
        sortable: true,
        filter: true,
        filterType: "number"
    ),
    new ColumnDto(
        field: "name",
        headerName: "Nombre",
        width: DimensionValue::px(200),
        sortable: true,
        filter: true,
        filterType: "text"
    )
);

$table = new TableComponent(
    id: "productos-table",
    model: Producto::class,  // Server-side automático
    columns: $columns,
    height: "600px",
    pagination: true,
    rowSelection: "multiple"
);

echo $table->render();
```

## ColumnDto

| Propiedad | Tipo | Descripción |
|-----------|------|-------------|
| `field` | `string` | Nombre del campo |
| `headerName` | `string` | Texto del header |
| `width` | `DimensionValue` | Ancho de columna |
| `sortable` | `bool` | Si es ordenable |
| `filter` | `bool` | Si es filtrable |
| `filterType` | `string` | `text`, `number`, `date` |
| `valueFormatter` | `string` | Función JS para formatear |
| `cellRenderer` | `string` | Función JS para renderizar |

## RowActions

```php
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;
use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;

$actions = new RowActionsCollection(
    new RowActionDto(
        id: "edit",
        label: "Editar",
        icon: "create-outline",
        callback: "handleEdit",  // Función global JS
        variant: "primary"
    ),
    new RowActionDto(
        id: "delete",
        label: "Eliminar",
        icon: "trash-outline",
        callback: "handleDelete",
        variant: "danger"
    )
);

$table = new TableComponent(
    // ...
    rowActions: $actions
);
```

## Callbacks en JS

```javascript
// Debe ser función global
window.handleEdit = function(rowData, tableId) {
    console.log('Editar:', rowData.id);
    openEditModule(rowData.id);
};

window.handleDelete = async function(rowData, tableId) {
    const confirmed = await ConfirmationService.confirm({
        title: '¿Eliminar?',
        type: 'danger'
    });
    if (confirmed) {
        await deleteRecord(rowData.id);
    }
};
```

## Server-Side Pagination

Automática cuando pasas `model`:

```php
$table = new TableComponent(
    model: Producto::class,  // Activa server-side
    pagination: true
);
```

Peticiones:
```
GET /api/get/productos?page=1&limit=20&sort=name&order=asc&filter[name]=test
```

## Persistencia de Filtros

```javascript
// Guardar filtros
window.addEventListener('lego:table:filterChanged', (e) => {
    window.legoWindowManager.setParam('columnFilters', e.detail.filterModel);
});

// Restaurar filtros
window.addEventListener('lego:table:ready', (e) => {
    const filters = window.legoWindowManager.getParam('columnFilters');
    if (filters) {
        e.detail.api.setFilterModel(filters);
    }
});
```

## Eventos

```javascript
// Tabla lista
window.addEventListener('lego:table:ready', (e) => {
    const { tableId, api } = e.detail;
});

// Filtros cambiados
window.addEventListener('lego:table:filterChanged', (e) => {
    const { tableId, filterModel } = e.detail;
});
```

## Acceso a la API de AG Grid

```javascript
// Global
const api = window.LEGO_TABLES['productos-table']?.api;

// Refrescar datos
api.refreshInfiniteCache();

// Aplicar filtro
api.setFilterModel({ name: { filter: 'test', type: 'contains' } });
```

