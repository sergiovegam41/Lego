# ProductsCrudV3 - CRUD Modular de Productos

## 📂 Estructura de Carpetas (Nueva)

```
ProductsCrudV3/
├── ProductsCrudV3Component.php     ← Componente principal (tabla de productos)
├── products-crud-v3.css            ← Estilos de la tabla
├── products-crud-v3.js             ← Lógica de la tabla (navegación, callbacks)
├── README.md                       ← Este archivo
└── childs/                         ← Componentes hijos (formularios)
    ├── ProductCreate/
    │   ├── ProductCreateComponent.php
    │   ├── product-create.js
    │   └── product-form.css
    └── ProductEdit/
        ├── ProductEditComponent.php
        ├── product-edit.js
        └── product-form.css
```

**📖 Ver documentación completa:** [Estructura de Carpetas para Componentes](/docs/COMPONENT_FOLDER_STRUCTURE.md)

---

## 🎯 Filosofía de Diseño

### Separación de Responsabilidades

Cada componente tiene una responsabilidad única:

| Componente | Responsabilidad | Ruta |
|------------|----------------|------|
| **ProductsCrud V3** | Mostrar tabla de productos con paginación server-side | `/products-crud-v3` |
| **ProductCreate** | Formulario para crear nuevos productos | `/products-crud-v3/create` |
| **ProductEdit** | Formulario para editar productos existentes | `/products-crud-v3/edit` |

### Ventajas de esta Estructura

✅ **Clara separación:** Cada componente está autocontenido en su carpeta
✅ **Escalable:** Agregar más funcionalidades (view, delete) es fácil
✅ **Mantenible:** Fácil localizar archivos relacionados
✅ **Reutilizable:** Componentes hijos comparten estilos (product-form.css)
✅ **Refleja jerarquía:** Estructura de carpetas = estructura conceptual

---

## 🏗️ Componente Principal: ProductsCrudV3

### Propósito

Componente enfocado ÚNICAMENTE en mostrar la tabla de productos con:
- Server-side pagination automática
- Filtros y ordenamiento
- Acciones por fila (Editar, Eliminar)
- Navegación hacia formularios de crear/editar

### Archivos

- **ProductsCrudV3Component.php**: Renderiza la tabla usando TableComponent
- **products-crud-v3.css**: Estilos específicos de la tabla
- **products-crud-v3.js**:
  - Gestión de tabla con TableManager
  - Callbacks para acciones (edit, delete)
  - Navegación a formularios usando módulos

### Características

- ✅ Model-driven con `Product::class`
- ✅ Paginación server-side desde `/api/get/products`
- ✅ RowActions con callbacks personalizados
- ✅ Navegación usando `openModuleWithMenu()` (no `window.location.href`)
- ✅ Theming automático con variables CSS

---

## 📝 Componentes Hijos

### ProductCreate

**Ubicación:** `childs/ProductCreate/`
**Namespace:** `Components\App\ProductsCrudV3\Childs\ProductCreate`

**Propósito:** Formulario para crear nuevos productos

**Archivos:**
- `ProductCreateComponent.php`: Renderiza formulario vacío
- `product-create.js`: Validación y envío a `/api/products/create`
- `product-form.css`: Estilos compartidos del formulario

**Flujo:**
1. Usuario hace clic en "Nuevo Producto" en tabla
2. Se abre módulo con `openCreateModule()`
3. Formulario se valida client-side
4. POST a `/api/products/create`
5. Auto-cierre y recarga de tabla
6. Ítem de menú dinámico "Nuevo Producto" aparece y desaparece

### ProductEdit

**Ubicación:** `childs/ProductEdit/`
**Namespace:** `Components\App\ProductsCrudV3\Childs\ProductEdit`

**Propósito:** Formulario para editar productos existentes

**Archivos:**
- `ProductEditComponent.php`: Renderiza formulario con datos del producto
- `product-edit.js`: Carga producto, valida y actualiza
- `product-form.css`: Estilos compartidos del formulario

