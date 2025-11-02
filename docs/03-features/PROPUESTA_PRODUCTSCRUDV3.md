# 🚀 PROPUESTA: ProductsCrudV3 - Refactorización Arquitectónica Completa

**Fecha:** 2025-01-01
**Versión:** 1.0
**Autor:** Claude (Anthropic)
**Estado:** Propuesta para Implementación

---

## 📋 ÍNDICE

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Alcance del Proyecto](#alcance-del-proyecto)
3. [Arquitectura de Menús](#arquitectura-de-menús)
4. [Arquitectura de Vistas (Screens)](#arquitectura-de-vistas-screens)
5. [Solución al Problema de Theming](#solución-al-problema-de-theming)
6. [Refactorización de Componentes Base](#refactorización-de-componentes-base)
7. [Eliminación de Código Legacy](#eliminación-de-código-legacy)
8. [Plan de Implementación](#plan-de-implementación)
9. [Testing Futuro (Preparación)](#testing-futuro-preparación)
10. [Checklist de Validación](#checklist-de-validación)

---

## 🎯 RESUMEN EJECUTIVO

### Objetivo

Crear **ProductsCrudV3** desde cero, eliminando completamente las implementaciones anteriores (V1 y V2), y refactorizando los componentes base del framework para prevenir sistemáticamente los errores identificados.

### Filosofía

> **"No arreglamos el botón. Arreglamos el sistema para que sea imposible crear un botón roto."**

### Principios Rectores

1. **Simplicidad primero** - Cada screen es independiente y simple
2. **Separación de responsabilidades** - Tabla, Crear, Ver, Editar son vistas separadas
3. **Theming sistemático** - TODO debe reaccionar al cambio de tema
4. **Componentes reutilizables** - Refactorizar base, no parches superficiales
5. **DX (Developer Experience)** - Fácil de usar, difícil de romper

### Resultado Esperado

- ✅ CRUD funcional con 3 screens independientes (Tabla, Crear, Editar/Ver)
- ✅ Menú padre-hijo en sidebar (`Products CRUD` → `Tabla`, `Crear`, `Editar`)
- ✅ Theming 100% funcional en todos los componentes
- ✅ Componentes base refactorizados (Table, Select, Button, etc.)
- ✅ Zero código legacy de V1/V2
- ✅ API backend intacta (sin cambios)

---

## 📦 ALCANCE DEL PROYECTO

### ✅ Incluido (IN SCOPE)

#### 1. Eliminación Completa

**Archivos a ELIMINAR:**

```
components/App/ProductsCrud/
├── ProductsCrudComponent.php          ❌ BORRAR
├── products-crud.js                   ❌ BORRAR
├── products-crud.css                  ❌ BORRAR
├── Childs/
│   └── ProductForm/
│       ├── ProductFormComponent.php   ❌ BORRAR
│       └── ...                        ❌ BORRAR

components/App/ProductsCrudV2/
├── ProductsCrudV2Component.php        ❌ BORRAR
├── ProductFormPageComponent.php       ❌ BORRAR
├── products-crud-v2-page.js           ❌ BORRAR
├── products-crud-v2-page.css          ❌ BORRAR
├── product-form-page.css              ❌ BORRAR
└── ...                                ❌ BORRAR
```

**Razón:** Empezar desde cero evita arrastrar deuda técnica y malas decisiones arquitectónicas.

#### 2. Refactorización de Componentes Base

**Componentes a REFACTORIZAR:**

```
components/Shared/Essentials/TableComponent/
├── TableComponent.php                 🔧 REFACTORIZAR
├── table.js                           🔧 REFACTORIZAR
├── Dtos/ColumnDto.php                 🔧 REFACTORIZAR
└── Collections/ColumnCollection.php   🔧 REFACTORIZAR

components/Shared/Forms/SelectComponent/
├── SelectComponent.php                🔧 REFACTORIZAR
└── select.js                          🔧 REFACTORIZAR (MVC)

components/Shared/Forms/ButtonComponent/
├── ButtonComponent.php                🔧 REFACTORIZAR
└── button.js                          🔧 REFACTORIZAR

assets/js/core/services/
├── ApiClient.js                       🔧 REFACTORIZAR (validación)
├── ValidationEngine.js                ✅ MANTENER (ya está bien)
├── StateManager.js                    ✅ MANTENER
└── TableManager.js                    🔧 REFACTORIZAR
```

**Razón:** Arreglar en la raíz para que ProductsCrudV3 sea consecuencia de buenos cimientos.

#### 3. Creación de ProductsCrudV3

**Estructura nueva:**

```
components/App/ProductsCrudV3/
├── ProductsCrudV3Component.php        ✨ CREAR (Vista Tabla)
├── ProductCreateComponent.php         ✨ CREAR (Vista Crear)
├── ProductEditComponent.php           ✨ CREAR (Vista Editar/Ver)
├── products-crud-v3.js                ✨ CREAR
├── products-crud-v3.css               ✨ CREAR
└── config/
    └── ProductsCrudConfig.php         ✨ CREAR (Configuración centralizada)
```

**Características:**
- 3 vistas independientes (no modales, no slides)
- Navegación por menú sidebar
- URLs distintas para cada vista
- Theming completo y funcional

#### 4. Arquitectura de Menús

**Registrar menú padre-hijo:**

```php
// En MainComponent.php
new MenuItemDto(
    id: "products_crud",
    name: "Products CRUD",
    url: "#",
    iconName: "cube-outline",
    childs: [
        new MenuItemDto(
            id: "products_crud_table",
            name: "Tabla",
            url: $HOST_NAME . '/component/products-crud-v3',
            iconName: "list-outline"
        ),
        new MenuItemDto(
            id: "products_crud_create",
            name: "Crear",
            url: $HOST_NAME . '/component/products-crud-v3/create',
            iconName: "add-circle-outline"
        ),
        new MenuItemDto(
            id: "products_crud_edit",
            name: "Editar",
            url: $HOST_NAME . '/component/products-crud-v3/edit',
            iconName: "create-outline"
        )
    ]
)
```

#### 5. Sistema de Theming

**Crear guía de theming:**

```
docs/
└── THEMING_GUIDE.md                   ✨ CREAR
```

**Validador automático:**

```
scripts/
└── validate-theming.js                ✨ CREAR (Script que detecta @media prefers-color-scheme)
```

### ❌ Excluido (OUT OF SCOPE)

- ❌ Cambios al API backend (`/api/products/*`)
- ❌ Cambios a base de datos
- ❌ Migración de datos de V1/V2
- ❌ Testing automatizado (se hará en fase posterior)
- ❌ Optimizaciones de performance (se hará después)

---

## 🗂️ ARQUITECTURA DE MENÚS

### Jerarquía de Menús

```
📁 LEGO Framework
├── 🏠 Inicio
├── 📊 Dashboards
├── 📦 Products CRUD (PADRE)
│   ├── 📋 Tabla         → /component/products-crud-v3
│   ├── ➕ Crear         → /component/products-crud-v3/create
│   └── ✏️  Editar       → /component/products-crud-v3/edit
└── ⚙️  Configuración
```

### Implementación en MainComponent.php

**Ubicación:** `components/Core/Home/Components/MainComponent/MainComponent.php`
**Líneas a modificar:** 45-115

**Código a agregar:**

```php
// Después de los menús existentes, agregar:

new MenuItemDto(
    id: "products_crud_v3",
    name: "Products CRUD",
    url: "#",
    iconName: "cube-outline",
    childs: [
        new MenuItemDto(
            id: "products_crud_v3_table",
            name: "Tabla de Productos",
            url: $HOST_NAME . '/component/products-crud-v3',
            iconName: "list-outline"
        ),
        new MenuItemDto(
            id: "products_crud_v3_create",
            name: "Crear Producto",
            url: $HOST_NAME . '/component/products-crud-v3/create',
            iconName: "add-circle-outline"
        ),
        new MenuItemDto(
            id: "products_crud_v3_edit",
            name: "Editar Producto",
            url: $HOST_NAME . '/component/products-crud-v3/edit',
            iconName: "create-outline"
        )
    ]
)
```

### Navegación entre Módulos

**Desde Tabla → Crear:**
```javascript
// Click en botón "Nuevo Producto"
// Abre módulo de crear usando windows-manager
openCreateModule();

// Internamente:
// - moduleStore._openModule('products_crud_v3_create', {...})
// - renderModule() fetch desde /component/products-crud-v3/create
// - Container se inyecta en DOM
```

**Desde Tabla → Editar:**
```javascript
// Click en botón "Editar" de una fila
editProduct(productId);

// Internamente:
// - Módulo único por producto: products_crud_v3_edit_123
// - Permite tener múltiples ediciones abiertas simultáneamente
// - Cada módulo es independiente
```

**Desde Formulario → Tabla:**
```javascript
// Después de crear/editar exitosamente
closeCurrentModule();  // Cierra módulo actual
openTableModule();     // Abre/recarga módulo de tabla

// Internamente:
// - legoWindowManager.closeModule(activeId)
// - _openModule('products_crud_v3_table', '/component/products-crud-v3')
// - Si tabla ya estaba abierta, se recarga con datos frescos
```

### Ventajas de Este Enfoque

1. **Sistema de pestañas** - Múltiples módulos abiertos simultáneamente
2. **Sin recarga de página** - Transiciones fluidas
3. **Estado preservado** - Módulos no activos mantienen su estado
4. **Separación clara** - Cada módulo es componente independiente
5. **Breadcrumb automático** - legoWindowManager actualiza navegación
6. **Fácil debugging** - moduleStore.getModules() muestra todos los módulos activos

---

## 🖥️ ARQUITECTURA DE VISTAS (SCREENS)

### 🔄 Sistema de Navegación (Pestañas Dinámicas)

**IMPORTANTE:** El framework LEGO **NO usa navegación tradicional**. En su lugar:

1. **Sistema de Módulos/Pestañas** - Componentes se cargan dinámicamente sin recargar página
2. **Windows Manager** - Gestiona apertura/cierre de módulos (ver `windows-manager.js`)
3. **ModuleStore** - Mantiene registro de módulos abiertos y activo
4. **Carga Dinámica** - Componentes PHP se renderizan vía API y se inyectan en el DOM

**Flujo de navegación:**

```javascript
// Cuando usuario hace click en menú:
// 1. generateMenuLinks() detecta click
// 2. moduleStore._openModule(id, component)
// 3. renderModule(id, url) - fetch componente desde PHP
// 4. Container se inyecta en #home-page
// 5. Scripts del componente se ejecutan
```

**Por lo tanto, en ProductsCrudV3:**
- ✅ **NO hay `window.location.href`**
- ✅ **NO hay navegación entre páginas**
- ✅ **SÍ hay apertura de módulos diferentes**
- ✅ **SÍ hay comunicación entre módulos via eventos**

---

### Vista 1: Tabla de Productos

**Componente:** `ProductsCrudV3Component.php`
**Ruta API:** `/component/products-crud-v3`
**Module ID:** `products_crud_v3_table`
**Responsabilidades:**
- Mostrar tabla con productos
- Paginación, búsqueda, filtros
- Botones "Nuevo", "Editar", "Eliminar"

**Estructura:**

```php
<?php
namespace Components\App\ProductsCrudV3;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Forms\ButtonComponent\ButtonComponent;

#[ApiComponent('/products-crud-v3', methods: ['GET'])]
class ProductsCrudV3Component extends CoreComponent
{
    protected $CSS_PATHS = ["./products-crud-v3.css"];
    protected $JS_PATHS_WITH_ARG = [];

    public function __construct() {}

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./products-crud-v3.js", [
                'view' => 'table'
            ])
        ];

        // Botón crear (abre módulo de crear)
        $createButton = (new ButtonComponent(
            text: 'Nuevo Producto',
            type: 'button',
            variant: 'primary',
            icon: 'add-outline',
            onClick: "openCreateModule()"
        ))->render();

        // Configurar tabla con nuevo ColumnDto refactorizado
        $config = ProductsCrudConfig::getTableConfig();
        $table = (new TableComponent(
            id: 'products-crud-v3-table',
            columns: $config->columns,
            rowData: [],
            pagination: true,
            paginationPageSize: 10,
            enableExport: true
        ))->render();

        return <<<HTML
        <div class="products-crud-v3-container">
            <div class="products-crud-v3-header">
                <div class="products-crud-v3-title">
                    <h1>Gestión de Productos</h1>
                    <p>CRUD con arquitectura moderna y theming sistemático</p>
                </div>
                {$createButton}
            </div>

            <div class="products-crud-v3-content">
                {$table}
            </div>
        </div>
        HTML;
    }
}
```

**JavaScript:**

```javascript
// products-crud-v3.js (view: table)
const api = new ApiClient('/api/products');
const tableManager = new TableManager('products-crud-v3-table');

tableManager.onReady(async () => {
    configureTableColumns();
    await loadProducts();
});

function configureTableColumns() {
    const columnDefs = ProductsCrudConfig.getColumnDefs();
    tableManager.setColumnDefs(columnDefs);
}

async function loadProducts() {
    const result = await api.list();
    if (result.success) {
        tableManager.setData(result.data);
    }
}

/**
 * Abrir módulo de crear producto
 * Usa el sistema de módulos del framework
 */
window.openCreateModule = function() {
    // Usar windows-manager para abrir nuevo módulo
    if (window.moduleStore && window.generateMenuLinks) {
        const moduleId = 'products_crud_v3_create';
        const moduleUrl = '/component/products-crud-v3/create';

        // Abrir módulo dinámicamente
        if (typeof _openModule === 'function') {
            _openModule(moduleId, moduleUrl);
        } else {
            // Fallback: simular click en menú
            const menuItem = document.querySelector(`[data-module-id="${moduleId}"]`);
            if (menuItem) {
                menuItem.click();
            }
        }
    }
};

/**
 * Abrir módulo de editar producto
 */
window.editProduct = function(id) {
    const moduleId = `products_crud_v3_edit_${id}`;
    const moduleUrl = `/component/products-crud-v3/edit?id=${id}`;

    if (typeof _openModule === 'function') {
        _openModule(moduleId, moduleUrl);
    }
};

window.deleteProduct = async function(id) {
    const confirmed = await AlertService.confirmDelete(`producto #${id}`);
    if (confirmed) {
        const result = await api.delete(id);
        if (result.success) {
            await loadProducts();
            AlertService.success('Producto eliminado');
        }
    }
};
```

---

### Vista 2: Crear Producto

**Componente:** `ProductCreateComponent.php`
**Ruta:** `/component/products-crud-v3/create`
**Responsabilidades:**
- Formulario de creación
- Validación client-side
- Submit a API
- Redirección a tabla

**Estructura:**

```php
<?php
namespace Components\App\ProductsCrudV3;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\ButtonComponent\ButtonComponent;

#[ApiComponent('/products-crud-v3/create', methods: ['GET'])]
class ProductCreateComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./products-crud-v3.css"];
    protected $JS_PATHS_WITH_ARG = [];

    public function __construct() {}

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./products-crud-v3.js", [
                'view' => 'create'
            ])
        ];

        // Campos del formulario
        $nameField = (new InputTextComponent(
            id: 'name',
            label: 'Nombre del Producto',
            placeholder: 'Ej: Laptop Dell XPS 13',
            required: true
        ))->render();

        $skuField = (new InputTextComponent(
            id: 'sku',
            label: 'SKU',
            placeholder: 'Ej: LAPTOP-001',
            required: true
        ))->render();

        $priceField = (new InputTextComponent(
            id: 'price',
            label: 'Precio ($)',
            placeholder: '999.99',
            type: 'number',
            required: true
        ))->render();

        $stockField = (new InputTextComponent(
            id: 'stock',
            label: 'Stock Disponible',
            placeholder: '0',
            type: 'number',
            required: true
        ))->render();

        $categoryField = (new SelectComponent(
            id: 'category',
            label: 'Categoría',
            options: [
                ['value' => 'electronics', 'label' => 'Electrónica'],
                ['value' => 'computers', 'label' => 'Computadoras'],
                ['value' => 'accessories', 'label' => 'Accesorios'],
                ['value' => 'software', 'label' => 'Software'],
                ['value' => 'other', 'label' => 'Otros']
            ],
            required: true,
            searchable: true
        ))->render();

        $descriptionField = (new TextAreaComponent(
            id: 'description',
            label: 'Descripción',
            placeholder: 'Descripción detallada del producto...',
            rows: 4
        ))->render();

        $isActiveField = (new SelectComponent(
            id: 'is_active',
            label: 'Estado',
            options: [
                ['value' => '1', 'label' => '✓ Activo'],
                ['value' => '0', 'label' => '✗ Inactivo']
            ],
            selected: '1',
            required: true
        ))->render();

        // Botones
        $submitButton = (new ButtonComponent(
            text: 'Crear Producto',
            type: 'submit',
            variant: 'primary'
        ))->render();

        $cancelButton = (new ButtonComponent(
            text: 'Cancelar',
            type: 'button',
            variant: 'secondary',
            onClick: 'closeCurrentModule()'
        ))->render();

        return <<<HTML
        <div class="products-crud-v3-container">
            <div class="products-crud-v3-header">
                <div class="products-crud-v3-title">
                    <h1>Crear Nuevo Producto</h1>
                    <p>Complete el formulario para agregar un producto</p>
                </div>
            </div>

            <div class="products-crud-v3-form-container">
                <form id="product-create-form" class="products-crud-v3-form">
                    <!-- Sección: Información Básica -->
                    <fieldset class="form-section">
                        <legend>Información Básica</legend>
                        <div class="form-grid">
                            {$nameField}
                            {$skuField}
                        </div>
                    </fieldset>

                    <!-- Sección: Precios y Stock -->
                    <fieldset class="form-section">
                        <legend>Precios y Stock</legend>
                        <div class="form-grid">
                            {$priceField}
                            {$stockField}
                        </div>
                    </fieldset>

                    <!-- Sección: Clasificación -->
                    <fieldset class="form-section">
                        <legend>Clasificación</legend>
                        <div class="form-grid">
                            {$categoryField}
                            {$isActiveField}
                        </div>
                    </fieldset>

                    <!-- Sección: Descripción -->
                    <fieldset class="form-section">
                        <legend>Descripción</legend>
                        {$descriptionField}
                    </fieldset>

                    <!-- Botones de Acción -->
                    <div class="form-actions">
                        {$cancelButton}
                        {$submitButton}
                    </div>
                </form>
            </div>
        </div>
        HTML;
    }
}
```

**JavaScript:**

```javascript
// products-crud-v3.js (view: create)
const api = new ApiClient('/api/products');
const validator = new ValidationEngine({
    name: { required: true, minLength: 3 },
    sku: { required: true, minLength: 2 },
    price: { required: true, type: 'number', min: 0 },
    stock: { type: 'number', min: 0 },
    category: { required: true },
    description: { minLength: 10 }
});

document.getElementById('product-create-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    await handleCreateSubmit(e.target);
});

async function handleCreateSubmit(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    // Convertir booleanos
    data.is_active = data.is_active === '1';

    // Validar client-side
    const errors = validator.validate(data);
    if (validator.hasErrors(errors)) {
        const errorMessages = Object.entries(errors)
            .map(([field, msgs]) => `${field}: ${msgs[0]}`)
            .join('\n');
        AlertService.error('Errores de validación:\n' + errorMessages);
        return;
    }

    // Crear producto
    const closeLoading = AlertService.loading('Creando producto...');
    try {
        const result = await api.create(data);
        closeLoading();

        if (result.success) {
            AlertService.success('Producto creado correctamente');

            // Cerrar módulo actual y abrir/recargar módulo de tabla
            setTimeout(() => {
                closeCurrentModule();
                openTableModule();
            }, 1000);
        } else {
            AlertService.error(result.message || 'Error al crear producto');
        }
    } catch (error) {
        closeLoading();
        AlertService.error('Error de conexión');
    }
}

/**
 * Cerrar módulo actual
 */
window.closeCurrentModule = function() {
    if (window.moduleStore && window.moduleStore.activeModule) {
        const activeId = window.moduleStore.activeModule;
        if (window.legoWindowManager) {
            window.legoWindowManager.closeModule(activeId);
        }
    }
};

/**
 * Abrir/recargar módulo de tabla
 */
window.openTableModule = function() {
    const moduleId = 'products_crud_v3_table';
    const moduleUrl = '/component/products-crud-v3';

    // Si el módulo ya está abierto, recargarlo
    if (window.moduleStore && window.moduleStore.modules[moduleId]) {
        if (window.legoWindowManager) {
            // Cerrar y reabrir para refrescar datos
            window.legoWindowManager.closeModule(moduleId);
            setTimeout(() => {
                if (typeof _openModule === 'function') {
                    _openModule(moduleId, moduleUrl);
                }
            }, 100);
        }
    } else {
        // Abrir módulo por primera vez
        if (typeof _openModule === 'function') {
            _openModule(moduleId, moduleUrl);
        }
    }
};
```

---

### Vista 3: Editar Producto

**Componente:** `ProductEditComponent.php`
**Ruta:** `/component/products-crud-v3/edit?id=123`
**Responsabilidades:**
- Cargar datos del producto
- Pre-llenar formulario
- Validación client-side
- Submit a API
- Redirección a tabla

**Estructura:**

```php
<?php
namespace Components\App\ProductsCrudV3;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;

#[ApiComponent('/products-crud-v3/edit', methods: ['GET'])]
class ProductEditComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./products-crud-v3.css"];
    protected $JS_PATHS_WITH_ARG = [];

    public function __construct() {}

    protected function component(): string
    {
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if (!$productId) {
            return <<<HTML
            <div class="products-crud-v3-container">
                <div class="error-message">
                    <h2>Error: ID de producto no especificado</h2>
                    <button onclick="window.location.href='/component/products-crud-v3'">
                        Volver a la tabla
                    </button>
                </div>
            </div>
            HTML;
        }

        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./products-crud-v3.js", [
                'view' => 'edit',
                'productId' => $productId
            ])
        ];

        // Formulario igual al de crear, pero:
        // 1. Los valores se llenarán desde JavaScript
        // 2. El botón dirá "Guardar Cambios"
        // 3. Se incluye el ID del producto

        // ... (Mismo código de campos que en ProductCreateComponent)

        $submitButton = (new ButtonComponent(
            text: 'Guardar Cambios',
            type: 'submit',
            variant: 'primary'
        ))->render();

        $cancelButton = (new ButtonComponent(
            text: 'Cancelar',
            type: 'button',
            variant: 'secondary',
            onClick: 'closeCurrentModule()'
        ))->render();

        return <<<HTML
        <div class="products-crud-v3-container">
            <div class="products-crud-v3-header">
                <div class="products-crud-v3-title">
                    <h1>Editar Producto #{$productId}</h1>
                    <p>Modifique los campos necesarios</p>
                </div>
            </div>

            <div class="products-crud-v3-form-container">
                <form id="product-edit-form" class="products-crud-v3-form" data-product-id="{$productId}">
                    <!-- Loading state mientras carga datos -->
                    <div id="form-loading" class="form-loading">
                        <ion-icon name="refresh-outline" class="spin"></ion-icon>
                        <p>Cargando datos del producto...</p>
                    </div>

                    <!-- Formulario (se mostrará cuando cargue) -->
                    <div id="form-content" style="display: none;">
                        <!-- ... campos del formulario ... -->

                        <div class="form-actions">
                            {$cancelButton}
                            {$submitButton}
                        </div>
                    </div>
                </form>
            </div>
        </div>
        HTML;
    }
}
```

**JavaScript:**

```javascript
// products-crud-v3.js (view: edit)
const api = new ApiClient('/api/products');
const productId = context.arg.productId;

