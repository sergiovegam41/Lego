# 📖 Guía de #[ApiGetResource] - API de Solo Lectura para Tablas

## 🎯 Propósito

`#[ApiGetResource]` es un atributo PHP 8 que expone automáticamente **endpoints GET de solo lectura** para alimentar componentes de tabla (TableComponent) con:
- Paginación server-side (offset, cursor, page)
- Filtros dinámicos
- Búsqueda global
- Ordenamiento personalizado

**Diferencia con #[ApiCrudResource]:**
- `#[ApiGetResource]`: Solo GET (2 rutas) → `/api/get/{resource}`
- `#[ApiCrudResource]`: CRUD completo (5 rutas) → `/api/{resource}`

---

## 🚀 Uso Básico

### Ejemplo Mínimo

```php
use Core\Attributes\ApiGetResource;

#[ApiGetResource]
class Product extends Model {}
```

**Rutas generadas automáticamente:**
```
GET /api/get/products        → Listar con paginación, filtros, búsqueda
GET /api/get/products/{id}   → Obtener por ID
```

**Respuesta de GET /api/get/products:**
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "Producto 1", "price": 99.99, ...},
    {"id": 2, "name": "Producto 2", "price": 149.99, ...}
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8,
    "from": 1,
    "to": 20
  }
}
```

---

## ⚙️ Configuración Completa

```php
#[ApiGetResource(
    endpoint: 'products',              // SIN /api/get (se agrega automáticamente)
    pagination: 'offset',              // offset | cursor | page
    perPage: 20,                       // 1-100 elementos por página
    sortable: ['id', 'name', 'price'], // Campos ordenables
    filterable: ['category', 'active'], // Campos filtrables
    searchable: ['name', 'description'] // Campos buscables
)]
class Product extends Model {}
```

### Parámetros

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `endpoint` | `string` | Auto-generado | Ruta **sin** `/api/get` (ej: `'products'` o `'catalog/items'`) |
| `pagination` | `string` | `'offset'` | Tipo: `'offset'`, `'cursor'`, `'page'` |
| `perPage` | `int` | `20` | Elementos por página (1-100) |
| `sortable` | `array` | `[]` | Campos permitidos para `?sort=` |
| `filterable` | `array` | `[]` | Campos permitidos para `?filter[]=` |
| `searchable` | `array` | `[]` | Campos para búsqueda global `?search=` |
| `middleware` | `array` | `[]` | Middlewares a aplicar |
| `hidden` | `array` | `[]` | Campos ocultos en respuesta |
| `appends` | `array` | `[]` | Campos adicionales a incluir |
| `controllerClass` | `string` | `AbstractGetController::class` | Controlador custom |

---

## 🔍 Query Parameters

### Paginación (offset)

```
GET /api/get/products?page=2&limit=50
```

**Respuesta:**
```json
{
  "pagination": {
    "current_page": 2,
    "per_page": 50,
    "total": 150,
    "last_page": 3
  }
}
```

### Paginación (cursor)

```
GET /api/get/products?cursor=eyJpZCI6MjB9&limit=20
```

**Respuesta:**
```json
{
  "pagination": {
    "next_cursor": "eyJpZCI6NDB9",
    "has_more": true,
    "per_page": 20
  }
}
```

### Ordenamiento

```
GET /api/get/products?sort=price&order=asc
```

- `sort`: Campo (debe estar en `sortable`)
- `order`: `asc` o `desc`

### Filtros

```
GET /api/get/products?filter[category]=electronics&filter[is_active]=1
```

Solo funcionan campos definidos en `filterable`.

### Búsqueda Global

```
GET /api/get/products?search=laptop
```

Busca en todos los campos definidos en `searchable` con `ILIKE %{search}%`.

### Combinado

```
GET /api/get/products?search=laptop&filter[category]=electronics&sort=price&order=asc&page=1&limit=25
```

---

## 📁 Organización de Rutas

### Auto-generación (Recomendado)

```php
#[ApiGetResource]
class Product extends Model {}
```

**Genera:** `/api/get/products`

### Personalización

```php
#[ApiGetResource(endpoint: 'catalog/items')]
class Product extends Model {}
```

**Genera:** `/api/get/catalog/items`

### Versionado

```php
#[ApiGetResource(endpoint: 'v2/products')]
class Product extends Model {}
```

**Genera:** `/api/get/v2/products`

### Por Módulos

```php
// App/Models/Blog/Post.php
#[ApiGetResource(endpoint: 'blog/posts')]
class Post extends Model {}

// App/Models/Blog/Comment.php
#[ApiGetResource(endpoint: 'blog/comments')]
class Comment extends Model {}
```

**Estructura:**
```
/api/get/blog/posts
/api/get/blog/comments
```

---

## ⚠️ Validaciones

### ❌ NO incluir /api o /api/get en endpoint

```php
// ❌ INCORRECTO
#[ApiGetResource(endpoint: '/api/get/products')]

// ❌ INCORRECTO
#[ApiGetResource(endpoint: '/api/products')]

// ✅ CORRECTO
#[ApiGetResource(endpoint: 'products')]
```

**Error lanzado:**
```
InvalidArgumentException: Endpoint should not include '/api/get' or '/api' prefix.
It's added automatically.
```

### ❌ Paginación inválida

```php
// ❌ INCORRECTO
#[ApiGetResource(pagination: 'invalid')]

// ✅ CORRECTO
#[ApiGetResource(pagination: 'offset')]  // o 'cursor' o 'page'
```

### ❌ perPage fuera de rango

```php
// ❌ INCORRECTO
#[ApiGetResource(perPage: 500)]