**Flujo:**
1. Usuario hace clic en "Editar" en fila de tabla
2. Se abre módulo con `openEditModule(productId)`
3. Sistema usa ventana reutilizable `products-crud-v3-edit`
4. Producto se carga vía `/api/products/{id}`
5. Formulario pre-poblado se muestra
6. PUT a `/api/products/update`
7. Auto-cierre y recarga de tabla
8. Solo UN ítem de menú "Editar Producto" (reutilizable)

---

## 🔗 Flujo de Navegación

```
┌─────────────────────────────────┐
│  ProductsCrudV3 (Tabla)         │
│  Route: /products-crud-v3       │
│  Namespace: ProductsCrudV3      │
└────────┬────────────────┬───────┘
         │                │
    ┌────▼─────┐     ┌────▼─────┐
    │  Crear   │     │  Editar  │
    └────┬─────┘     └────┬─────┘
         │                │
         │                │
    ┌────▼────────────────▼───────┐
    │  childs/                    │
    │  ├── ProductCreate/         │
    │  │   Namespace: ...Childs   │
    │  │   .ProductCreate         │
    │  └── ProductEdit/           │
    │      Namespace: ...Childs   │
    │      .ProductEdit           │
    └─────────────────────────────┘
```

---

## 🚀 Cómo Usar

### Ver Tabla de Productos

```
URL: /products-crud-v3
Component: ProductsCrudV3Component
```

La tabla se carga automáticamente con:
- Paginación server-side (20 items por página)
- Filtros por categoría, activo/inactivo
- Ordenamiento por nombre, precio, stock, etc.

### Crear Producto

**Desde la tabla:**
```javascript
// Botón "Nuevo Producto"
openCreateModule();
```

**Abre:**
```
URL: /products-crud-v3/create
Component: childs/ProductCreate/ProductCreateComponent
Module ID: products-crud-v3-create
Menu Item: "Nuevo Producto" (dinámico, temporal)
```

### Editar Producto

**Desde una fila:**
```javascript
// Botón "Editar" en fila
handleEditProduct(rowData, tableId);
```

**Abre:**
```
URL: /products-crud-v3/edit?id={productId}
Component: childs/ProductEdit/ProductEditComponent
Module ID: products-crud-v3-edit (ÚNICO, reutilizable)
Menu Item: "Editar Producto" (reemplaza contenido al editar otros)
```

**Nota:** Solo existe UNA ventana de edición que reemplaza su contenido al editar diferentes productos. Esto evita proliferación de ítems de menú.

---

## 🛠️ Tecnologías Utilizadas

### PHP Components

- **TableComponent**: Tabla con AG Grid
- **InputTextComponent**: Inputs de texto
- **TextAreaComponent**: Descripción del producto
- **SelectComponent**: Selector de categoría
- **FilePondComponent**: Upload de imágenes

### JavaScript

- **TableManager**: Gestión de AG Grid
- **ValidationEngine**: Validación client-side
- **ApiClient**: Fetch con manejo de errores
- **ModuleStore**: Sistema de módulos/pestañas
- **ThemeManager**: Cambio de tema dark/light
- **WindowManager**: Gestión de ventanas y menú dinámico

### CSS

- **Variables de tema**: Sistema unificado de theming
- **Grid layout**: Formularios responsivos
- **Transitions**: Animaciones suaves

---

## 📦 APIs Consumidas

| Endpoint | Método | Propósito |
|----------|--------|-----------|
| `/api/get/products` | GET | Listar productos con paginación |
| `/api/products/create` | POST | Crear nuevo producto |
| `/api/products/{id}` | GET | Obtener producto por ID |
| `/api/products/update` | PUT | Actualizar producto existente |
| `/api/products/delete` | POST | Eliminar producto |

---

## 🎨 Theming