// Cargar datos del producto al inicio
(async function initializeEdit() {
    try {
        const result = await api.get(productId);

        if (result.success && result.data) {
            fillFormWithProductData(result.data);
            showForm();
        } else {
            AlertService.error('No se pudo cargar el producto');
        }
    } catch (error) {
        AlertService.error('Error al cargar producto');
    }
})();

function fillFormWithProductData(product) {
    // Usar API pública de componentes (refactorizada)
    LegoInputText.setValue('name', product.name);
    LegoInputText.setValue('sku', product.sku);
    LegoInputText.setValue('price', product.price);
    LegoInputText.setValue('stock', product.stock);

    // SelectComponent con API refactorizada
    LegoSelect.setValue('category', product.category, { silent: true });
    LegoSelect.setValue('is_active', product.is_active ? '1' : '0', { silent: true });

    // TextArea
    document.getElementById('description').value = product.description || '';
}

function showForm() {
    document.getElementById('form-loading').style.display = 'none';
    document.getElementById('form-content').style.display = 'block';
}

// Submit handler
document.getElementById('product-edit-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    await handleEditSubmit(e.target);
});

async function handleEditSubmit(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    data.id = productId; // Agregar ID
    data.is_active = data.is_active === '1';

    // Validar
    const errors = validator.validate(data);
    if (validator.hasErrors(errors)) {
        const errorMessages = Object.entries(errors)
            .map(([field, msgs]) => `${field}: ${msgs[0]}`)
            .join('\n');
        AlertService.error('Errores de validación:\n' + errorMessages);
        return;
    }

    // Actualizar
    const closeLoading = AlertService.loading('Actualizando producto...');
    try {
        const result = await api.update(data);
        closeLoading();

        if (result.success) {
            AlertService.success('Producto actualizado correctamente');

            // Cerrar módulo actual y recargar tabla
            setTimeout(() => {
                closeCurrentModule();
                openTableModule();
            }, 1000);
        } else {
            AlertService.error(result.message || 'Error al actualizar producto');
        }
    } catch (error) {
        closeLoading();
        AlertService.error('Error de conexión');
    }
}
```

---

## 🎨 SOLUCIÓN AL PROBLEMA DE THEMING

### Problema Identificado

**ProductsCrudV2** usa `@media (prefers-color-scheme: dark)` en vez de clases `.dark` / `.light`, lo que causa:

1. ❌ Solo reacciona a preferencia del sistema
2. ❌ NO reacciona al toggle manual de tema
3. ❌ Ignora la elección del usuario guardada en localStorage

**Archivos afectados:**
- `components/App/ProductsCrudV2/products-crud-v2-page.css` (líneas 14, 113-116, 131-135, etc.)
- `components/App/ProductsCrudV2/product-form-page.css` (líneas 37-41, 48-50, etc.)

### Causa Raíz

**Enfoque INCORRECTO (media query):**

```css
.products-crud-v2-container {
    background: var(--color-background, #ffffff); /* Fallback hardcoded */
}

@media (prefers-color-scheme: dark) {
    .products-crud-v2-container {
        background: #111827; /* Solo se aplica con preferencia del sistema */
    }
}
```

**Enfoque CORRECTO (class-based):**

```css
.products-crud-container {
    background: var(--bg-body); /* Variable CSS que cambia con tema */
}

/* O si necesitas override específico: */
body.dark .products-crud-container {
    background: var(--bg-body);
}
```

### Solución Sistémica

#### 1. Usar CSS Variables del Framework

**Variables disponibles** (definidas en `assets/css/core/base.css`):

```css
/* Dark Mode (default) */
html, html.dark {
    --bg-body: #18191a;
    --bg-sidebar: #242526;
    --bg-surface: #3a3b3c;
    --bg-surface-secondary: #2d2e2f;
    --bg-surface-hover: #404040;

    --text-primary: #ffffff;
    --text-secondary: #dddddd;
    --text-tertiary: #b0b0b0;

    --border-light: #404040;
    --border-medium: #5a5a5a;

    --accent-primary: #3ba1ff;
    --accent-hover: #2b91ef;
}

/* Light Mode */
html.light {
    --bg-body: #ffffff;
    --bg-sidebar: #ffffff;
    --bg-surface: #F5F5F5;
    --bg-surface-secondary: #f0f0f0;

    --text-primary: #000000;
    --text-secondary: #707070;

    --border-light: #dddddd;
    --border-medium: #a0a0a0;

    --accent-primary: #3ba1ff;
}
```

#### 2. Reglas de Theming para ProductsCrudV3

**❌ NUNCA HACER:**

```css
/* NO usar media queries de preferencia del sistema */
@media (prefers-color-scheme: dark) {
    .component {
        background: #000;
    }
}

/* NO hardcodear colores con fallbacks */
.component {
    background: var(--bg-body, #ffffff); /* El fallback anula el theming */
}

/* NO usar colores directos */
.component {
    background: #ffffff;
    color: #000000;
}
```

**✅ SIEMPRE HACER:**

```css
/* SÍ usar variables CSS sin fallbacks */
.products-crud-v3-container {
    background: var(--bg-body);
    color: var(--text-primary);
}

/* SÍ usar selectores de clase si necesitas override */
body.dark .products-crud-v3-special {
    background: var(--bg-surface);
}

body.light .products-crud-v3-special {
    background: var(--bg-surface);
}

/* O simplificado: */
.products-crud-v3-special {
    background: var(--bg-surface); /* Ya cambia automáticamente */
}
```

#### 3. Ejemplo Completo: products-crud-v3.css

```css
/**
 * ProductsCrudV3 Styles
 *
 * REGLAS DE THEMING:
 * 1. SOLO usar variables CSS del framework (--bg-*, --text-*, etc.)
 * 2. NO usar @media (prefers-color-scheme)
 * 3. NO usar fallbacks hardcoded
 * 4. Si necesitas override, usar body.dark o body.light
 */

/* Container principal */
.products-crud-v3-container {
    padding: 2rem;
    background: var(--bg-body);
    min-height: 100vh;
}

/* Header */
.products-crud-v3-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-light);
}

