# LEGO Framework: Modular Blocks Guide

## Filosofía

El framework LEGO proporciona **bloques reutilizables y agnósticos** que se pueden combinar de manera versátil para construir cualquier CRUD, formulario, tabla o aplicación sin code duplication ni templates rígidos.

En lugar de:
```
"Template rigido que hace CRUDS genéricos y te da 3 opciones - solo me limitaría"
```

Proporcionamos:
```
"Elementos compatibles que puedes juntar de manera versátil, ágil y sin sorpresas"
```

---

## Bloques Disponibles

### 1. **ApiClient** - Comunicación HTTP agnóstica
**Archivo**: `/assets/js/core/services/ApiClient.js`

Abstracts HTTP communication for ANY REST endpoint.

```javascript
// Crear cliente para cualquier API
const api = new ApiClient('/api/products');
const clientsApi = new ApiClient('/api/clients');
const invoicesApi = new ApiClient('/api/invoices');

// Métodos estándar
const products = await api.list();
const product = await api.get(1);
const newProduct = await api.create({ name: 'Laptop', price: 999 });
await api.update({ id: 1, price: 899 });
await api.delete(1);

// Llamada genérica
const result = await api.call('POST', '/custom-endpoint', { data: 'value' });
```

**Ventajas**:
- No hardcodea rutas API específicas
- Funciona con cualquier endpoint REST
- Manejo automático de errores y timeouts
- Soporta cualquier método HTTP

---

### 2. **StateManager** - Gestión de estado con eventos
**Archivo**: `/assets/js/core/services/StateManager.js`

Centralized state management with pub/sub pattern.

```javascript
const state = new StateManager();

// Guardar estado
state.setState('products', [{ id: 1, name: 'Laptop' }]);
state.setState('currentUser', { id: 10, role: 'admin' });

// Leer estado
const products = state.getState('products');

// Escuchar cambios de estado
state.on('products:changed', (newProducts) => {
    console.log('Productos actualizados:', newProducts);
    updateUI(newProducts);
});

// Emitir eventos personalizados
state.emit('products:loaded', { count: 100 });

// Suscribirse una sola vez
state.once('product:created', (newProduct) => {
    showNotification(`Producto ${newProduct.name} creado`);
});
```

**Ventajas**:
- Comunica componentes sin acoplamiento
- Patrón pub/sub elegante
- Facilita debugging con eventos

---

### 3. **ValidationEngine** - Validación agnóstica de datos
**Archivo**: `/assets/js/core/services/ValidationEngine.js`

Schema-based validation without hardcoding rules.

```javascript
// Definir esquema de validación una sola vez
const validator = new ValidationEngine({
    name: { required: true, minLength: 3 },
    email: { required: true, patternName: 'email' },
    price: { type: 'number', min: 0, max: 99999 },
    stock: { type: 'number', min: 0 },
    description: { minLength: 10, maxLength: 500 },
    website: { patternName: 'url' }
});

// Validar datos (reutilizable en create/update/formularios)
const errors = validator.validate(formData);

if (validator.hasErrors(errors)) {
    // Mostrar errores a usuario
    Object.entries(errors).forEach(([field, messages]) => {
        console.error(`${field}: ${messages[0]}`);
    });
} else {
    // Datos válidos, proceder
    await api.create(formData);
}

// Patrones predefinidos
// - email: validar correo electrónico
// - phone: validar teléfono
// - url: validar URL
// - sku: validar código SKU
// - uuid: validar UUID
// - ipv4: validar dirección IP

// Validadores personalizados
validator.addCustom('price', (value) => {
    if (value > 10000) {
        return ['El precio máximo es $10,000'];
    }
    return [];
});
```

**Ventajas**:
- Define reglas una sola vez
- Reutilizable en create, update, formularios
- Patrones predefinidos para casos comunes
- Validadores personalizados

---

### 4. **TableManager** - Abstracción de AG Grid
**Archivo**: `/assets/js/core/services/TableManager.js`

Wraps AG Grid complexity for simple table management.

