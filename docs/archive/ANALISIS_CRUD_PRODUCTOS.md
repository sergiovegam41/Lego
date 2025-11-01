# ANÁLISIS ESTRUCTURAL DEL CRUD DE PRODUCTOS - LEGO FRAMEWORK

## 1. IDENTIFICACIÓN DE ARCHIVOS PRINCIPALES Y SUS PROPÓSITOS

### Estructura de Directorios del CRUD

```
Lego/
├── App/
│   ├── Controllers/Products/Controllers/
│   │   └── ProductsController.php          (REST API - 580 líneas)
│   └── Models/
│       ├── Product.php                     (Modelo Eloquent - 154 líneas)
│       └── ProductImage.php                (Modelo Eloquent - 114 líneas)
├── components/
│   └── App/ProductsCrud/
│       ├── ProductsCrudComponent.php       (Componente Principal - 165 líneas)
│       ├── products-crud.js                (Lógica CRUD - 309 líneas)
│       ├── products-crud.css               (Estilos - 312 líneas)
│       └── Childs/ProductForm/
│           └── ProductFormComponent.php    (Formulario - 231 líneas)
├── database/
│   └── migrations/
│       ├── 2025_01_27_create_products_table.php
│       ├── 2025_01_28_create_product_images_table.php
│       └── 2025_01_29_add_sku_min_stock_to_products.php
└── assets/js/helpers/
    ├── CrudManager.js                      (Abstracción CRUD genérica - 336 líneas)
    └── RestClient.js                       (Cliente HTTP genérico)
```

### Análisis de Archivos: Hardcodeado vs Genérico

#### 1. **ProductsCrudComponent.php** - BASTANTE ACOPLADO A PRODUCTOS

**Hardcodeado:**
- Líneas 41-92: Definición de columnas específicas de productos (name, price, stock, etc.)
- Línea 96: ID de tabla hardcodeado: `'products-crud-table'`
- Línea 105: Nombre de export hardcodeado: `'productos'`
- Línea 122-123: Título y descripción específicos de productos

**Genérico:**
- Línea 8: Importa `TableComponent` que es reutilizable
- Línea 11: Usa componente `Button` genérico
- Sistema de columnas dinámicas (ColumnCollection/ColumnDto)

**Problema:** Las columnas se definen en PHP pero se repiten/redefinen en JavaScript (líneas 50-112 en products-crud.js)

---

#### 2. **ProductFormComponent.php** - MUY ACOPLADO A PRODUCTOS

**Hardcodeado:**
- Línea 31: Ruta específica: `'/products-crud/product-form'`
- Líneas 71-79: Categorías hardcodeadas para productos específicamente
- Líneas 96-115: InputText para "name" y "sku" (campos de producto)
- Líneas 144-153: Select de categoría y InputText de precio (específico de productos)
- Línea 207-210: Endpoints hardcodeados para subida de imágenes

**Específico del formulario:**
```php
uploadEndpoint: '/api/products/upload_image',  // Hardcodeado
deleteEndpoint: '/api/products/delete_image',   // Hardcodeado
reorderEndpoint: '/api/products/reorder_images', // Hardcodeado
setPrimaryEndpoint: '/api/products/set_primary' // Hardcodeado
```

**Problema:** No hay forma de reutilizar este componente para otros CRUDs

---

#### 3. **products-crud.js** - COMPLETAMENTE HARDCODEADO A PRODUCTOS

**Rutas Hardcodeadas:**
```javascript
// Línea 11
const API_BASE = '/api/products';  // HARDCODEADO

// Línea 197
AlertService.componentModal('/component/products-crud/product-form', {...})
// HARDCODEADO

// Línea 238
AlertService.componentModal('/component/products-crud/product-form', {...})
// HARDCODEADO
```

**Funciones Globales Específicas (Líneas 195-306):**
```javascript
window.createProduct = async function() { ... }    // HARDCODEADO
window.editProduct = async function(id) { ... }    // HARDCODEADO
window.deleteProduct = async function(id) { ... }  // HARDCODEADO
```

**Nombres de Tabla Específicos (Línea 20):**
```javascript
if (typeof legoTable_products_crud_table_api !== 'undefined') {
    // Nombre de tabla HARDCODEADO en la variable global
}
```

