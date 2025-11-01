# ProductsCrudV2 - Página Hijo con Formulario (Sin Modales)

## Visión General

ProductsCrudV2 es un nuevo patrón de CRUD que **abandona los modales tradicionales** en favor de **páginas hijo dinámicamente cargadas que se deslizan desde el lado derecho**, creando una experiencia de usuario más natural y fluida.

```
ANTES (Modal):                    DESPUÉS (Página Hijo):
┌─────────────────────┐          ┌─────────────────────────────┐
│ TABLA DE PRODUCTOS  │          │    TABLA DE PRODUCTOS │Forn│
│ ┌──────────────────┐│          │ (se oscurece levemente)│ula│
│ │ ┌────────────────┤│ Modal    │ Crear  │Editar │Eliminar│rio│
│ │ │ FORMULARIO     ││ bloquea  │ ────────────────────────  ││
│ │ │ CENTRADO       ││ flujo    │ ID │ Nombre │ SKU │Precio││
│ │ │               ││          │ ────────────────────────  ││
│ │ │ Cancelar│Crear││          │ ...more rows...           ││
│ │ └────────────────┤│          │                           ││
│ └──────────────────┘│          └─────────────────────────────┘
│ (User siente interrumpido)     (User siente continuidad)
└─────────────────────┘
```

## Características Principales

### ✅ Página Hijo Dinámica
- Se carga dinámicamente desde el servidor cuando se necesita
- No ocupa espacio en memoria hasta que se abre
- Se destruye cuando se cierra (no hay estado flotante)

### ✅ Transiciones Suaves
- Overlay oscurece lentamente: `opacity: 0 → 1` (0.3s)
- Página slide in desde derecha: `translateX(100%) → 0` (0.3s)
- Al cerrar: se revierte suavemente

### ✅ Flujo Natural
- El usuario siente que abre "páginas" no "popups"
- Similar a navegación mobile
- La tabla permanece visible y legible de fondo

### ✅ Bloques Modulares
- ApiClient para HTTP
- ValidationEngine para validación
- StateManager para eventos
- TableManager para tabla
- Sin hardcoding de entidades específicas

### ✅ Carga Lazy
- Componente formulario se carga solo cuando se abre
- Reduce carga inicial
- Mejor rendimiento


## Arquitectura

```
┌──────────────────────────────────────────────────────────────┐
│                    ProductsCrudV2                            │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ ProductsCrudV2Component.php                            │ │
│  │ • Renderiza tabla AG Grid                              │ │
│  │ • Carga products-crud-v2-page.js                       │ │
│  │ • Button "Crear" → openCreatePage()                    │ │
│  │ • Contenedor vacío para página hijo                    │ │
│  └────────────────────────────────────────────────────────┘ │
│                          ↓ (JavaScript)                      │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ products-crud-v2-page.js                               │ │
│  │ • Bloques modulares: ApiClient, TableManager, etc.     │ │
│  │ • openCreatePage() → loadFormPageComponent()           │ │
│  │ • showFormPage() con transiciones CSS                  │ │
│  │ • handleFormSubmit() con validación                    │ │
│  │ • closeFormPage() con animación de cierre              │ │
│  └────────────────────────────────────────────────────────┘ │
│                          ↓ (HTTP)                            │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ ProductFormPageComponent.php                           │ │
│  │ • @ApiComponent('/products-crud-v2/product-form-page') │ │
│  │ • GET params: action=create|edit, product_id (opt)     │ │
│  │ • Renderiza formulario HTML con componentes LEGO       │ │
│  │ • Incluye ImageGallery si es edit                      │ │
│  │ • Retorna HTML sin <html><head><body>                 │ │
│  └────────────────────────────────────────────────────────┘ │
│                          ↓ (API)                             │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ ProductsController (Backend)                           │ │
│  │ • POST /api/products/create                            │ │
│  │ • POST /api/products/update                            │ │
│  │ • POST /api/products/delete                            │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

## Flujo de Usuario

### 1️⃣ Usuario ve tabla
```
┌─────────────────────────────────────────┐
│ Gestión de Productos                    │
│ [Nuevo Producto] ← usuario hace clic    │
├─────────────────────────────────────────┤
│ ID │ Nombre │ SKU │ Precio │ Stock │... │
├─────────────────────────────────────────┤
│  1 │ Laptop │ ... │  999   │  10   │... │
│  2 │ Mouse  │ ... │   29   │  50   │... │
└─────────────────────────────────────────┘
```

### 2️⃣ Se carga componente formulario
```
JavaScript ejecuta:
1. openCreatePage()
2. const html = loadFormPageComponent(null, {})
   → Fetch GET /component/products-crud-v2/product-form-page?action=create