.products-crud-v3-title h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
}

.products-crud-v3-title p {
    margin: 0.5rem 0 0 0;
    font-size: 0.9rem;
    color: var(--text-secondary);
}

/* Content area */
.products-crud-v3-content {
    background: var(--bg-surface);
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid var(--border-light);
}

/* Form container */
.products-crud-v3-form-container {
    max-width: 900px;
    margin: 0 auto;
    background: var(--bg-surface);
    border-radius: 8px;
    padding: 2rem;
    border: 1px solid var(--border-light);
}

/* Form sections */
.form-section {
    margin-bottom: 2rem;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 1.5rem;
    background: var(--bg-surface-secondary);
}

.form-section legend {
    padding: 0 0.5rem;
    font-weight: 600;
    color: var(--text-primary);
    font-size: 1.1rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

/* Form actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-light);
}

/* Loading state */
.form-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
}

.form-loading ion-icon {
    font-size: 3rem;
    color: var(--accent-primary);
    margin-bottom: 1rem;
}

.form-loading.spin ion-icon {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Error message */
.error-message {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--bg-surface);
    border-radius: 8px;
    border: 1px solid var(--border-light);
}

.error-message h2 {
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.error-message button {
    margin-top: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .products-crud-v3-container {
        padding: 1rem;
    }

    .products-crud-v3-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions button {
        width: 100%;
    }
}
```

#### 4. Validador Automático de Theming

Crear script que detecte uso incorrecto de theming:

**Archivo:** `scripts/validate-theming.js`

```javascript
#!/usr/bin/env node

/**
 * Validador de Theming - Detecta anti-patterns en CSS
 *
 * Uso: node scripts/validate-theming.js
 */

const fs = require('fs');
const path = require('path');
const glob = require('glob');

const errors = [];

// Buscar todos los archivos CSS
const cssFiles = glob.sync('components/**/*.css', { cwd: __dirname + '/..' });

cssFiles.forEach(filePath => {
    const content = fs.readFileSync(path.join(__dirname, '..', filePath), 'utf-8');
    const lines = content.split('\n');

    lines.forEach((line, index) => {
        const lineNumber = index + 1;

        // Detectar @media (prefers-color-scheme)
        if (line.includes('@media') && line.includes('prefers-color-scheme')) {
            errors.push({
                file: filePath,
                line: lineNumber,
                type: 'media-query',
                message: '❌ Usando @media (prefers-color-scheme) - usar body.dark o body.light'
            });
        }

        // Detectar fallbacks hardcoded en var()
        const varWithFallback = /var\(--[\w-]+,\s*#[0-9a-fA-F]{3,6}\)/;
        if (varWithFallback.test(line)) {
            errors.push({
                file: filePath,
                line: lineNumber,
                type: 'hardcoded-fallback',
                message: '⚠️  Fallback hardcoded en var() - remover para permitir theming'
            });
        }

        // Detectar colores directos (no en comentarios)
        if (!line.trim().startsWith('//') && !line.trim().startsWith('/*')) {
            const directColor = /(background|color|border-color|fill|stroke):\s*#[0-9a-fA-F]{3,6}/;
            if (directColor.test(line)) {
                errors.push({
                    file: filePath,
                    line: lineNumber,
                    type: 'direct-color',
                    message: '⚠️  Color hardcoded - usar variable CSS (--bg-*, --text-*, etc.)'
                });
            }
        }
    });
});

// Reportar errores
if (errors.length > 0) {
    console.log('\n🔍 VALIDACIÓN DE THEMING\n');
    console.log(`❌ Se encontraron ${errors.length} problemas:\n`);

    errors.forEach(error => {
        console.log(`${error.file}:${error.line}`);
        console.log(`   ${error.message}\n`);
    });

    process.exit(1);
} else {
    console.log('\n✅ Validación de theming exitosa - no se encontraron problemas\n');
    process.exit(0);
}
```

**Uso:**

```bash
# En CI/CD o pre-commit hook:
node scripts/validate-theming.js
```

#### 5. Guía de Theming para Desarrolladores

**Archivo:** `docs/THEMING_GUIDE.md`

```markdown
# 🎨 Guía de Theming - LEGO Framework

## Reglas Fundamentales

### ✅ DO (Hacer)

1. **Usar variables CSS del framework**
   ```css
   .component {
       background: var(--bg-body);
       color: var(--text-primary);
   }
   ```

2. **Usar selectores de clase para overrides**
   ```css
   body.dark .component {
       box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
   }

   body.light .component {
       box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
   }
   ```

3. **Testear en ambos temas**
   - Abrir componente en modo claro
   - Hacer toggle a modo oscuro
   - Verificar que TODO cambie

### ❌ DON'T (No Hacer)

1. **NO usar @media (prefers-color-scheme)**
   ```css
   /* ❌ MAL - solo reacciona a preferencia del sistema */
   @media (prefers-color-scheme: dark) {
       .component { background: #000; }
   }
   ```

2. **NO hardcodear colores con fallbacks**
   ```css
   /* ❌ MAL - fallback anula el theming */
   .component {
       background: var(--bg-body, #ffffff);
   }
   ```

3. **NO usar colores directos**
   ```css
   /* ❌ MAL - no reacciona al tema */
   .component {
       background: #ffffff;
       color: #000000;
   }
   ```

## Variables CSS Disponibles

### Backgrounds
- `--bg-body` - Fondo principal de página
- `--bg-sidebar` - Fondo del sidebar
- `--bg-surface` - Fondo de tarjetas/superficies
- `--bg-surface-secondary` - Superficies secundarias
- `--bg-surface-hover` - Estado hover

### Text
- `--text-primary` - Texto principal
- `--text-secondary` - Texto secundario
- `--text-tertiary` - Texto terciario

### Borders
- `--border-light` - Bordes claros
- `--border-medium` - Bordes medios
- `--border-focus` - Estado focus

### Accents
- `--accent-primary` - Color primario de acento
- `--accent-hover` - Estado hover de acento

## Cómo Funciona el Theming

1. **Universal Init** - Script se ejecuta ANTES de render
2. **Theme Manager** - Maneja el toggle de tema
3. **CSS Variables** - Se redefinen en `html.dark` y `html.light`
4. **Componentes** - Usan variables, cambian automáticamente

## Testing Checklist

Antes de aprobar un componente:

- [ ] ¿Usa variables CSS en vez de colores directos?
- [ ] ¿NO usa @media (prefers-color-scheme)?
- [ ] ¿Se ve bien en modo claro?
- [ ] ¿Se ve bien en modo oscuro?
- [ ] ¿Cambia correctamente al hacer toggle?
- [ ] ¿Pasa el validador (`node scripts/validate-theming.js`)?
```

---

## 🔧 REFACTORIZACIÓN DE COMPONENTES BASE

### 1. SelectComponent - Arquitectura MVC

**Problema actual:** `.setValue()` usa `.click()` hack

**Solución:** Separar modelo, vista y controlador

**Archivos a refactorizar:**
- `components/Shared/Forms/SelectComponent/select.js` (líneas 1-296)

**Cambios:**

```javascript
// select.js - REFACTORIZADO

/**
 * SelectComponent - Arquitectura MVC
 *
 * Modelo: Maneja estado (selectedValues)
 * Vista: Renderiza UI (opciones, checkmarks, display)
 * Controlador: Conecta eventos de usuario con modelo
 */

// ==================== MODELO ====================
class SelectModel {
    constructor(id, isMultiple) {
        this.id = id;
        this.isMultiple = isMultiple;
        this.selectedValues = [];
        this.options = [];
        this.listeners = [];
    }

    setValue(value, { silent = false } = {}) {
        const oldValue = this.getValue();

        if (this.isMultiple) {
            this.selectedValues = Array.isArray(value) ? value : [value];
        } else {
            this.selectedValues = value ? [value] : [];
        }

        if (!silent && oldValue !== this.getValue()) {
            this.notify('change', { value: this.getValue(), oldValue });
        }
    }

    getValue() {
        if (this.isMultiple) {
            return this.selectedValues.length > 0 ? this.selectedValues : null;
        }
        return this.selectedValues.length > 0 ? this.selectedValues[0] : null;
    }

    toggleValue(value) {
        const index = this.selectedValues.indexOf(value);
        if (index > -1) {
            this.selectedValues.splice(index, 1);
        } else {
            this.selectedValues.push(value);
        }
        this.notify('change', { value: this.getValue() });
    }

    on(event, callback) {
        this.listeners.push({ event, callback });
        return () => {
            this.listeners = this.listeners.filter(l => l.callback !== callback);
        };
    }

    notify(event, data) {
        this.listeners
            .filter(l => l.event === event)
            .forEach(l => l.callback(data));
    }
}

// ==================== VISTA ====================
class SelectView {
    constructor(container, model) {
        this.container = container;
        this.model = model;
        this.elements = this.cacheElements();

        // Observer: escuchar cambios del modelo
        this.model.on('change', () => this.render());
    }

    cacheElements() {
        return {
            trigger: this.container.querySelector('.lego-select__trigger'),
            dropdown: this.container.querySelector('.lego-select__dropdown'),
            options: this.container.querySelectorAll('.lego-select__option'),
            nativeSelect: this.container.querySelector('.lego-select__native'),
            valueDisplay: this.container.querySelector('.lego-select__value')
        };
    }

    render() {
        this.updateOptions();
        this.updateDisplayText();
        this.updateNativeSelect();
    }

    updateOptions() {
        const selectedValues = this.model.selectedValues;

        this.elements.options.forEach(opt => {
            const optValue = opt.getAttribute('data-value');
            const isSelected = selectedValues.includes(optValue);

            opt.classList.toggle('lego-select__option--selected', isSelected);
            opt.setAttribute('aria-selected', isSelected);

            // Actualizar checkmark sin modificar innerHTML completo
            let checkmark = opt.querySelector('.lego-select__checkmark');
            if (isSelected && !checkmark) {
                checkmark = document.createElement('span');
                checkmark.className = 'lego-select__checkmark';
                checkmark.textContent = '✓';
                opt.appendChild(checkmark);
            } else if (!isSelected && checkmark) {
                checkmark.remove();
            }
        });
    }

    updateDisplayText() {
        const value = this.model.getValue();
        const placeholder = this.elements.trigger.getAttribute('data-placeholder') || 'Selecciona una opción';

        if (this.model.isMultiple) {
            const count = this.model.selectedValues.length;
            this.elements.valueDisplay.textContent = count > 0
                ? `${count} seleccionado${count > 1 ? 's' : ''}`
                : placeholder;
        } else if (value) {
            const selectedOption = Array.from(this.elements.options)
                .find(opt => opt.getAttribute('data-value') === value);
            if (selectedOption) {
                const label = selectedOption.querySelector('.lego-select__option-label');
                this.elements.valueDisplay.textContent = label ? label.textContent : value;
            }
        } else {
            this.elements.valueDisplay.textContent = placeholder;
        }
    }

    updateNativeSelect() {
        if (!this.elements.nativeSelect) return;

        Array.from(this.elements.nativeSelect.options).forEach(opt => {
            opt.selected = this.model.selectedValues.includes(opt.value);
        });
    }
}

// ==================== CONTROLADOR ====================
class SelectController {
    constructor(container) {
        this.container = container;
        this.selectId = container.getAttribute('data-select-id');
        this.nativeSelect = container.querySelector('.lego-select__native');
        this.isMultiple = this.nativeSelect?.hasAttribute('multiple');

        // Crear modelo y vista
        this.model = new SelectModel(this.selectId, this.isMultiple);
        this.view = new SelectView(container, this.model);

        // Inicializar desde HTML
        this.initializeFromHTML();

        // Conectar eventos
        this.attachEventListeners();
        this.setupDropdown();
        this.setupSearch();
    }

    initializeFromHTML() {
        const selectedOptions = Array.from(this.nativeSelect.selectedOptions);
        const values = selectedOptions.map(opt => opt.value);
        if (values.length > 0) {
            this.model.setValue(values, { silent: true });
        }
    }

    attachEventListeners() {
        // Clicks de usuario
        this.view.elements.options.forEach(option => {
            option.addEventListener('click', () => {
                const value = option.getAttribute('data-value');
                this.handleUserSelect(value);
            });
        });

        // Eventos DOM cuando el modelo cambia
        this.model.on('change', (data) => {
            const event = new CustomEvent('lego:select-change', {
                detail: { id: this.selectId, ...data },
                bubbles: true
            });
            this.container.dispatchEvent(event);
            this.nativeSelect.dispatchEvent(new Event('change'));
        });
    }

    handleUserSelect(value) {
        if (this.isMultiple) {
            this.model.toggleValue(value);
        } else {
            this.model.setValue(value);
            this.closeDropdown();
        }
    }

    setupDropdown() {
        const trigger = this.view.elements.trigger;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });

        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.closeDropdown();
            }
        });
    }

    setupSearch() {
        const search = this.container.querySelector('.lego-select__search');
        if (search) {
            search.addEventListener('input', (e) => {
                this.filterOptions(e.target.value.toLowerCase());
            });
        }
    }

    toggleDropdown() {
        this.container.classList.toggle('lego-select--open');
    }

    closeDropdown() {
        this.container.classList.remove('lego-select--open');
    }

    filterOptions(searchTerm) {
        this.view.elements.options.forEach(option => {
            const label = option.querySelector('.lego-select__option-label');
            const text = (label ? label.textContent : option.textContent).toLowerCase();
            const matches = text.includes(searchTerm);
            option.classList.toggle('lego-select__option--hidden', !matches);
        });
    }

    // API pública
    getValue() {
        return this.model.getValue();
    }

    setValue(value, options = {}) {
        this.model.setValue(value, options);
    }

    on(event, callback) {
        return this.model.on(event, callback);
    }
}