**Estadísticas Hardcodeadas (Líneas 174-190):**
```javascript
const totalEl = document.getElementById('total-products');      // HARDCODEADO
const activeEl = document.getElementById('active-products');    // HARDCODEADO
const inStockEl = document.getElementById('instock-products');  // HARDCODEADO
```

**Problema:** 150+ líneas de código que se repiten exactamente igual para cada CRUD

---

#### 4. **ProductsController.php** - PARCIALMENTE GENÉRICO

**Específico a Productos:**
- Línea 9: Constante `ROUTE = 'products'` (define la ruta base)
- Líneas 139-147: Campos de creación específicos (name, description, price, stock, etc.)
- Línea 145: Campo `image_url` (legacy, específico del modelo)
- Líneas 194-201: Campos de actualización (hardcodeados en lugar de dinámicos)

**Genérico:**
- Estructura base de controlador (método constructor estándar)
- Manejo de errores (try/catch)
- Sistema de respuestas (ResponseDTO)
- Integración con StorageService para imágenes

**Problema:** Los campos que se actualizan (línea 192-201) son hardcodeados para producto, no dinámicos

---

#### 5. **Product.php y ProductImage.php** - ESPECÍFICOS AL MODELO

**Hardcodeado:**
- Línea 32 (Product): `protected $table = 'products'` 
- Línea 33 (ProductImage): `protected $table = 'product_images'`
- Línea 37-46 (Product): Lista de campos en `$fillable`
- Línea 76-95 (Product): Scopes específicos (`Active`, `ByCategory`, `InStock`)
- Línea 100-121 (Product): Accessors específicos (`priceFormatted`, `statusText`, `availability`)

**Por qué está bien aquí:** Los modelos SÍ deben ser específicos a la tabla/entidad que representan

---

## 2. ANÁLISIS DE DEPENDENCIAS Y ACOPLAMIENTO

### Matriz de Acoplamiento

| Archivo | Acoplado a | Severidad | Razón |
|---------|-----------|-----------|-------|
| ProductsCrudComponent.php | "productos" (nombre, precio, stock) | MEDIA | Columnas hardcodeadas |
| ProductFormComponent.php | "/products-crud" + "/api/products/*" | ALTA | Rutas y endpoints específicos |
| products-crud.js | "/api/products" + "products-crud-table" | CRÍTICA | 300 líneas específicas a producto |
| ProductsController.php | Tabla "products" + campos específicos | MEDIA | Es el modelo así que está OK |
| CrudManager.js | Genérico (endpoint configurable) | BAJA | Acepta config dinámica ✓ |

### Gráfico de Dependencias

```
products-crud.js
  ├─> /api/products (hardcodeado)
  ├─> /component/products-crud/product-form (hardcodeado)
  ├─> AlertService (genérico)
  ├─> legoTable_products_crud_table_api (hardcodeado)
  └─> updateStats() (específico)
       └─> #total-products, #active-products, etc.

ProductFormComponent.php
  ├─> '/products-crud/product-form' (hardcodeado)
  ├─> ImageGalleryComponent (genérico)
  ├─> Form, InputText, Select, etc. (genéricos)
  └─> /api/products/* endpoints (hardcodeados)
       ├─> upload_image
       ├─> delete_image
       ├─> reorder_images
       └─> set_primary

ProductsCrudComponent.php
  ├─> TableComponent (genérico)
  ├─> ColumnCollection/ColumnDto (genéricos)
  ├─> Button component (genérico)
  └─> products-crud.js (específico)

ProductsController.php
  ├─> Product model (específico)
  ├─> ProductImage model (específico)
  ├─> StorageService (genérico)
  └─> ResponseDTO (genérico)
```

---

## 3. IDENTIFICACIÓN DE CÓDIGO REPETIDO

### Funciones Idénticas Repetidas

**Problema 1: Lógica CRUD repetida en products-crud.js**

Las funciones `createProduct()`, `editProduct()`, y `deleteProduct()` (líneas 195-306) implementan patrones idénticos:

```javascript
// Patrón repetido para CREATE:
const result = await AlertService.componentModal('/component/products-crud/product-form', {...});
if (result.isConfirmed && result.value) {
    const closeLoading = AlertService.loading('Creando producto...');
    try {
        const response = await fetch(`${API_BASE}/create`, {...});
        const result = await response.json();
        closeLoading();
        if (result.success) {
            loadProducts();
            AlertService.success('Producto creado correctamente');
        } else {
            AlertService.error(result.message || 'Error al crear producto');
        }
    } catch (error) {
        closeLoading();
        AlertService.error('Error de conexión al crear producto');
    }
}

// Patrón idéntico para EDIT (líneas 236-273)
const result = await AlertService.componentModal(...);
if (result.isConfirmed && result.value) {
    const closeLoadingUpdate = AlertService.loading('Actualizando producto...');
    try {
        const updateResponse = await fetch(`${API_BASE}/update`, {...});
        // EXACTAMENTE el mismo patrón
    } catch (error) { ... }
}

// Patrón idéntico para DELETE (líneas 278-306)
const confirmed = await AlertService.confirmDelete(...);
if (confirmed) {
    const closeLoading = AlertService.loading('Eliminando producto...');
    try {
        const response = await fetch(`${API_BASE}/delete`, {...});
        // EXACTAMENTE el mismo patrón
    } catch (error) { ... }
}
```

**Líneas de código duplicadas:** ~110 líneas (60% del archivo)

---

### Problema 2: Columnas definidas dos veces

**En ProductsCrudComponent.php (líneas 41-92):**
```php
new ColumnDto(
    field: 'id',
    headerName: 'ID',
    width: 80,
    sortable: true,
    filter: true,
    pinned: 'left'
),
// ... 6 columnas más
```

**En products-crud.js (líneas 50-113):**
```javascript
const columnDefs = [
    { field: 'id', headerName: 'ID', width: 80, sortable: true, filter: true, pinned: 'left' },
    // ... EXACTAMENTE las MISMAS columnas
```

**Problema:** Las columnas se definen en PHP pero se redefinen completamente en JavaScript, lo que causa:
- Duplicidad de definiciones
- Inconsistencias si se cambia uno sin el otro
- Dificultad de mantenimiento

---

### Problema 3: Estadísticas calculadas pero comentadas

**En ProductsCrudComponent.php (líneas 129-157):**
```php
<!-- Comentado pero presente -->
<div class="stat-card">
    <div class="stat-icon">📦</div>
    <div class="stat-info">
        <div class="stat-label">Total Productos</div>
        <div class="stat-value" id="total-products">0</div>
    </div>
</div>
```

**En products-crud.js (líneas 174-190):**
```javascript
function updateStats(products) {
    const total = products.length;
    const active = products.filter(p => p.is_active).length;
    const inStock = products.filter(p => p.stock > 0).length;
    const totalValue = products.reduce(...);
    // Se buscan elementos HTML que están comentados
    const totalEl = document.getElementById('total-products');
    // ...
}
```

**Problema:** Código fantasma - estadísticas calculadas pero nunca mostradas

---

### Problema 4: Método formatBytes duplicado

**En ProductFormComponent.php (líneas 221-229):**
```php
private function formatBytes(int $bytes): string
{
    if ($bytes === 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}
```

**En ProductsController.php (líneas 571-579):**
```php
private function formatBytes(int $bytes): string
{
    if ($bytes === 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}
```

**Problema:** Función exactamente igual en dos archivos diferentes - violación DRY

---

## 4. PROBLEMAS ENCONTRADOS EN LA IMPLEMENTACIÓN

### 4.1 Evolución Problemática del Código (Evidencia en Migraciones)

**Commit 613c2b3** muestra la implementación original que evidencia problemas encontrados:

#### Problema 1: SKU faltaba en la migración inicial
- **2025_01_27_create_products_table.php** - No tenía columna SKU
- **2025_01_29_add_sku_min_stock_to_products.php** - Se agregó DESPUÉS

**Causa raíz:** No se diseñó la estructura completa antes de implementar
- El CRUD se creó sin SKU
- Después se necesitó agregar SKU
- Se requirió una migración adicional

**Línea problemática en ProductFormComponent.php (línea 107-114):**
```php
new InputText(
    id: 'sku',  // Campo que inicialmente no existía
    label: 'SKU',
    required: true,  // Pero marcado como requerido desde el inicio
)
```

---

#### Problema 2: min_stock también se agregó después
**2025_01_29_add_sku_min_stock_to_products.php** agrega ambas columnas simultáneamente

**Por qué:** El diseño no contempló el control de inventario desde el inicio