```javascript
// Crear gerenciador de tabla
const tableManager = new TableManager('products-crud-table');

// Esperar a que la tabla esté lista
tableManager.onReady(() => {
    console.log('Tabla lista');
    loadData();
});

// Actualizar datos
tableManager.setData([
    { id: 1, name: 'Laptop', price: 999 },
    { id: 2, name: 'Mouse', price: 29 }
]);

// Actualizar contador de registros
tableManager.updateRowCount();

// Obtener datos
const currentData = tableManager.getData();

// Obtener filas seleccionadas
const selected = tableManager.getSelectedRows();

// Actualizar columnas
tableManager.setColumnDefs([
    { field: 'id', headerName: 'ID' },
    { field: 'name', headerName: 'Nombre' }
]);

// Exportar
tableManager.exportToCSV('productos');

// Mostrar/ocultar loader
tableManager.setLoading(true);

// Eventos personalizados
tableManager.on('row:selected', (rowId) => {
    console.log('Fila seleccionada:', rowId);
});
tableManager.emit('row:action', { rowId: 1, action: 'edit' });

// Acceso directo a API AG Grid si necesitas algo avanzado
const gridApi = tableManager.getAPI();
gridApi.sizeColumnsToFit();
```

**Ventajas**:
- Abstrae complejidad de AG Grid
- API simple y consistente
- Sistema de eventos integrado
- Fallback a API directa si necesitas algo avanzado

---

### 5. **FormBuilder** - Constructor de formularios agnóstico
**Archivo**: `/assets/js/core/services/FormBuilder.js`

Manages form values, errors, and validation without HTML generation.

```javascript
// El HTML ya existe en el DOM (componentes LEGO)
const form = new FormBuilder({
    id: 'product-form',
    fields: {
        name: { type: 'text', required: true },
        price: { type: 'number', min: 0 },
        description: { type: 'textarea' }
    }
});

// Obtener valores del formulario
const data = form.getData();
// { name: 'Laptop', price: 999, description: '...' }

// Cargar datos (para edición)
form.setData({ name: 'Mouse', price: 29 });

// Limpiar formulario
form.clear();

// Validación (integra con ValidationEngine)
const validator = new ValidationEngine({ /* ... */ });
const errors = validator.validate(form.getData());
form.setErrors(errors);

// O validar campo por campo
form.setFieldError('email', 'El email no es válido');
form.clearFieldError('email');

// Obtener errores actuales
if (form.hasErrors()) {
    console.error('Errores:', form.getErrors());
}

// Desplazarse al primer error
form.focusFirstError();

// Marcar campo como válido/inválido (visual)
form.markFieldValid('email');
form.markFieldInvalid('phone');

// Deshabilitar formulario
form.setDisabled(true);

// Eventos
form.on('field:change', (fieldName) => {
    console.log('Campo cambió:', fieldName);
});
```

**Ventajas**:
- No genera HTML (compatible con componentes existentes)
- Gestiona valores de campos dinámicamente
- Sistema de errores integrado
- Marca visual de campos válidos/inválidos

---

## Ejemplo: Construir un CRUD Completo

### Escenario
Queremos crear un CRUD para "Clientes" reutilizando SOLO los bloques modulares, sin plantillas rígidas.

### Pasos

#### 1. Crear ClientsController (PHP backend)
```php
// /App/Controllers/Clients/ClientsController.php
namespace App\Controllers\Clients;

class ClientsController {
    public function list() {
        return response(['success' => true, 'data' => Client::all()]);
    }

    public function create($request) {
        $data = $request->json();
        $client = Client::create($data);
        return response(['success' => true, 'data' => $client]);
    }

    public function update($request) {
        $id = $request->input('id');
        $data = $request->input();
        Client::find($id)->update($data);
        return response(['success' => true]);
    }

    public function delete($request) {
        Client::find($request->input('id'))->delete();
        return response(['success' => true]);
    }
}
```

#### 2. Crear HTML con TableComponent y formulario