// ==================== REGISTRO GLOBAL ====================
const selectInstances = new Map();

document.querySelectorAll('.lego-select').forEach(container => {
    const controller = new SelectController(container);
    const selectId = container.getAttribute('data-select-id');
    selectInstances.set(selectId, controller);
});

// API global (backward compatible)
window.LegoSelect = {
    getValue: (selectId) => {
        return selectInstances.get(selectId)?.getValue() || null;
    },

    setValue: (selectId, value, options = {}) => {
        selectInstances.get(selectId)?.setValue(value, options);
    },

    on: (selectId, event, callback) => {
        return selectInstances.get(selectId)?.on(event, callback);
    },

    getInstance: (selectId) => {
        return selectInstances.get(selectId);
    }
};
```

**Beneficios:**
- ✅ `.setValue()` ya NO usa `.click()`
- ✅ Modo `silent` para no disparar eventos
- ✅ Testeable (modelo, vista, controlador separados)
- ✅ Backward compatible con API existente

---

### 2. ColumnDto - Sistema de Dimensiones

**Problema actual:** Solo acepta `width` en pixels

**Solución:** Crear `DimensionValue` type-safe

**Archivos a crear:**
- `Core/Types/DimensionValue.php` (nuevo)
- `Core/Types/DimensionUnit.php` (nuevo)

**Archivos a refactorizar:**
- `components/Shared/Essentials/TableComponent/Dtos/ColumnDto.php`

**Implementación:**

```php
<?php
// Core/Types/DimensionUnit.php