// ✅ CORRECTO (1-100)
#[ApiGetResource(perPage: 50)]
```

---

## 🔧 Ejemplo Completo

### Modelo

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Core\Attributes\ApiGetResource;

#[ApiGetResource(
    endpoint: 'products',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'name', 'price', 'stock', 'created_at'],
    filterable: ['category', 'is_active'],
    searchable: ['name', 'description', 'sku']
)]
class Product extends Model
{
    protected $fillable = [
        'name', 'sku', 'description', 'price',
        'stock', 'category', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];
}
```

### Registro de Rutas (Routes/Api.php)

```php
use Core\Routing\ApiGetRouter;

ApiGetRouter::registerRoutes();
```

### Consumo desde Frontend

```javascript
// Lista con paginación
fetch('/api/get/products?page=1&limit=20')
  .then(res => res.json())
  .then(data => {
    console.log(data.data);         // Array de productos
    console.log(data.pagination);   // Info de paginación
  });

// Búsqueda
fetch('/api/get/products?search=laptop&filter[category]=electronics')
  .then(res => res.json())
  .then(data => console.log(data));

// Por ID
fetch('/api/get/products/1')
  .then(res => res.json())
  .then(data => console.log(data.data));
```

---

## 🔀 Diferencias con ApiCrudResource

| Característica | ApiGetResource | ApiCrudResource |
|---------------|----------------|-----------------|
| **Rutas generadas** | 2 (GET) | 5 (GET, POST, PUT, DELETE) |
| **Patrón de ruta** | `/api/get/{resource}` | `/api/{resource}` |
| **Operaciones** | Solo lectura | CRUD completo |
| **Propósito** | Alimentar tablas | API REST completa |
| **Paginación** | ✅ Incluida | ✅ Incluida |
| **Filtros** | ✅ Incluidos | ✅ Incluidos |
| **Búsqueda** | ✅ Incluida | ✅ Incluida |
| **Crear/Editar/Eliminar** | ❌ No | ✅ Sí |
| **Colisión de rutas** | ❌ No (usa `/api/get/`) | ⚠️ Posible con controladores existentes |

---

## 🎨 Casos de Uso

### 1. Tabla de Productos (Básico)

```php
#[ApiGetResource]
class Product extends Model {}
```

**Uso:** Mostrar lista de productos en TableComponent sin operaciones de escritura.

---

### 2. Catálogo con Búsqueda Avanzada

```php
#[ApiGetResource(
    endpoint: 'catalog/products',
    pagination: 'offset',
    perPage: 50,
    sortable: ['name', 'price', 'rating', 'created_at'],
    filterable: ['category_id', 'brand_id', 'is_featured', 'in_stock'],
    searchable: ['name', 'description', 'sku', 'tags']
)]
class Product extends Model {}
```

**Uso:** E-commerce con múltiples filtros y búsqueda.

---

### 3. Feed Infinito (Cursor-based)

```php
#[ApiGetResource(
    endpoint: 'posts',
    pagination: 'cursor',
    perPage: 20,
    sortable: ['created_at'],
    searchable: ['title', 'content']
)]
class Post extends Model {}
```

**Uso:** Blog o red social con scroll infinito.

---

### 4. Listado de Solo Lectura (Sin Filtros)

```php
#[ApiGetResource(
    endpoint: 'countries',
    pagination: 'offset',
    perPage: 100
)]
class Country extends Model {}
```

**Uso:** Datos de referencia (países, categorías, etc.).

---

## 🐛 Troubleshooting

### Problema: Ruta no se genera

**Causa:** El modelo no está en `App/Models/` o no se cargó.

**Solución:**
```bash
composer dump-autoload
```

Verificar logs:
```
[ApiGetRouter] Registered 2 GET endpoints for 1 models
[ApiGetRouter] ✓ Registered GET for Product at /api/get/products
```

---

### Problema: Campo no se puede filtrar/ordenar

**Causa:** El campo no está en `filterable` o `sortable`.

**Solución:** Agregar explícitamente:
```php
#[ApiGetResource(
    sortable: ['name', 'price'],
    filterable: ['category']
)]
```

---

### Problema: Búsqueda no funciona

**Causa:** No hay campos en `searchable`.

**Solución:**
```php
#[ApiGetResource(
    searchable: ['name', 'description']
)]
```

---

### Problema: Colisión con ProductsController existente

**Solución:** `#[ApiGetResource]` usa `/api/get/products`, mientras que `ProductsController` usa `/api/products`. **No hay colisión**.

---

## 📚 Documentación Relacionada

- [API_CRUD_RESOURCE_EXAMPLES.md](./API_CRUD_RESOURCE_EXAMPLES.md) - Ejemplos de CRUD completo
- [DYNAMIC_COMPONENTS.md](./DYNAMIC_COMPONENTS.md) - Sistema de componentes dinámicos
- [TODO_TABLE_REFINEMENT.md](../TODO_TABLE_REFINEMENT.md) - Roadmap de TableComponent

---

## 📊 Comparación Visual

```
#[ApiGetResource]                   #[ApiCrudResource]
      ↓                                      ↓
ApiGetRouter                           ApiCrudRouter
      ↓                                      ↓
AbstractGetController              AbstractCrudController
      ↓                                      ↓
GET /api/get/products              GET    /api/products
GET /api/get/products/{id}         GET    /api/products/{id}
                                   POST   /api/products
                                   PUT    /api/products/{id}
                                   DELETE /api/products/{id}
```

---

**Última actualización:** 2025-01-11
