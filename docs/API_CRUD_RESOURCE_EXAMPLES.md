# 📖 Ejemplos de #[ApiCrudResource] - Personalización de Rutas

## 🎯 Sintaxis Básica

```php
use Core\Attributes\ApiCrudResource;

#[ApiCrudResource(
    endpoint: 'ruta/custom',        // Opcional: SIN /api (se agrega automáticamente)
    pagination: 'offset',           // offset | cursor | page
    perPage: 20,                    // 1-100
    sortable: ['campo1', 'campo2'], // Campos ordenables
    filterable: ['campo3'],         // Campos filtrables
    searchable: ['campo4', 'campo5'] // Campos buscables
)]
class MiModelo extends Model {}
```

**⚠️ IMPORTANTE:** El prefijo `/api` se agrega **automáticamente**. NO lo incluyas en el endpoint.

---

## 📋 Ejemplos de Uso

### Ejemplo 1: Auto-generación (Sin endpoint)

```php
#[ApiCrudResource]
class Product extends Model {}
```

**Rutas generadas:**
```
GET    /api/products
GET    /api/products/{id}
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}
```

**Lógica:**
- Toma el nombre del modelo (`Product`)
- Lo pluraliza (`Products`)
- Lo convierte a kebab-case (`products`)
- Agrega prefijo `/api/`

---

### Ejemplo 2: Ruta Personalizada Simple

```php
#[ApiCrudResource(
    endpoint: 'catalog/items'  // SIN /api
)]
class Product extends Model {}
```

**Rutas generadas:**
```
GET    /api/catalog/items
GET    /api/catalog/items/{id}
POST   /api/catalog/items
PUT    /api/catalog/items/{id}
DELETE /api/catalog/items/{id}
```

**Uso:** Cuando quieres organizar tus endpoints por categorías.

---

### Ejemplo 3: Versioning de API

```php
#[ApiCrudResource(
    endpoint: 'v2/products'  // SIN /api
)]
class Product extends Model {}
```

**Rutas generadas:**
```
GET    /api/v2/products
GET    /api/v2/products/{id}
POST   /api/v2/products
PUT    /api/v2/products/{id}
DELETE /api/v2/products/{id}
```

**Uso:** Para mantener múltiples versiones de tu API.

---

### Ejemplo 4: Recursos Anidados

```php
#[ApiCrudResource(
    endpoint: 'users/profile'  // SIN /api
)]
class UserProfile extends Model {}
```

**Rutas generadas:**
```
GET    /api/users/profile
GET    /api/users/profile/{id}
POST   /api/users/profile
PUT    /api/users/profile/{id}
DELETE /api/users/profile/{id}
```

**Uso:** Para recursos que conceptualmente pertenecen a otro recurso.

---

### Ejemplo 5: Nombres Descriptivos

```php
#[ApiCrudResource(
    endpoint: 'inventory/stock-items'  // SIN /api
)]
class InventoryItem extends Model {}
```

**Rutas generadas:**
```
GET    /api/inventory/stock-items
...
```

**Uso:** Cuando el nombre auto-generado no es lo suficientemente descriptivo.

---

### Ejemplo 6: Organización por Módulos

```php
// App/Models/Blog/Post.php
#[ApiCrudResource(
    endpoint: 'blog/posts'  // SIN /api
)]
class Post extends Model {}

// App/Models/Blog/Comment.php
#[ApiCrudResource(
    endpoint: 'blog/comments'  // SIN /api
)]
class Comment extends Model {}

// App/Models/Blog/Category.php
#[ApiCrudResource(
    endpoint: 'blog/categories'  // SIN /api
)]
class Category extends Model {}
```

**Estructura generada:**
```
/api/blog/posts
/api/blog/comments
/api/blog/categories
```

**Uso:** Organizar endpoints relacionados bajo un prefijo común.

---

### Ejemplo 7: Configuración Completa

```php
#[ApiCrudResource(
    endpoint: 'ecommerce/products',  // SIN /api
    pagination: 'cursor',
    perPage: 50,
    sortable: ['name', 'price', 'rating', 'created_at'],
    filterable: ['category_id', 'brand_id', 'is_featured', 'in_stock'],
    searchable: ['name', 'description', 'sku', 'tags']
)]
class Product extends Model {}
```

**Características:**
- Ruta custom: `/api/ecommerce/products`
- Paginación cursor-based (para feeds infinitos)
- 50 elementos por página
- Múltiples campos ordenables
- Múltiples filtros disponibles
- Búsqueda en 4 campos

**Ejemplo de query:**
```
GET /api/ecommerce/products?search=laptop&filter[category_id]=5&filter[in_stock]=1&sort=price&order=asc&cursor=xxx&limit=20
```

---

## 🎨 Convenciones de Nombres

### Nombres de Modelos → Rutas Auto-generadas

| Modelo | Ruta Auto-generada |
|--------|-------------------|
| `Product` | `/api/products` |
| `User` | `/api/users` |
| `Category` | `/api/categories` |
| `OrderItem` | `/api/order-items` |
| `ProductImage` | `/api/product-images` |
| `ShoppingCart` | `/api/shopping-carts` |

### Pluralización Especial

```php
// Irregulares manejados automáticamente
Person → /api/people
Child → /api/children
Man → /api/men
Woman → /api/women
```