namespace Core\Types;

enum DimensionUnit: string {
    case PIXELS = 'px';
    case PERCENT = '%';
    case FLEX = 'flex';
    case AUTO = 'auto';
}
```

```php
<?php
// Core/Types/DimensionValue.php

namespace Core\Types;

readonly class DimensionValue {

    private function __construct(
        public float $value,
        public DimensionUnit $unit,
        public array $params = []
    ) {}

    public static function px(float $value): self {
        if ($value < 0) {
            throw new \InvalidArgumentException("Width cannot be negative: {$value}");
        }
        return new self($value, DimensionUnit::PIXELS);
    }

    public static function percent(float $value): self {
        if ($value < 0 || $value > 100) {
            throw new \InvalidArgumentException("Percentage must be between 0-100: {$value}");
        }
        return new self($value, DimensionUnit::PERCENT);
    }

    public static function flex(float $grow, float $shrink = 1, string|int $basis = 'auto'): self {
        return new self($grow, DimensionUnit::FLEX, [
            'grow' => $grow,
            'shrink' => $shrink,
            'basis' => $basis
        ]);
    }

    public static function auto(): self {
        return new self(0, DimensionUnit::AUTO);
    }

    public function toAgGrid(): array {
        return match($this->unit) {
            DimensionUnit::PIXELS => ['width' => (int)$this->value],
            DimensionUnit::PERCENT => ['width' => "{$this->value}%"],
            DimensionUnit::AUTO => ['flex' => 0, 'minWidth' => 100],
            DimensionUnit::FLEX => ['flex' => (int)$this->params['grow']]
        };
    }

    public function toCss(): string {
        return match($this->unit) {
            DimensionUnit::PIXELS => "{$this->value}px",
            DimensionUnit::PERCENT => "{$this->value}%",
            DimensionUnit::AUTO => "auto",
            DimensionUnit::FLEX => "{$this->params['grow']} {$this->params['shrink']} {$this->params['basis']}"
        };
    }
}
```

```php
<?php
// components/Shared/Essentials/TableComponent/Dtos/ColumnDto.php - REFACTORIZADO

namespace Components\Shared\Essentials\TableComponent\Dtos;

use Core\Types\DimensionValue;

readonly class ColumnDto {

    public function __construct(
        public string $field,
        public string $headerName = "",
        public ?DimensionValue $width = null, // ← Cambio de int a DimensionValue
        public bool $sortable = false,
        public bool $filter = false,
        public ?string $pinned = null,
        public bool $resizable = true,
        public bool $editable = false,
        public bool $hide = false,
        public ?string $filterType = null,
        public bool $checkboxSelection = false
    ) {
        $this->validate();
    }

    private function validate(): void {
        if (empty($this->field)) {
            throw new \InvalidArgumentException("ColumnDto: field no puede estar vacío");
        }

        if ($this->pinned && !in_array($this->pinned, ['left', 'right'])) {
            throw new \InvalidArgumentException("ColumnDto: pinned debe ser 'left' o 'right', recibido: {$this->pinned}");
        }

        if ($this->filterType && !in_array($this->filterType, ['text', 'number', 'date'])) {
            throw new \InvalidArgumentException("ColumnDto: filterType inválido: {$this->filterType}");
        }
    }

    public function toArray(): array {
        $config = [
            'field' => $this->field,
            'headerName' => $this->headerName ?: $this->field,
            'resizable' => $this->resizable
        ];

        // Width usando DimensionValue
        if ($this->width) {
            $config = array_merge($config, $this->width->toAgGrid());
        }

        if ($this->sortable) $config['sortable'] = true;

        if ($this->filter) {
            $filterType = $this->filterType ?: 'text';
            $config['filter'] = match($filterType) {
                'number' => 'agNumberColumnFilter',
                'date' => 'agDateColumnFilter',
                default => 'agTextColumnFilter'
            };
        }

        if ($this->pinned) $config['pinned'] = $this->pinned;
        if ($this->editable) $config['editable'] = true;
        if ($this->hide) $config['hide'] = true;
        if ($this->checkboxSelection) $config['checkboxSelection'] = true;

        return $config;
    }
}
```

**Uso:**

```php
// ProductsCrudV3Component.php
use Core\Types\DimensionValue;

$columns = new ColumnCollection(
    new ColumnDto(
        field: 'id',
        headerName: 'ID',
        width: DimensionValue::px(80), // ← Explícito
        pinned: 'left'
    ),
    new ColumnDto(
        field: 'name',
        headerName: 'Nombre',
        width: DimensionValue::flex(2), // ← Flexible, crece 2x
        sortable: true
    ),
    new ColumnDto(
        field: 'price',
        headerName: 'Precio',
        width: DimensionValue::percent(15), // ← 15% del ancho
        sortable: true,
        filterType: 'number'
    ),
    new ColumnDto(
        field: 'actions',
        headerName: 'Acciones',
        width: DimensionValue::px(180),
        pinned: 'right'
    )
);
```

**Beneficios:**
- ✅ Type-safe: Error si pasas string
- ✅ Validación: Excepción si width negativo o percent > 100
- ✅ Explícito: `DimensionValue::px(80)` vs `width: 80`
- ✅ Flexible: Soporta px, %, flex, auto

---

### 3. ApiClient - Validación Robusta

**Problema actual:** No valida `response.ok`, usa POST para GET

**Solución:** Agregar validación, errores tipados, interceptors

**Archivo a refactorizar:**
- `assets/js/core/services/ApiClient.js`

**Cambios clave:**

```javascript
// ApiClient.js - REFACTORIZADO

/**
 * ApiError - Error tipado para API
 */
class ApiError extends Error {
    constructor(status, message, response = null) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.response = response;
    }

    static async fromResponse(response) {
        const text = await response.text();
        try {
            const json = JSON.parse(text);
            return new ApiError(
                response.status,
                json.message || json.msj || 'Error desconocido',
                json
            );
        } catch {
            return new ApiError(
                response.status,
                `HTTP ${response.status}: ${response.statusText}`,
                text
            );
        }
    }

    is404() { return this.status === 404; }
    is422() { return this.status === 422; }
    is500() { return this.status >= 500; }
}

/**
 * ApiClient - Cliente HTTP robusto
 */
class ApiClient {
    constructor(baseUrl, options = {}) {
        if (!baseUrl) throw new Error('baseUrl es requerido');
        this.baseUrl = baseUrl;
        this.options = {
            validateResponses: true,
            throwOnError: true,
            timeout: 30000,
            ...options
        };
        this.interceptors = {
            request: [],
            response: []
        };
    }