**Línea problemática en ProductFormComponent.php (línea 172-181):**
```php
new InputText(
    id: 'min_stock',  // Campo agregado después
    value: $product['min_stock'] ?? '5',  // Con valor por defecto
)
```

---

#### Problema 3: Gestión de imágenes fue agregada posteriormente
- **2025_01_27_create_products_table.php** - Tenía `image_url` (string de 500 caracteres)
- **2025_01_28_create_product_images_table.php** - Se creó tabla separada DESPUÉS

**Causa raíz:** Falta de escalabilidad en el diseño inicial
- Primero se hizo simple: una URL por producto
- Después se necesitó: múltiples imágenes por producto
- Ahora hay código legacy (`image_url`) junto con nuevo (`product_images`)

**Prueba en ProductFormComponent.php (línea 203-213):**
```php
ImageGalleryComponent::create(  // Sistema complejo para imágenes
    id: 'product-gallery',
    entityId: $productId,
    existingImages: $existingImages,
    uploadEndpoint: '/api/products/upload_image',  // Endpoints específicos
    deleteEndpoint: '/api/products/delete_image',
    reorderEndpoint: '/api/products/reorder_images',
    setPrimaryEndpoint: '/api/products/set_primary',
    maxFiles: 10,
    maxFileSize: 5242880 // 5MB
)
```

Y en Product.php (línea 142-151):
```php
public function getPrimaryImageUrlAttribute(): ?string
{
    // Primero intentar con la relación de imágenes
    $primaryImage = $this->primaryImage;
    if ($primaryImage && $primaryImage->url) {
        return $primaryImage->url;
    }
    
    // Fallback al campo legacy image_url
    return $this->image_url;  // Legacy support - evidencia de cambio posterior
}
```

---

### 4.2 Errores y Soluciones Implementadas

#### Error 1: Tabla product_images - Necesario para escalabilidad
**Solución:** Crear tabla separada con relaciones 1:N
**Implementado en:** 2025_01_28_create_product_images_table.php

#### Error 2: SKU no estaba en el modelo
**Solución:** Agregar columna SKU con índice para búsqueda rápida
**Implementado en:** 2025_01_29_add_sku_min_stock_to_products.php

#### Error 3: Falta control de inventario bajo
**Solución:** Agregar columna min_stock para alertas
**Implementado en:** 2025_01_29_add_sku_min_stock_to_products.php

---

### 4.3 Inconsistencias Arquitectónicas

#### Inconsistencia 1: Existe CrudManager.js (genérico) pero ProductsCrud usa código específico

**CrudManager.js disponible (líneas 37-335):**
```javascript
class CrudManager {
    constructor(config) {
        // Acepta endpoint, formPath, tableId, entityName dinámicamente
        this.config = {
            modalWidth: '700px',
            prefix: null,
            ...config
        };
        this.api = new RestClient(this.config.endpoint);
    }
    
    async create() { /* Lógica genérica */ }
    async edit(id) { /* Lógica genérica */ }
    async delete(id) { /* Lógica genérica */ }
    expose() {
        // Crea window.create${entityName}()
        // Crea window.edit${entityName}(id)
        // Crea window.delete${entityName}(id)
    }
}
```

**Pero products-crud.js NO LO UTILIZA.**

En su lugar, reimplementa TODO manualmente (líneas 11-309):
```javascript
const API_BASE = '/api/products';  // Hardcodeado

window.createProduct = async function() { /* ~35 líneas */ }
window.editProduct = async function(id) { /* ~37 líneas */ }
window.deleteProduct = async function(id) { /* ~30 líneas */ }
```

**¿Por qué?** Probabilidad: El CrudManager se creó DESPUÉS de ProductsCrud, por lo que el código de productos no fue refactorizado.

---

## 5. OPORTUNIDADES DE MEJORA

### 5.1 Arquitectura Propuesta: Sistema CRUD Genérico

#### ANTES (Actual - Específico a Productos)

```
ProductsCrudComponent.php ────────────> products-crud.js
     ├─> Columnas hardcodeadas             ├─> API_BASE = '/api/products'
     └─> Tabla ID hardcodeado              ├─> Funciones específicas
                                           ├─> Rutas hardcodeadas
                                           └─> Estadísticas específicas

ProductFormComponent.php ───────────────> /api/products/upload_image
     ├─> Ruta hardcodeada                  /api/products/delete_image
     ├─> Categorías hardcodeadas           /api/products/reorder_images
     └─> Endpoints hardcodeados
```