```php
// components/App/Clients/ClientsComponent.php
class ClientsComponent extends CoreComponent {
    protected function component(): string {
        // Usar TableComponent existente
        $table = (new TableComponent(
            id: 'clients-table',
            columns: new ColumnCollection(
                new ColumnDto(field: 'id', headerName: 'ID', width: 80),
                new ColumnDto(field: 'name', headerName: 'Nombre', width: 200),
                new ColumnDto(field: 'email', headerName: 'Email', width: 250),
                // ... más columnas
            ),
            rowData: [],
            pagination: true
        ))->render();

        return <<<HTML
        <div class="clients-container">
            <button onclick="createClient()" class="btn btn-primary">Nuevo Cliente</button>
            {$table}
        </div>
        HTML;
    }
}
```

#### 3. Escribir JavaScript usando bloques modulares

```javascript
// components/App/Clients/clients.js

// Crear bloques - AGNÓSTICO, sin hardcoding de entity
const api = new ApiClient('/api/clients');
const state = new StateManager();
const tableManager = new TableManager('clients-table');

const validator = new ValidationEngine({
    name: { required: true, minLength: 2 },
    email: { required: true, patternName: 'email' },
    phone: { required: true, patternName: 'phone' },
    company: { required: true }
});

// Cuando tabla esté lista
tableManager.onReady(async () => {
    configureColumns();
    await loadClients();
});

// Configurar columnas con acciones
function configureColumns() {
    const columns = [
        { field: 'id', headerName: 'ID', width: 80, pinned: 'left' },
        { field: 'name', headerName: 'Nombre', width: 200 },
        { field: 'email', headerName: 'Email', width: 250 },
        { field: 'phone', headerName: 'Teléfono', width: 150 },
        { field: 'company', headerName: 'Empresa', width: 200 },
        {
            field: 'actions',
            headerName: 'Acciones',
            width: 180,
            cellRenderer: params => `
                <button onclick="editClient(${params.data.id})" class="btn-edit">Editar</button>
                <button onclick="deleteClient(${params.data.id})" class="btn-delete">Eliminar</button>
            `
        }
    ];

    tableManager.setColumnDefs(columns);
}

// Cargar datos
async function loadClients() {
    tableManager.setLoading(true);
    try {
        const result = await api.list();
        if (result.success) {
            tableManager.setData(result.data);
            tableManager.updateRowCount();
            state.setState('clients', result.data);
            state.emit('clients:loaded', { count: result.data.length });
        }
    } finally {
        tableManager.setLoading(false);
    }
}

// Crear cliente
window.createClient = async function() {
    const result = await AlertService.componentModal(
        '/component/clients/client-form'
    );

    if (result.isConfirmed && result.value) {
        const errors = validator.validate(result.value);
        if (validator.hasErrors(errors)) {
            AlertService.error('Errores: ' + Object.values(errors).flat().join('\n'));
            return;
        }

        const response = await api.create(result.value);
        if (response.success) {
            await loadClients();
            AlertService.success('Cliente creado');
        }
    }
};

// Editar cliente
window.editClient = async function(id) {
    const result = await AlertService.componentModal(
        '/component/clients/client-form',
        { params: { id } }
    );

    if (result.isConfirmed && result.value) {
        const errors = validator.validate(result.value);
        if (validator.hasErrors(errors)) {
            AlertService.error('Errores: ' + Object.values(errors).flat().join('\n'));
            return;
        }

        const response = await api.update({ id, ...result.value });
        if (response.success) {
            await loadClients();
            AlertService.success('Cliente actualizado');
        }
    }
};

// Eliminar cliente
window.deleteClient = async function(id) {
    if (await AlertService.confirmDelete('este cliente')) {
        const response = await api.delete(id);
        if (response.success) {
            await loadClients();
            AlertService.success('Cliente eliminado');
        }
    }
};

// Escuchar cambios globales de estado
state.on('clients:loaded', (data) => {
    console.log(`${data.count} clientes cargados`);
    // Actualizar badges, contadores, etc.
});
```

### Resultado

- **SIN plantilla rígida** con 3 opciones limitadas
- **CON bloques** agnósticos reutilizables
- **Código limpio**: ~80 líneas para CRUD completo vs ~300 líneas de template
- **Versátil**: mismo código sirve para clientes, productos, proveedores, etc.
- **Sin sorpresas**: cada bloque tiene responsabilidad clara

---

## Patrones de Composición

### Patrón 1: Tabla + API
```javascript
const api = new ApiClient('/api/products');
const tableManager = new TableManager('products-table');

tableManager.onReady(async () => {
    const result = await api.list();
    tableManager.setData(result.data);
});
```