    /**
     * Método HTTP genérico con validación
     */
    async request(method, path, { body, query, headers } = {}) {
        const url = new URL(path, this.baseUrl);

        if (query) {
            Object.entries(query).forEach(([key, value]) => {
                url.searchParams.append(key, value);
            });
        }

        let requestInit = {
            method: method.toUpperCase(),
            headers: {
                'Content-Type': 'application/json',
                ...headers
            }
        };

        if (body) {
            requestInit.body = JSON.stringify(body);
        }

        // Interceptores de request
        for (const interceptor of this.interceptors.request) {
            requestInit = await interceptor(requestInit);
        }

        // Fetch con timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.options.timeout);

        try {
            const response = await fetch(url.toString(), {
                ...requestInit,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            // Interceptores de response
            let processedResponse = response;
            for (const interceptor of this.interceptors.response) {
                processedResponse = await interceptor(processedResponse);
            }

            // ✅ VALIDAR response.ok
            if (!processedResponse.ok) {
                const error = await ApiError.fromResponse(processedResponse);
                if (this.options.throwOnError) {
                    throw error;
                }
                return { success: false, error };
            }

            const data = await processedResponse.json();
            return data;

        } catch (error) {
            clearTimeout(timeoutId);

            if (error.name === 'AbortError') {
                throw new ApiError(408, 'Request timeout', null);
            }

            throw error;
        }
    }

    /**
     * Métodos REST (compatibles con backend actual)
     */
    async list(query = {}) {
        return this.request('GET', '/list', { query });
    }

    async get(id) {
        // Backend usa POST /get en vez de GET /:id
        return this.request('POST', '/get', {
            body: { id }
        });
    }

    async create(data) {
        return this.request('POST', '/create', { body: data });
    }

    async update(data) {
        return this.request('POST', '/update', { body: data });
    }

    async delete(id) {
        return this.request('POST', '/delete', {
            body: { id }
        });
    }

    /**
     * Interceptors
     */
    addRequestInterceptor(fn) {
        this.interceptors.request.push(fn);
    }

    addResponseInterceptor(fn) {
        this.interceptors.response.push(fn);
    }
}

// Exportar para uso global
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ApiClient, ApiError };
}
```

**Uso con manejo de errores:**

```javascript
const api = new ApiClient('/api/products');

try {
    const product = await api.get(123);
} catch (error) {
    if (error instanceof ApiError) {
        if (error.is404()) {
            AlertService.error('Producto no encontrado');
        } else if (error.is422()) {
            AlertService.error('Errores de validación: ' + JSON.stringify(error.response.errors));
        } else if (error.is500()) {
            AlertService.error('Error del servidor, intente nuevamente');
        } else {
            AlertService.error('Error desconocido');
        }
    } else {
        AlertService.error('Error de conexión');
    }
}
```

**Beneficios:**
- ✅ Valida `response.ok`
- ✅ Errores tipados (`ApiError`)
- ✅ Helpers (`.is404()`, `.is422()`)
- ✅ Timeout configurable
- ✅ Interceptors para auth/logging

---

### 4. TableManager - Sin Globals

**Problema actual:** Depende de `window.legoTable_xxx_api`

**Solución:** Registry interno, exponer instancia via ID

**Archivo a refactorizar:**
- `assets/js/core/services/TableManager.js`

**Cambios:**

```javascript
// TableManager.js - REFACTORIZADO

class TableManager {
    static registry = new Map(); // ← Registry interno en vez de window

    constructor(tableId, options = {}) {
        this.tableId = tableId;
        this.jsId = this._sanitizeId(tableId);
        this.api = null;
        this.columnApi = null;
        this.isReady = false;
        this.options = {
            pollingTimeout: 10000, // 10 segundos max
            pollingInterval: 100,
            ...options
        };

        // Registrar instancia
        TableManager.registry.set(tableId, this);

        this._initializeTableApi();

        if (!this.api) {
            this._waitForTableReady();
        }
    }

    _initializeTableApi() {
        // Buscar en window (backward compatible)
        const globalApi = window[`legoTable_${this.jsId}_api`];
        const globalColumnApi = window[`legoTable_${this.jsId}_columnApi`];

        if (globalApi && globalColumnApi) {
            this.api = globalApi;
            this.columnApi = globalColumnApi;
            this.isReady = true;
        }
    }

    _waitForTableReady() {
        let elapsed = 0;

        const checkInterval = setInterval(() => {
            this._initializeTableApi();

            if (this.api && this.columnApi) {
                clearInterval(checkInterval);
                this.isReady = true;
                this._notifyReady();
            }

            elapsed += this.options.pollingInterval;

            // ✅ Timeout para evitar polling infinito
            if (elapsed >= this.options.pollingTimeout) {
                clearInterval(checkInterval);
                console.error(`[TableManager] Timeout esperando tabla ${this.tableId}`);
            }
        }, this.options.pollingInterval);
    }

    // ... resto de métodos igual

    /**
     * Método estático para obtener instancia
     */
    static getInstance(tableId) {
        return TableManager.registry.get(tableId);
    }

    /**
     * Método estático para limpiar registry
     */
    static destroyInstance(tableId) {
        const instance = TableManager.registry.get(tableId);
        if (instance) {
            instance.api = null;
            instance.columnApi = null;
            TableManager.registry.delete(tableId);
        }
    }
}
```

**Beneficios:**
- ✅ Registry interno (no contamina window)
- ✅ Timeout en polling
- ✅ Cleanup de instancias

---

## 🗑️ ELIMINACIÓN DE CÓDIGO LEGACY

### Archivos a Eliminar

```bash
# Eliminar ProductsCrud V1
rm -rf components/App/ProductsCrud/

# Eliminar ProductsCrudV2
rm -rf components/App/ProductsCrudV2/
```

### Verificar Referencias

Antes de eliminar, verificar que no haya referencias en:

```bash
# Buscar referencias a ProductsCrud
grep -r "ProductsCrud" components/
grep -r "products-crud" components/
grep -r "/products-crud" components/

# Buscar referencias a ProductsCrudV2
grep -r "ProductsCrudV2" components/
grep -r "products-crud-v2" components/
grep -r "/products-crud-v2" components/
```

### Limpiar Menús

En `MainComponent.php`, comentar o eliminar items de menú de V1/V2:

```php
// ❌ Eliminar estos items:
/*
new MenuItemDto(
    id: "...",
    name: "Products CRUD",
    url: $HOST_NAME . '/component/products-crud',
    iconName: "cube-outline"
),
new MenuItemDto(
    id: "...",
    name: "Products CRUD V2",
    url: $HOST_NAME . '/component/products-crud-v2',
    iconName: "cube-outline"
)
*/
```

---

## 📅 PLAN DE IMPLEMENTACIÓN

### Fase 1: Preparación (Día 1)

**Objetivo:** Crear estructura base y eliminar legacy

#### Tareas:

1. **Crear estructura de carpetas**
   ```bash
   mkdir -p components/App/ProductsCrudV3/config
   mkdir -p Core/Types
   mkdir -p docs
   mkdir -p scripts
   ```

2. **Eliminar código legacy**
   ```bash
   rm -rf components/App/ProductsCrud/
   rm -rf components/App/ProductsCrudV2/
   ```

3. **Crear archivos base**
   ```bash
   touch components/App/ProductsCrudV3/ProductsCrudV3Component.php
   touch components/App/ProductsCrudV3/ProductCreateComponent.php
   touch components/App/ProductsCrudV3/ProductEditComponent.php
   touch components/App/ProductsCrudV3/products-crud-v3.js
   touch components/App/ProductsCrudV3/products-crud-v3.css
   touch components/App/ProductsCrudV3/config/ProductsCrudConfig.php
   ```

4. **Crear sistema de tipos**
   ```bash
   touch Core/Types/DimensionUnit.php
   touch Core/Types/DimensionValue.php
   ```

5. **Crear documentación y scripts**
   ```bash
   touch docs/THEMING_GUIDE.md
   touch scripts/validate-theming.js
   ```

**Checklist:**
- [ ] Estructura de carpetas creada
- [ ] Legacy code eliminado
- [ ] Archivos base creados
- [ ] Sistema de tipos creado
- [ ] Docs y scripts base creados

---

### Fase 2: Refactorización de Base (Día 2-3)

**Objetivo:** Arreglar componentes base ANTES de crear CRUD

#### 2.1 Sistema de Dimensiones

**Archivos:**
- `Core/Types/DimensionUnit.php`
- `Core/Types/DimensionValue.php`
- `components/Shared/Essentials/TableComponent/Dtos/ColumnDto.php`

**Implementación:**
1. Crear enum `DimensionUnit`
2. Crear class `DimensionValue`
3. Refactorizar `ColumnDto` para aceptar `DimensionValue`
4. Agregar validación en constructor

**Testing:**
```php
// Test manual
$width = DimensionValue::px(80);
echo $width->toAgGrid(); // ['width' => 80]

$width = DimensionValue::flex(2);
echo $width->toAgGrid(); // ['flex' => 2]

$width = DimensionValue::percent(25);
echo $width->toAgGrid(); // ['width' => '25%']
```

**Checklist:**
- [ ] `DimensionUnit` enum creado
- [ ] `DimensionValue` class creada
- [ ] `ColumnDto` refactorizado
- [ ] Validación agregada
- [ ] Tests manuales pasados

---

#### 2.2 SelectComponent MVC

**Archivos:**
- `components/Shared/Forms/SelectComponent/select.js`

**Implementación:**
1. Crear `SelectModel` class
2. Crear `SelectView` class
3. Crear `SelectController` class
4. Mantener API global backward compatible

**Testing:**
```javascript
// Test manual en consola
LegoSelect.setValue('test-select', 'option-1');
console.log(LegoSelect.getValue('test-select')); // 'option-1'

// Test con silent mode
LegoSelect.setValue('test-select', 'option-2', { silent: true });
// No debe disparar evento 'lego:select-change'
```

**Checklist:**
- [ ] `SelectModel` implementado
- [ ] `SelectView` implementado
- [ ] `SelectController` implementado
- [ ] API backward compatible
- [ ] `.setValue()` NO usa `.click()`
- [ ] Modo `silent` funciona

---

#### 2.3 ApiClient Robusto

**Archivos:**
- `assets/js/core/services/ApiClient.js`

**Implementación:**
1. Crear `ApiError` class
2. Agregar validación `response.ok`
3. Agregar timeout
4. Agregar interceptors

**Testing:**
```javascript
// Test con servidor caído
const api = new ApiClient('/api/products');
try {
    await api.list();
} catch (error) {
    console.log(error instanceof ApiError); // true
}