#### DESPUÉS (Propuesto - Genérico)

```
GenericCrudComponent.php (configurable)
     ├─> Acepta {entity, columns, endpoints}
     ├─> Dinámicamente
     └─> Carga CSS/JS específicos

generic-crud.js (reutilizable)
     ├─> new CrudManager(config).expose()
     └─> Todo automático

GenericFormComponent.php (configurable)
     ├─> Acepta {entity, fields, endpoints}
     └─> Renderiza campos dinámicamente
```

---

### 5.2 Refactorización Específica: products-crud.js

**ACTUAL (309 líneas específicas):**
```javascript
const API_BASE = '/api/products';

window.createProduct = async function() { /* 35 líneas */ }
window.editProduct = async function(id) { /* 37 líneas */ }
window.deleteProduct = async function(id) { /* 30 líneas */ }
window.loadProducts = async function() { /* 48 líneas */ }
window.configureTableColumns = function() { /* 75 líneas */ }
```

**PROPUESTO (4 líneas genéricas):**
```javascript
// Auto-carga de configuración
const config = window.CRUD_CONFIG || {
    endpoint: '/api/products',
    formPath: '/component/products-crud/product-form',
    tableId: 'products-crud-table',
    entityName: 'Producto'
};

// Una sola línea para crear toda la interfaz
new CrudManager(config).expose().loadInitialData();
```

---

### 5.3 Abstracción de ImageGalleryComponent

**ACTUAL (ProductFormComponent.php líneas 203-213):**
```php
ImageGalleryComponent::create(
    id: 'product-gallery',
    entityId: $productId,
    existingImages: $existingImages,
    uploadEndpoint: '/api/products/upload_image',    // Hardcodeado
    deleteEndpoint: '/api/products/delete_image',    // Hardcodeado
    reorderEndpoint: '/api/products/reorder_images', // Hardcodeado
    setPrimaryEndpoint: '/api/products/set_primary', // Hardcodeado
    maxFiles: 10,
    maxFileSize: 5242880
)
```

**PROPUESTO (Genérico):**
```php
ImageGalleryComponent::create(
    id: 'product-gallery',
    entityId: $productId,
    existingImages: $existingImages,
    entity: 'products',  // Una sola configuración
    maxFiles: 10,
    maxFileSize: 5242880
)
// ImageGalleryComponent internamente construye:
// /api/{entity}/upload_image
// /api/{entity}/delete_image
// /api/{entity}/reorder_images
// /api/{entity}/set_primary
```

---

### 5.4 Consolidación de Lógica Duplicada

**PROBLEMA: formatBytes() existe en 2 archivos**

**SOLUCIÓN: Crear class/trait reutilizable**

```php
// Crear: Core/Helpers/FileHelper.php
namespace Core\Helpers;

class FileHelper {
    public static function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}

// Usar en ProductFormComponent.php
use Core\Helpers\FileHelper;

private function formatBytes(int $bytes): string {
    return FileHelper::formatBytes($bytes);
}
```

---

### 5.5 Patrones de Diseño Aplicables

#### Patrón 1: **Factory Pattern** para Componentes CRUD

```php
class CrudComponentFactory {
    public static function create(string $entity, array $config): GenericCrudComponent {
        return new GenericCrudComponent(
            entity: $entity,
            columns: $config['columns'] ?? [],
            endpoints: $config['endpoints'] ?? [],
            actions: $config['actions'] ?? ['create', 'read', 'update', 'delete']
        );
    }
}

// Uso:
$productsComponent = CrudComponentFactory::create('products', [
    'columns' => [
        ['field' => 'name', 'label' => 'Nombre'],
        ['field' => 'price', 'label' => 'Precio'],
    ],
    'endpoints' => ['/api/products/list', '/api/products/create']
]);
```

---

#### Patrón 2: **Strategy Pattern** para Validaciones