3. showFormPage(html, 'create')
```

### 3️⃣ Página hijo se anima
```
┌───────────────────────────────────────────────────────────┐
│ Gestión de Productos (oscurecida)    ┌──────────────────┐ │
│ [Nuevo Producto]                     │ Crear Producto   │ │
├────────────────┼──────────────────────┼──────────────────┤ │
│ ID │ Nombre    │ [Overlay fade-in]   │ Nombre:  [_____] │ │
├────────────────┼──────────────────────┼──────────────────┤ │
│  1 │ Laptop    │ [Page slide-in]     │ SKU:     [_____] │ │
│  2 │ Mouse     │                     │ Precio:  [_____] │ │
│                │                     │ Stock:   [_____] │ │
│                │                     │ Categoría:[____]▼│ │
│                │                     │                  │ │
│                │                     │ [Cancelar][Crear]│ │
│                │                     └──────────────────┘ │
└───────────────────────────────────────────────────────────┘

CSS:
- overlay: opacity 0 → 1
- page: translateX(100%) → 0
```

### 4️⃣ Usuario llena formulario y envía
```
JavaScript:
1. handleFormSubmit(form, 'create')
2. Obtiene datos con FormData
3. Valida con ValidationEngine
   - Si errores: muestra inline en campos
   - Si válido: continúa
4. Llamada: api.create(data)
   → POST /api/products/create
5. Backend crea producto
```

### 5️⃣ Formulario se cierra
```
JavaScript:
1. closeFormPage()
2. Anima cierre:
   - page: translateX(0) → 100%
   - overlay: opacity 1 → 0
3. setTimeout(300ms): limpia HTML

Resultado:
┌─────────────────────────────────────────┐
│ Gestión de Productos                    │
│ [Nuevo Producto]                        │
├─────────────────────────────────────────┤
│ ID │ Nombre │ SKU │ Precio │ Stock │... │
├─────────────────────────────────────────┤
│  1 │ Laptop │ ... │  999   │  10   │... │
│  2 │ Mouse  │ ... │   29   │  50   │... │
│  3 │ NUEVO  │ ... │  500   │  20   │... │ ← Nuevo producto
└─────────────────────────────────────────┘
```

## Archivos Creados

### 1. ProductsCrudV2Component.php
**Ubicación**: `/components/App/ProductsCrudV2/ProductsCrudV2Component.php`

Componente principal que renderiza:
- Encabezado con botón "Nuevo Producto"
- Tabla AG Grid
- Contenedor para página hijo: `#products-form-page-container`

```php
#[ApiComponent('/products-crud-v2', methods: ['GET'])]
class ProductsCrudV2Component extends CoreComponent
{
    // Renderiza tabla y contenedor
}
```

**Acceso**: `GET /products-crud-v2` o desde menú si se configura

### 2. ProductFormPageComponent.php
**Ubicación**: `/components/App/ProductsCrudV2/ProductFormPageComponent.php`

Componente dinámico para crear/editar:
- Recibe parámetros: `action=create|edit`, `product_id` (opcional)
- Renderiza formulario con componentes LEGO
- Si edit: muestra galería de imágenes

```php
#[ApiComponent('/products-crud-v2/product-form-page', methods: ['GET'])]
class ProductFormPageComponent extends CoreComponent
{
    // Auto-descubierto por ApiRouteDiscovery
}
```

**Acceso**:
- `GET /component/products-crud-v2/product-form-page?action=create`
- `GET /component/products-crud-v2/product-form-page?action=edit&product_id=5`

### 3. products-crud-v2-page.js
**Ubicación**: `/components/App/ProductsCrudV2/products-crud-v2-page.js`

Implementación completa con bloques modulares:

```javascript
// Bloques modulares (reutilizables)
const api = new ApiClient('/api/products');
const tableManager = new TableManager('products-crud-v2-table');
const validator = new ValidationEngine({ ... });
const state = new StateManager();

// Abierto
window.openCreatePage = async function() { ... }
window.openEditPage = async function(id) { ... }

// Cerrado
window.closeFormPage = function() { ... }

// Carga dinámica de componente
async function loadFormPageComponent(id, data) { ... }

// Transiciones y DOM
function showFormPage(html, mode) { ... }

// Manejo de formulario
async function handleFormSubmit(form, mode) { ... }

// Operaciones CRUD
window.deleteProduct = async function(id) { ... }
```

### 4. Estilos: products-crud-v2-page.css
**Ubicación**: `/components/App/ProductsCrudV2/products-crud-v2-page.css`

- Contenedor principal
- Animaciones de página hijo
- Overlay oscurecedor
- Responsive design
- Dark mode

Key animations:
```css
.product-form-page {
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.product-form-page.active {
    opacity: 1;
    transform: translateX(0);
}
```

### 5. Estilos: product-form-page.css
**Ubicación**: `/components/App/ProductsCrudV2/product-form-page.css`

- Estilo de campos de formulario
- Validación visual
- Compatibilidad con componentes LEGO
- Tema claro/oscuro


## Cómo Usar

### 1. Acceder a ProductsCrudV2
- Opción A: Mediante ruta directa `/products-crud-v2`
- Opción B: Agregar entrada en el menú

### 2. Ver tabla
```
La tabla carga automáticamente productos vía ApiClient
- TableManager conecta a AG Grid
- ValidationEngine está listo para validación
- StateManager escucha eventos
```