// Test con timeout
const api = new ApiClient('/api/products', { timeout: 1000 });
// Simular respuesta lenta en backend
```

**Checklist:**
- [ ] `ApiError` class creada
- [ ] Validación `response.ok`
- [ ] Timeout implementado
- [ ] Interceptors funcionando
- [ ] Manejo de errores mejorado

---

#### 2.4 Theming Sistemático

**Archivos:**
- `docs/THEMING_GUIDE.md`
- `scripts/validate-theming.js`

**Implementación:**
1. Crear guía de theming
2. Crear validador automático
3. Documentar variables CSS
4. Documentar anti-patterns

**Testing:**
```bash
# Ejecutar validador
node scripts/validate-theming.js

# Debe detectar errores en V2 (que ya no existe)
# No debe detectar errores en componentes base
```

**Checklist:**
- [ ] Guía de theming creada
- [ ] Validador funcionando
- [ ] Variables CSS documentadas
- [ ] Anti-patterns documentados

---

### Fase 3: ProductsCrudV3 - Vista Tabla (Día 4)

**Objetivo:** Implementar vista de tabla con columnas porcentuales

#### Tareas:

1. **Implementar ProductsCrudV3Component.php**
   - Usar `DimensionValue` para anchos
   - Botón "Nuevo" que redirige a `/create`
   - Botones "Editar" y "Eliminar" por fila

2. **Implementar products-crud-v3.js (view: table)**
   - Cargar productos con `ApiClient`
   - Configurar tabla con `TableManager`
   - Handlers para navegación

3. **Implementar products-crud-v3.css**
   - Usar SOLO variables CSS
   - NO usar `@media (prefers-color-scheme)`
   - Validar con script

4. **Configuración centralizada**
   - `ProductsCrudConfig.php`
   - Definir columnas UNA VEZ
   - Reutilizar en todas las vistas

**Testing:**
1. Abrir `/component/products-crud-v3`
2. Verificar tabla carga productos
3. Cambiar tema → fondo debe cambiar
4. Verificar anchos adaptativos
5. Click "Nuevo" → redirige a `/create`

**Checklist:**
- [ ] Componente PHP implementado
- [ ] JavaScript implementado
- [ ] CSS con theming correcto
- [ ] Config centralizada
- [ ] Tabla funciona
- [ ] Theming funciona
- [ ] Anchos porcentuales

---

### Fase 4: ProductsCrudV3 - Vista Crear (Día 5)

**Objetivo:** Implementar formulario de creación

#### Tareas:

1. **Implementar ProductCreateComponent.php**
   - Formulario con campos LEGO
   - Botones "Crear" y "Cancelar"

2. **Implementar products-crud-v3.js (view: create)**
   - Validación con `ValidationEngine`
   - Submit con `ApiClient`
   - Redirección a tabla

3. **Testing de validación**
   - Campos vacíos → mostrar errores
   - Datos válidos → crear y redirigir

**Checklist:**
- [ ] Componente PHP implementado
- [ ] Formulario renderiza
- [ ] Validación client-side funciona
- [ ] Submit crea producto
- [ ] Redirección funciona
- [ ] Theming correcto

---

### Fase 5: ProductsCrudV3 - Vista Editar (Día 6)

**Objetivo:** Implementar formulario de edición

#### Tareas:

1. **Implementar ProductEditComponent.php**
   - Formulario igual a crear
   - Botón "Guardar Cambios"
   - Validar ID en query string

2. **Implementar products-crud-v3.js (view: edit)**
   - Cargar producto con `ApiClient.get()`
   - Pre-llenar formulario con `LegoSelect.setValue()` y `LegoInputText.setValue()`
   - Submit con `ApiClient.update()`

3. **Testing de pre-llenado**
   - Abrir `/edit?id=1`
   - Verificar que campos se llenan correctamente
   - Verificar que select de categoría se selecciona

**Checklist:**
- [ ] Componente PHP implementado
- [ ] Carga datos del producto
- [ ] Pre-llena formulario correctamente
- [ ] SelectComponent usa API (no `.click()`)
- [ ] Submit actualiza producto
- [ ] Redirección funciona

---

### Fase 6: Menú y Navegación (Día 7)

**Objetivo:** Integrar menú padre-hijo

#### Tareas:

1. **Registrar menú en MainComponent.php**
   - Crear item padre "Products CRUD"
   - Crear items hijos: Tabla, Crear, Editar

2. **Testing de navegación**
   - Click en "Products CRUD" → expande menú
   - Click en "Tabla" → abre vista tabla
   - Click en "Crear" → abre vista crear
   - Click en "Editar" → abre vista editar (con ID)

3. **Flujo completo**
   - Tabla → Crear → Submit → Tabla
   - Tabla → Editar → Submit → Tabla
   - Tabla → Eliminar → Confirmación → Tabla

**Checklist:**
- [ ] Menú registrado
- [ ] Navegación funciona
- [ ] URLs correctas
- [ ] Flujo completo funciona

---

### Fase 7: Validación Final (Día 8)

**Objetivo:** Verificar que todo funciona

#### Testing Exhaustivo:

1. **Theming**
   ```bash
   node scripts/validate-theming.js
   ```
   - [ ] No detecta errores en ProductsCrudV3

2. **Funcionalidad**
   - [ ] Crear producto funciona
   - [ ] Editar producto funciona
   - [ ] Eliminar producto funciona
   - [ ] Tabla carga datos
   - [ ] Paginación funciona
   - [ ] Búsqueda funciona (si implementada)

3. **Theming Manual**
   - [ ] Tabla se ve bien en light mode
   - [ ] Tabla se ve bien en dark mode
   - [ ] Formulario crear se ve bien en ambos
   - [ ] Formulario editar se ve bien en ambos
   - [ ] Cambiar tema actualiza TODO

4. **Responsive**
   - [ ] Tabla en mobile
   - [ ] Formulario en mobile
   - [ ] Menú en mobile

5. **Validación**
   - [ ] Campos vacíos muestran error
   - [ ] Campos inválidos muestran error
   - [ ] Submit exitoso muestra éxito

---

## ✅ CHECKLIST DE VALIDACIÓN

Antes de considerar el proyecto COMPLETO:

### Arquitectura
- [ ] ProductsCrud V1 eliminado completamente
- [ ] ProductsCrudV2 eliminado completamente
- [ ] ProductsCrudV3 implementado desde cero
- [ ] Componentes base refactorizados (Select, ColumnDto, ApiClient)
- [ ] Sin referencias a código legacy

### Funcionalidad
- [ ] Vista Tabla muestra productos
- [ ] Vista Crear permite crear productos
- [ ] Vista Editar permite editar productos
- [ ] Eliminar producto funciona
- [ ] Validación client-side funciona
- [ ] Redirecciones funcionan
- [ ] API backend NO fue modificada

### Theming
- [ ] `node scripts/validate-theming.js` pasa sin errores
- [ ] NO se usa `@media (prefers-color-scheme)` en ProductsCrudV3
- [ ] SOLO se usan variables CSS (`--bg-*`, `--text-*`)
- [ ] Cambiar tema actualiza TODOS los componentes
- [ ] Light mode se ve correcto
- [ ] Dark mode se ve correcto

### Componentes Base
- [ ] SelectComponent usa MVC (no `.click()`)
- [ ] SelectComponent `.setValue()` tiene modo `silent`
- [ ] ColumnDto acepta `DimensionValue`
- [ ] ColumnDto valida inputs
- [ ] ApiClient valida `response.ok`
- [ ] ApiClient maneja errores con `ApiError`
- [ ] TableManager tiene timeout en polling

### Menú
- [ ] Menú padre "Products CRUD" registrado
- [ ] Menú hijo "Tabla" funciona
- [ ] Menú hijo "Crear" funciona
- [ ] Menú hijo "Editar" funciona
- [ ] Navegación entre vistas funciona

### Documentación
- [ ] `docs/THEMING_GUIDE.md` creado
- [ ] Guía explica variables CSS
- [ ] Guía explica anti-patterns
- [ ] Scripts de validación documentados

### Developer Experience
- [ ] Código simple y fácil de entender
- [ ] Componentes reutilizables
- [ ] Errores claros y útiles
- [ ] No hay globals innecesarios
- [ ] APIs públicas documentadas

---

## 🎯 CRITERIOS DE ÉXITO

El proyecto se considera exitoso cuando:

1. ✅ **Funcionalidad completa**
   - CRUD de productos funciona 100%
   - Todas las operaciones (crear, leer, actualizar, eliminar) funcionan
   - Validación client-side previene errores

2. ✅ **Theming perfecto**
   - Cambiar tema actualiza TODOS los componentes instantáneamente
   - No hay colores hardcoded
   - Validador automático pasa sin errores

3. ✅ **Arquitectura sólida**
   - Componentes base refactorizados siguen principios SOLID
   - SelectComponent usa MVC (testeable, mantenible)
   - ColumnDto type-safe con validación
   - ApiClient robusto con manejo de errores

4. ✅ **Simplicidad**
   - Cada vista es independiente y simple
   - No hay modales complicados
   - No hay slides confusos
   - Navegación clara con URLs

5. ✅ **Mantenibilidad**
   - Fácil agregar nuevas vistas
   - Fácil modificar columnas de tabla
   - Fácil agregar validaciones
   - Difícil cometer errores de theming

---

## 🧪 TESTING FUTURO (Preparación)

### Objetivo

Dejar **espacio preparado** para agregar testing en el futuro sin necesidad de refactorizar código.

### Estructura de Testing Propuesta

```
tests/
├── Unit/                           # Tests unitarios
│   ├── Core/
│   │   ├── Types/
│   │   │   ├── DimensionValueTest.php
│   │   │   └── DimensionUnitTest.php
│   │   └── Services/
│   │       └── ApiClientTest.js
│   ├── Components/
│   │   ├── SelectModelTest.js
│   │   ├── SelectViewTest.js
│   │   └── SelectControllerTest.js
│   └── Validation/
│       └── ValidationEngineTest.js
│
├── Integration/                    # Tests de integración
│   ├── ProductsCrudV3/
│   │   ├── CreateProductFlowTest.php
│   │   ├── EditProductFlowTest.php
│   │   └── DeleteProductFlowTest.php
│   └── Components/
│       └── TableComponentTest.php
│
├── E2E/                            # Tests end-to-end
│   ├── ProductsCrudV3/
│   │   ├── full-crud-flow.spec.js
│   │   └── theming.spec.js
│   └── Navigation/
│       └── module-navigation.spec.js
│
└── Fixtures/                       # Datos de prueba
    ├── products.json
    └── categories.json