Todos los componentes usan el **nuevo sistema de variables CSS** para theming automático:

```css
/* Ejemplo: products-crud-v3.css */
.products-crud-header {
    background: var(--bg-surface);
    color: var(--text-primary);
    border: 1px solid var(--border-light);
}
```

**Sin JavaScript necesario** - los colores cambian automáticamente al hacer toggle del tema.

**📖 Ver documentación:** [Sistema de Theming](/docs/THEMING_README.md)

---

## 🔄 Mejoras vs V1/V2

### V1 (Antiguo)
❌ Todo en un solo archivo
❌ Modales para crear/editar
❌ Código duplicado
❌ window.location.href para navegación
❌ Colores hardcodeados

### V2 (Intermedio)
⚠️ Componentes separados pero sin organización
⚠️ Archivos mezclados en carpeta raíz
⚠️ Difícil de mantener

### V3 (Actual) ✅
✅ Estructura de carpetas jerárquica con `childs/`
✅ Componentes completamente separados
✅ Navegación con módulos (no page reload)
✅ Server-side pagination model-driven
✅ Ventana de edición reutilizable (evita proliferación)
✅ Sistema de theming automático
✅ Validación unificada
✅ Auto-cierre de formularios
✅ Items de menú dinámicos (fantasma)
✅ Namespaces reflejan estructura

---

## 🤝 Contribuir

Al agregar nuevas funcionalidades (ej: ProductView, ProductDelete):

### 1. Crear carpeta en `childs/`

```bash
mkdir childs/ProductView
```

### 2. Crear archivos del componente

```
childs/ProductView/
├── ProductViewComponent.php
├── product-view.js
└── product-view.css
```

### 3. Usar namespace correcto

```php
<?php
namespace Components\App\ProductsCrudV3\Childs\ProductView;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;

#[ApiComponent('/products-crud-v3/view', methods: ['GET'])]
class ProductViewComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./product-view.css"];
    protected $JS_PATHS = ["./product-view.js"];

    // ...
}
```

### 4. Agregar navegación en `products-crud-v3.js`

```javascript
function openViewModule(productId) {
    window.legoWindowManager.openModuleWithMenu({
        moduleId: `products-crud-v3-view-${productId}`,
        parentMenuId: '10-1',
        label: `Ver #${productId}`,
        url: `/component/products-crud-v3/view?id=${productId}`,
        icon: 'eye-outline'
    });
}

window.openViewModule = openViewModule;
```

### 5. Agregar acción en tabla (opcional)

```php
// En ProductsCrudV3Component.php
$actions = new RowActionsCollection(
    // ... acciones existentes
    new RowActionDto(
        id: "view",
        label: "Ver",
        icon: "eye-outline",
        callback: "handleViewProduct",
        variant: "secondary",
        tooltip: "Ver detalles"
    )
);
```

---

## ✅ Checklist de Calidad

Este componente cumple con:

- [x] Estructura de carpetas jerárquica con `childs/`
- [x] Separación de responsabilidades (SRP)
- [x] Namespaces reflejan estructura de carpetas
- [x] Sin colores hardcodeados (usa variables CSS)
- [x] Theming automático dark/light
- [x] Navegación con módulos (no page reload)
- [x] Validación client-side consistente
- [x] Manejo de errores robusto
- [x] Auto-cierre de formularios
- [x] Items de menú dinámicos con gestión inteligente
- [x] Ventana de edición reutilizable
- [x] Documentación completa

---

## 📚 Documentación Relacionada

- **[Estructura de Carpetas para Componentes](/docs/COMPONENT_FOLDER_STRUCTURE.md)** - Guía completa
- **[Sistema de Theming](/docs/THEMING_README.md)** - Theming automático
- **[TableComponent Guide](/docs/TABLE_COMPONENT.md)** - Uso de tablas

---

**Versión:** 3.0
**Última actualización:** 2025-11-02
**Mantenido por:** LEGO Framework Team