### Patrón 2: Tabla + API + Validación + Estado
```javascript
const api = new ApiClient('/api/products');
const tableManager = new TableManager('products-table');
const state = new StateManager();
const validator = new ValidationEngine({ /* ... */ });

tableManager.onReady(async () => {
    await loadProducts();
});

async function loadProducts() {
    const result = await api.list();
    tableManager.setData(result.data);
    state.setState('products', result.data);
}

async function createProduct(data) {
    const errors = validator.validate(data);
    if (!validator.hasErrors(errors)) {
        await api.create(data);
        await loadProducts();
    }
}
```

### Patrón 3: Tabla + Formulario + API + Validación
```javascript
const api = new ApiClient('/api/products');
const tableManager = new TableManager('products-table');
const form = new FormBuilder({ id: 'product-form', fields: { /* ... */ } });
const validator = new ValidationEngine({ /* ... */ });

tableManager.onReady(async () => {
    await loadProducts();
});

async function saveProduct(isEdit = false) {
    const data = form.getData();
    const errors = validator.validate(data);

    form.setErrors(errors);
    if (form.hasErrors()) {
        form.focusFirstError();
        return false;
    }

    const response = isEdit
        ? await api.update(data)
        : await api.create(data);

    if (response.success) {
        form.clear();
        await loadProducts();
        return true;
    }
    return false;
}
```

---

## Guía de Migración: Template → Bloques

### Antes (Template Rígido)
```javascript
// Hardcoded, duplicado en cada CRUD
const API_BASE = '/api/products';

function loadProducts() {
    fetch(`${API_BASE}/list`)
        .then(r => r.json())
        .then(data => {
            window.legoTable_products_table_api.setGridOption('rowData', data);
            window.legoTable_products_table_updateRowCount();
        });
}

function createProduct(data) {
    fetch(`${API_BASE}/create`, { method: 'POST', body: JSON.stringify(data) })
        .then(r => r.json())
        .then(data => {
            if (data.success) loadProducts();
        });
}
```

### Después (Bloques Agnósticos)
```javascript
// Reutilizable, sin hardcoding
const api = new ApiClient('/api/products');
const tableManager = new TableManager('products-table');

async function loadProducts() {
    const result = await api.list();
    tableManager.setData(result.data);
    tableManager.updateRowCount();
}

async function createProduct(data) {
    const response = await api.create(data);
    if (response.success) await loadProducts();
}
```

---

## Checklist para Nuevos CRUDs

- [ ] Crear Controller con métodos list/create/update/delete
- [ ] Crear HTML Component con TableComponent y formularios
- [ ] Crear archivo JS con bloques modulares:
  - [ ] `ApiClient` para comunicación
  - [ ] `TableManager` para tabla
  - [ ] `ValidationEngine` para validación
  - [ ] `StateManager` para eventos
  - [ ] `FormBuilder` para formularios (si aplica)
- [ ] Implementar funciones create/edit/delete usando bloques
- [ ] Testar integración

---

## Ventajas de Este Enfoque

✅ **Reutilizable**: Mismos bloques para productos, clientes, proveedores, etc.
✅ **Mantenible**: Cambios en ApiClient afectan a todos los CRUDs
✅ **No repetitivo**: No hay duplicación de lógica
✅ **Agnóstico**: Bloques no asumen entity específico
✅ **Escalable**: Agregar nuevos campos es trivial
✅ **Testeable**: Cada bloque se puede probar independientemente
✅ **Flexible**: Componer bloques según necesidad
✅ **Sin sorpresas**: Comportamiento predecible

---

## Referencias

- [ApiClient.js](/assets/js/core/services/ApiClient.js)
- [StateManager.js](/assets/js/core/services/StateManager.js)
- [ValidationEngine.js](/assets/js/core/services/ValidationEngine.js)
- [TableManager.js](/assets/js/core/services/TableManager.js)
- [FormBuilder.js](/assets/js/core/services/FormBuilder.js)
- [ProductsCrud v2 (Ejemplo)](/components/App/ProductsCrud/products-crud-v2.js)