```

### Principios para Código Testeable

#### 1. Dependency Injection

**❌ Difícil de testear:**
```javascript
class ProductsController {
    loadProducts() {
        const api = new ApiClient('/api/products'); // ← Hardcoded
        const table = window.legoTable_products_api; // ← Global
    }
}
```

**✅ Fácil de testear:**
```javascript
class ProductsController {
    constructor(apiClient, tableManager) {
        this.api = apiClient;
        this.table = tableManager;
    }

    loadProducts() {
        const data = this.api.list();
        this.table.setData(data);
    }
}

// En tests:
const mockApi = { list: jest.fn() };
const mockTable = { setData: jest.fn() };
const controller = new ProductsController(mockApi, mockTable);
```

#### 2. Separación de Lógica y UI

**✅ ProductsCrudV3 ya implementa esto:**
```javascript
// SelectModel - Testeable independientemente
test('SelectModel.setValue changes value', () => {
    const model = new SelectModel('test', false);
    model.setValue('option-1');
    expect(model.getValue()).toBe('option-1');
});

// SelectView - Testeable con DOM mock
test('SelectView renders selected option', () => {
    const container = document.createElement('div');
    const model = new SelectModel('test', false);
    const view = new SelectView(container, model);
    // ... assertions
});
```

#### 3. Funciones Puras

**✅ DimensionValue es función pura:**
```php
// tests/Unit/Core/Types/DimensionValueTest.php
public function testPxConversion() {
    $dim = DimensionValue::px(80);
    $agGrid = $dim->toAgGrid();
    $this->assertEquals(['width' => 80], $agGrid);
}

public function testNegativeWidthThrows() {
    $this->expectException(\InvalidArgumentException::class);
    DimensionValue::px(-10);
}
```

### Herramientas Sugeridas

**PHP Testing:**
```bash
composer require --dev phpunit/phpunit
composer require --dev mockery/mockery
```

**JavaScript Testing:**
```bash
npm install --save-dev jest
npm install --save-dev @testing-library/dom
npm install --save-dev @testing-library/user-event
```

**E2E Testing:**
```bash
npm install --save-dev playwright
# o
npm install --save-dev cypress
```

### Tests Prioritarios para Fase 1

Una vez ProductsCrudV3 esté implementado, estos tests deberían ser los primeros:

1. **DimensionValue** (simple, alta cobertura)
   ```php
   testPxConversion()
   testPercentConversion()
   testFlexConversion()
   testNegativeWidthThrows()
   testPercentOutOfRangeThrows()
   ```

2. **SelectModel** (core del componente)
   ```javascript
   testSetValueChangesValue()
   testSetValueEmitsEvent()
   testSetValueSilentDoesNotEmit()
   testGetValueReturnsCorrectValue()
   testToggleValueAddsRemoves()
   ```

3. **ApiClient** (crítico para funcionamiento)
   ```javascript
   testListCallsCorrectEndpoint()
   testGetCallsWithId()
   testCreateSendsData()
   testUpdateSendsData()
   testDeleteSendsId()
   test404ThrowsApiError()
   test500ThrowsApiError()
   ```

4. **Theming** (prevenir regresiones)
   ```javascript
   testThemeToggleUpdatesAllComponents()
   testNoMediaQueriesInCSS() // Validador automático
   testAllComponentsUseCSSVariables()
   ```

### Configuración Base

**jest.config.js:**
```javascript
module.exports = {
    testEnvironment: 'jsdom',
    setupFilesAfterEnv: ['<rootDir>/tests/setup.js'],
    moduleNameMapper: {
        '\\.(css|less|scss|sass)$': 'identity-obj-proxy',
    },
    collectCoverageFrom: [
        'assets/js/**/*.js',
        'components/**/*.js',
        '!**/*.spec.js',
        '!**/node_modules/**'
    ],
    coverageThreshold: {
        global: {
            branches: 70,
            functions: 70,
            lines: 70,
            statements: 70
        }
    }
};
```

**phpunit.xml:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">./Core</directory>
            <directory suffix=".php">./components</directory>
        </whitelist>
    </filter>
</phpunit>
```

### Integración con CI/CD (Futuro)

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  php-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: vendor/bin/phpunit

  js-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Node
        uses: actions/setup-node@v2
        with:
          node-version: '18'
      - name: Install dependencies
        run: npm install
      - name: Run tests
        run: npm test
      - name: Run theming validator
        run: node scripts/validate-theming.js
```

### Preparación en ProductsCrudV3

Para facilitar testing futuro, ProductsCrudV3 debe:

1. ✅ **Usar Dependency Injection** - Constructor recibe dependencias
2. ✅ **Separar modelo/vista/controlador** - SelectComponent MVC
3. ✅ **Funciones puras** - DimensionValue sin side effects
4. ✅ **Sin globals innecesarios** - Usar registry en vez de window
5. ✅ **Validación en construcción** - Fail fast, fácil de testear
6. ✅ **Eventos observables** - SelectModel.on() para espiar en tests

### Métricas de Código Testeable

**Indicadores de que el código es testeable:**

- [ ] Funciones < 20 líneas
- [ ] Clases < 200 líneas
- [ ] Dependencias inyectadas (no hardcoded)
- [ ] Sin uso directo de `window` (usar abstracción)
- [ ] Sin side effects en funciones puras
- [ ] Métodos públicos documentados
- [ ] Errores tipados (ApiError, ValidationError)

**Si encuentras código difícil de testear → Refactorizar ANTES de continuar.**

---

## 📝 NOTAS FINALES

### Por Qué Este Enfoque

1. **Empezar desde cero** permite:
   - No arrastrar deuda técnica
   - Aplicar principios aprendidos
   - Crear código limpio desde el inicio

2. **Refactorizar base primero** asegura:
   - ProductsCrudV3 es consecuencia de buenos cimientos
   - Otros CRUDs futuros también serán buenos
   - Los errores NO se repiten

3. **Theming sistemático** previene:
   - Componentes que no reaccionan al tema
   - Uso de media queries incorrectos
   - Colores hardcoded

4. **Vistas separadas** proporciona:
   - Simplicidad en cada screen
   - Navegación natural
   - Mejor UX

### ⚠️ REGLAS DE IMPLEMENTACIÓN (CRÍTICAS)

Durante la implementación de esta propuesta, se deben seguir estas reglas **OBLIGATORIAS**:

#### 1. **Simplicidad en el Uso**
- ✅ API intuitiva: `DimensionValue::px(80)` es claro
- ✅ Nombres descriptivos: `openCreateModule()` no `om()`
- ✅ Pocos parámetros: Máximo 5 parámetros por función
- ❌ NO crear abstracciones complicadas innecesarias

**Ejemplo:**
```php
// ✅ BIEN - Simple y claro
$column = new ColumnDto(
    field: 'name',
    headerName: 'Nombre',
    width: DimensionValue::flex(1)
);

// ❌ MAL - Demasiado complejo
$column = ColumnBuilder::create()
    ->setField('name')
    ->setHeader('Nombre')
    ->setWidth(WidthFactory::createFlex(1))
    ->build();
```

#### 2. **Ideas Creativas (Pero Simples)**
- ✅ Usar patrones existentes del framework (ModuleStore, windows-manager)
- ✅ Soluciones elegantes a problemas complejos (MVC en SelectComponent)
- ❌ NO reinventar la rueda
- ❌ NO copiar código de internet sin entender

**Filosofía:** *"Sé creativo en la solución, no en la sintaxis"*

#### 3. **Cero Duplicación**
- ✅ DRY (Don't Repeat Yourself) siempre
- ✅ Reutilizar componentes base refactorizados
- ✅ Una sola fuente de verdad (config en un lugar)
- ❌ NO copiar-pegar código
- ❌ NO definir columnas en PHP Y JavaScript

**Checklist antes de duplicar:**
1. ¿Existe ya esta función/clase?
2. ¿Puedo hacer genérica la existente?
3. ¿Puedo extraer a utilidad compartida?

#### 4. **Detener y Avisar Antes de Malas Prácticas**

Si durante la implementación te encuentras haciendo **CUALQUIERA** de esto:

❌ **DETENTE Y AVISA:**

1. Usando globals innecesarios (`window.miVariable`)
2. Hardcodeando valores que podrían ser configurables
3. Duplicando lógica que ya existe
4. Creando dependencias circulares
5. Mezclando responsabilidades (UI + lógica + datos en un archivo)
6. Usando `eval()`, `innerHTML` con data no sanitizada
7. Ignorando errores (`try-catch` vacío)
8. Creando abstracciones que nadie va a entender
9. Nombrando variables `data`, `temp`, `aux`, `x`
10. Funciones > 50 líneas sin dividir

**Protocolo:**
```
1. STOP - No escribas ese código
2. THINK - ¿Por qué esto es una mala práctica?
3. ASK - Consultar antes de continuar
4. REFACTOR - Buscar solución correcta
```

#### 5. **Validación de Cimientos**

Antes de crear ProductsCrudV3, validar que los cimientos estén PERFECTOS:

**Checklist de Componentes Base:**
```bash
# SelectComponent
- [ ] .setValue() NO usa .click()
- [ ] Tiene modo silent
- [ ] MVC implementado correctamente
- [ ] API pública documentada

# ColumnDto
- [ ] Acepta DimensionValue
- [ ] Valida en constructor
- [ ] Lanza errores claros

# ApiClient
- [ ] Valida response.ok
- [ ] Maneja errores con ApiError
- [ ] Tiene timeout

# Theming
- [ ] NO usa @media (prefers-color-scheme)
- [ ] Solo variables CSS
- [ ] Pasa validador automático
```

**Si un cimiento está roto → Arreglarlo PRIMERO, CRUD después.**

### Siguiente Paso

Una vez aprobada esta propuesta:

1. **Revisar esta sección de reglas** con todo el equipo
2. **Acordar protocolo** de cuando detener y preguntar
3. **Proceder con Fase 1** - Preparación y eliminación de legacy
4. **Validar cada fase** antes de pasar a la siguiente

**Recuerda:** Es mejor tardar 1 día más haciendo algo bien, que tardar 1 semana arreglando algo mal hecho.

---

**Fin del documento**

*Versión 1.0 - Revisada con sistema de módulos/pestañas y testing futuro*