---

## 🔧 Casos de Uso Avanzados

### Múltiples Modelos, Mismo Endpoint (NO RECOMENDADO)

```php
// ❌ NO HACER ESTO
#[ApiCrudResource(endpoint: '/api/items')]
class Product extends Model {}

#[ApiCrudResource(endpoint: '/api/items')]
class Service extends Model {}
```

**Problema:** Colisión de rutas. El último modelo registrado sobrescribe al primero.

**Solución:** Usar endpoints únicos.

---

### Prefijos por Entorno

```php
#[ApiCrudResource(
    endpoint: getenv('API_PREFIX') . '/products'
)]
class Product extends Model {}
```

**NO SOPORTADO actualmente** - Los atributos PHP no permiten expresiones dinámicas.

**Alternativa:** Usar un controlador custom con lógica de prefijos.

---

### Rutas con Idioma

```php
#[ApiCrudResource(
    endpoint: '/api/es/productos'  // Español
)]
class ProductoES extends Model {}

#[ApiCrudResource(
    endpoint: '/api/en/products'   // Inglés
)]
class ProductEN extends Model {}
```

**Uso:** APIs multiidioma con modelos separados.

---

## 📊 Comparación: Con vs Sin endpoint

### Sin endpoint (Auto-generado)

```php
#[ApiCrudResource(
    pagination: 'offset',
    perPage: 20
)]
class ProductReview extends Model {}
```

**Resultado:** `/api/product-reviews`

**Ventajas:**
- ✅ Menos código
- ✅ Convención consistente
- ✅ Fácil de predecir

**Desventajas:**
- ⚠️ No controlas el nombre exacto
- ⚠️ Depende de la lógica de pluralización

---

### Con endpoint (Explícito)

```php
#[ApiCrudResource(
    endpoint: '/api/reviews',
    pagination: 'offset',
    perPage: 20
)]
class ProductReview extends Model {}
```

**Resultado:** `/api/reviews`

**Ventajas:**
- ✅ Control total sobre la ruta
- ✅ Rutas más cortas/simples
- ✅ API más limpia

**Desventajas:**
- ⚠️ Más código
- ⚠️ Debes mantener consistencia manualmente

---

## 🎯 Recomendaciones

### 1. Usa Auto-generación por Defecto

```php
#[ApiCrudResource]
class Product extends Model {}
```

Solo personaliza cuando sea necesario.

---

### 2. Mantén Consistencia

Si usas prefijos como `v1/`, úsalos para **todos** los modelos.

```php
// ✅ BIEN
#[ApiCrudResource(endpoint: 'v1/products')]
#[ApiCrudResource(endpoint: 'v1/users')]
#[ApiCrudResource(endpoint: 'v1/orders')]

// ❌ MAL (inconsistente)
#[ApiCrudResource(endpoint: 'v1/products')]
#[ApiCrudResource] // Auto-genera /api/users (sin v1)
```

---

### 3. Organiza por Módulos

```php
// E-commerce
#[ApiCrudResource(endpoint: 'shop/products')]
#[ApiCrudResource(endpoint: 'shop/orders')]

// Blog
#[ApiCrudResource(endpoint: 'blog/posts')]
#[ApiCrudResource(endpoint: 'blog/comments')]

// Admin
#[ApiCrudResource(endpoint: 'admin/users')]
#[ApiCrudResource(endpoint: 'admin/settings')]
```

---

### 4. Evita Rutas Muy Largas

```php
// ❌ Demasiado largo
#[ApiCrudResource(endpoint: 'v1/ecommerce/catalog/products/items')]

// ✅ Mejor
#[ApiCrudResource(endpoint: 'v1/products')]
```

---

### 5. Usa Singular para Recursos Únicos

```php
// Para recursos que no se listan (solo 1 instancia)
#[ApiCrudResource(endpoint: 'user/profile')]
class UserProfile extends Model {}
```

---

## 🐛 Troubleshooting

### Problema: Ruta no se genera

**Causa:** El modelo no está en `App/Models/` o el archivo no se carga.

**Solución:**
1. Verificar que el modelo está en `App/Models/`
2. Ejecutar `composer dump-autoload`
3. Verificar logs para ver si se registró

---

### Problema: Colisión de rutas

**Causa:** Dos modelos usan el mismo endpoint.

**Solución:** Cambiar el `endpoint` de uno de los modelos.

---

### Problema: Ruta no sigue convención

**Causa:** La auto-generación no funciona como esperas.

**Solución:** Especifica `endpoint` explícitamente.

---

## 📝 Cheat Sheet

```php
// Mínimo (auto-genera ruta)
#[ApiCrudResource]

// Con ruta custom (SIN /api)
#[ApiCrudResource(endpoint: 'custom/path')]

// Completo
#[ApiCrudResource(
    endpoint: 'v1/resources',  // SIN /api - se agrega automáticamente
    pagination: 'offset',
    perPage: 20,
    sortable: ['field1', 'field2'],
    filterable: ['field3'],
    searchable: ['field4', 'field5']
)]
```

**⚠️ RECORDATORIO:** NUNCA incluyas `/api` en el endpoint. Se agrega automáticamente.

---

**Última actualización:** 2025-01-11