```php
interface ValidationStrategy {
    public function validate(array $data): array; // [isValid => bool, errors => []]
}

class ProductValidation implements ValidationStrategy {
    public function validate(array $data): array {
        $errors = [];
        if (empty($data['name'])) $errors[] = 'Nombre requerido';
        if (empty($data['sku'])) $errors[] = 'SKU requerido';
        if (!is_numeric($data['price'])) $errors[] = 'Precio inválido';
        return ['isValid' => empty($errors), 'errors' => $errors];
    }
}

// En ProductsController.php:
class ProductsController {
    private ValidationStrategy $validator;
    
    public function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        $validation = $this->validator->validate($data);
        if (!$validation['isValid']) {
            Response::json(400, new ResponseDTO(false, 'Validación fallida', $validation['errors']));
        }
    }
}
```

---

#### Patrón 3: **Configuration Object Pattern**

```php
// Crear: App/Config/EntityConfig.php
class EntityConfig {
    public function __construct(
        public string $entity,
        public array $columns,
        public array $fillable,
        public array $validation,
        public array $endpoints,
        public ?string $imageField = null
    ) {}
}

// Definir para productos:
$productConfig = new EntityConfig(
    entity: 'products',
    columns: [
        ['field' => 'id', 'label' => 'ID', 'width' => 80],
        ['field' => 'name', 'label' => 'Nombre', 'width' => 200],
    ],
    fillable: ['name', 'sku', 'price', 'stock', 'category'],
    validation: [...],
    endpoints: [...],
    imageField: 'product_images'
);

// Reutilizar en múltiples componentes
$crud = new GenericCrudComponent($productConfig);
```

---

### 5.6 Documentación Faltante

#### Falta: Diagrama de Flujo de Datos

```
Usuario → ProductsCrudComponent
          ├─> Renderiza tabla (TableComponent)
          ├─> Carga products-crud.js
          │   ├─> fetch /api/products/list
          │   └─> Actualiza tabla dinámicamente
          └─> Botón "Nuevo Producto"
              └─> createProduct()
                  ├─> AlertService.componentModal()
                  ├─> ProductFormComponent cargado
                  │   ├─> Campos dinámicos
                  │   └─> ImageGalleryComponent
                  ├─> fetch /api/products/create
                  ├─> fetch /api/products/upload_image (para cada imagen)
                  └─> loadProducts() (recarga tabla)
```

#### Falta: Documentación de Convenciones

- ¿Cómo crear un nuevo CRUD? (debería ser 1 línea)
- ¿Dónde van las validaciones?
- ¿Cómo agregar un nuevo campo?
- ¿Cómo personalizar las columnas de la tabla?

---

## 6. RESUMEN EJECUTIVO

### Puntos Críticos

| Aspecto | Estado | Severidad | Impacto |
|---------|--------|-----------|---------|
| Hardcoding de rutas | 3+ archivos | CRÍTICA | No reutilizable |
| Código duplicado | ~150 líneas | ALTA | Mantenimiento difícil |
| Inconsistencia arquitectónica | CrudManager no usado | ALTA | Deuda técnica |
| Evolución del schema | Múltiples migraciones | MEDIA | Falta diseño previo |
| Documentación | Inexistente | MEDIA | Nuevos devs desorientados |

### Causa Raíz

**El CRUD se implementó de forma específica (productos) en lugar de genérica (entity).**

Mientras que existen herramientas genéricas disponibles (CrudManager, RestClient, AlertService), el código de productos las ignoró y reimplementó todo manualmente.

### Recomendaciones Prioritarias

1. **CORTO PLAZO:** Refactorizar products-crud.js para usar CrudManager
2. **MEDIANO PLAZO:** Crear GenericFormComponent reutilizable
3. **LARGO PLAZO:** Sistema de configuración centralizado para CRUDs

---

## Archivos Analizados

- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/components/App/ProductsCrud/ProductsCrudComponent.php`
- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/components/App/ProductsCrud/products-crud.js`
- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/components/App/ProductsCrud/Childs/ProductForm/ProductFormComponent.php`
- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/components/App/ProductsCrud/products-crud.css`
- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/App/Controllers/Products/Controllers/ProductsController.php`
- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/App/Models/Product.php`
- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/App/Models/ProductImage.php`
- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/assets/js/helpers/CrudManager.js`
- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/database/migrations/2025_01_27_create_products_table.php`
- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/database/migrations/2025_01_28_create_product_images_table.php`
- `/Users/serioluisvegamartinez/Documents/GitHub/Lego/database/migrations/2025_01_29_add_sku_min_stock_to_products.php`