### 3. Crear producto
```
Click "Nuevo Producto"
→ openCreatePage() se ejecuta
→ Componente se carga dinámicamente
→ Página slide in desde derecha
→ Usuario llena formulario
→ Envía
→ Validación en cliente
→ API call si válido
→ Tabla recarga
→ Página se cierra
```

### 4. Editar producto
```
Click botón editar en fila
→ openEditPage(id) se ejecuta
→ Obtiene datos del producto vía ApiClient
→ Componente se carga con action=edit
→ Galería de imágenes visible
→ Usuario edita
→ Envía
→ Similar al crear
```

### 5. Eliminar producto
```
Click botón eliminar en fila
→ Confirmación
→ Si confirma: api.delete(id)
→ Tabla recarga
```


## Ventajas vs Modales

| Aspecto | Modal | Página Hijo |
|---------|-------|------------|
| UX | Interrumpida | Fluida |
| Animación | Aparecer en centro | Slide desde lado |
| Contex visual | Pierde tabla | Puede ver tabla atrás |
| Mobile-like | No | Sí |
| Carga de JS | Todo al cargar | Lazy (cuando se abre) |
| Sensación | "Pop-up" | "Navegación" |
| Responsive | Difícil en móvil | Natural |
| Dark mode | Fácil | Fácil |


## Bloques Modulares Utilizados

### ApiClient
```javascript
const api = new ApiClient('/api/products');
await api.list()      // GET /api/products/list
await api.get(1)      // GET /api/products/get/1
await api.create(data) // POST /api/products/create
await api.update(data) // POST /api/products/update
await api.delete(id)  // POST /api/products/delete
```

### TableManager
```javascript
const tableManager = new TableManager('products-crud-v2-table');
tableManager.onReady(() => { /* tabla lista */ })
tableManager.setData(products)
tableManager.setColumnDefs(columns)
tableManager.updateRowCount()
tableManager.setLoading(true)
```

### ValidationEngine
```javascript
const validator = new ValidationEngine({
    name: { required: true, minLength: 3 },
    email: { required: true, patternName: 'email' },
    price: { type: 'number', min: 0 }
});
const errors = validator.validate(data);
if (validator.hasErrors(errors)) { /* mostrar errores */ }
```

### StateManager
```javascript
const state = new StateManager();
state.setState('products', products)
state.on('product:created', (product) => { /* evento */ })
state.emit('product:created', newProduct)
```


## Diferencias con ProductsCrudV1

| Feature | V1 | V2 |
|---------|----|----|
| Formulario | Modal | Página hijo |
| Carga | Toda al iniciar | Lazy (cuando abre) |
| Código | Hardcoded para productos | Bloques modulares agnósticos |
| Transición | Modal en centro | Slide desde derecha |
| Tabla visible | No mientras edita | Sí (fondo oscuro) |
| User experience | "Pop-up" interrumpe | "Navegación" natural |


## Patrón Reutilizable

ProductsCrudV2 es un patrón que se puede aplicar a cualquier entidad:

```javascript
// Mismo código, solo cambiar endpoint
const api = new ApiClient('/api/clients');    // Clientes
const api = new ApiClient('/api/invoices');   // Facturas
const api = new ApiClient('/api/suppliers');  // Proveedores
```

Solo necesitas:
1. Un controlador con métodos: list, create, update, delete
2. Un componente principal con tabla
3. Un componente formulario
4. Un archivo JS con los bloques modulares
5. Estilos CSS (puedes reutilizar)

**Todo reutilizable, sin hardcoding de entidades.**


## Próximos Pasos

1. **Acceder a /products-crud-v2** para ver en funcionamiento
2. **Crear segundo CRUD** (ej: Clientes) usando mismo patrón
3. **Extraer estilos comunes** en una hoja CSS reutilizable
4. **Crear más bloques modulares** según sea necesario
5. **Documentar patrones** para el equipo


## Referencias

- [MODULAR_BLOCKS_GUIDE.md](./MODULAR_BLOCKS_GUIDE.md) - Bloques modulares
- [MODULAR_BLOCKS_ARCHITECTURE.md](./MODULAR_BLOCKS_ARCHITECTURE.md) - Arquitectura
- ProductsCrudV2Component.php - Componente principal
- ProductFormPageComponent.php - Componente formulario
- products-crud-v2-page.js - Lógica principal
- products-crud-v2-page.css - Estilos


## Filosofía LEGO

ProductsCrudV2 encarna la filosofía de LEGO Framework:

> "Serie de elementos muy compatibles que pueda juntar de manera versátil y que pueda armar un CRUD o cualquier otra cosa de forma ágil y sin sorpresas"

✅ **Compatible**: Bloques ApiClient, TableManager, etc.
✅ **Versátil**: Funciona para cualquier entidad
✅ **Ágil**: Crear nuevo CRUD en 30 minutos
✅ **Sin sorpresas**: Comportamiento predecible, código limpio
