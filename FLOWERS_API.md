# 🌸 Flowers Public API - Flora Fresh

## 📋 Descripción

API pública para consultar, filtrar y buscar flores con paginación. Perfecta para la tienda pública de la landing page.

## 🚀 Endpoints

### 1. Buscar/Filtrar Flores

```
GET /api/flowers-catalog
```

**Descripción:** Busca y filtra flores con paginación de 15 items por página (configurable).

#### Query Parameters

| Parámetro | Tipo | Requerido | Default | Descripción |
|-----------|------|-----------|---------|-------------|
| `category` | integer | No | null | ID de categoría para filtrar |
| `q` | string | No | null | Búsqueda general en nombre y descripción |
| `page` | integer | No | 1 | Número de página |
| `per_page` | integer | No | 15 | Items por página (máx: 50) |
| `sort` | string | No | name | Campo de ordenamiento: `name`, `price`, `created_at` |
| `order` | string | No | asc | Orden: `asc` o `desc` |

#### Ejemplos de Uso

**1. Todas las flores (primera página):**
```bash
curl http://localhost:8080/api/flowers-catalog
```

**2. Filtrar por categoría:**
```bash
curl http://localhost:8080/api/flowers-catalog?category=2
```

**3. Buscar "rosa" en nombre o descripción:**
```bash
curl http://localhost:8080/api/flowers-catalog?q=rosa
```

**4. Categoría + búsqueda:**
```bash
curl http://localhost:8080/api/flowers-catalog?category=2&q=roja
```

**5. Paginación personalizada (20 items por página):**
```bash
curl http://localhost:8080/api/flowers-catalog?page=2&per_page=20
```

**6. Ordenar por precio descendente:**
```bash
curl http://localhost:8080/api/flowers-catalog?sort=price&order=desc
```

**7. Combinación completa:**
```bash
curl "http://localhost:8080/api/flowers-catalog?category=3&q=amor&page=1&per_page=10&sort=price&order=asc"
```

#### Respuesta Exitosa

```json
{
  "status": "success",
  "timestamp": "2025-11-05T22:30:00+00:00",
  "data": {
    "flowers": [
      {
        "id": 1,
        "name": "Rosas Rojas Premium",
        "description": "Hermoso ramo de rosas rojas para toda ocasión",
        "price": 49.99,
        "currency": "USD",
        "category": {
          "id": 2,
          "name": "Rosas"
        },
        "images": [
          {
            "id": 10,
            "url": "https://storage.example.com/flowers/rosa-1.jpg",
            "thumbnail": "https://storage.example.com/flowers/rosa-1-thumb.jpg"
          }
        ],
        "main_image": "https://storage.example.com/flowers/rosa-1.jpg",
        "available": true
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total_items": 42,
      "total_pages": 3,
      "has_next_page": true,
      "has_prev_page": false,
      "next_page": 2,
      "prev_page": null
    },
    "filters": {
      "category": 2,
      "search_query": "roja",
      "sort_by": "name",
      "sort_order": "asc"
    }
  }
}
```

---

### 2. Obtener Flor por ID

```
GET /api/flower-detail/{id}
```

**Descripción:** Obtiene los detalles completos de una flor específica.

#### Ejemplo

```bash
curl http://localhost:8080/api/flower-detail/5
```

#### Respuesta Exitosa

```json
{
  "status": "success",
  "timestamp": "2025-11-05T22:30:00+00:00",
  "data": {
    "id": 5,
    "name": "Girasoles Alegres",
    "description": "Ramo de girasoles frescos que iluminarán cualquier espacio",
    "price": 35.99,
    "currency": "USD",
    "category": {
      "id": 3,
      "name": "Girasoles",
      "description": "Flores alegres y vibrantes"
    },
    "images": [
      {
        "id": 15,
        "url": "https://storage.example.com/flowers/girasol-1.jpg",
        "thumbnail": "https://storage.example.com/flowers/girasol-1-thumb.jpg"
      },
      {
        "id": 16,
        "url": "https://storage.example.com/flowers/girasol-2.jpg",
        "thumbnail": "https://storage.example.com/flowers/girasol-2-thumb.jpg"
      }
    ],
    "main_image": "https://storage.example.com/flowers/girasol-1.jpg",
    "available": true,
    "created_at": "2025-10-15T10:30:00Z",
    "updated_at": "2025-11-01T14:20:00Z"
  }
}
```

#### Respuesta de Error (404)

```json
{
  "status": "error",
  "message": "Flower not found"
}
```

---

## 🎨 Casos de Uso Típicos

### 1. Landing Page - Todas las flores
```javascript
const response = await fetch('http://localhost:8080/api/flowers-catalog');
const { data } = await response.json();
const flowers = data.flowers;
```

### 2. Página de Categoría
```javascript
const categoryId = 2; // Rosas
const response = await fetch(`http://localhost:8080/api/flowers-catalog?category=${categoryId}`);
const { data } = await response.json();
```

### 3. Búsqueda en tiempo real
```javascript
const searchQuery = document.getElementById('search').value;
const response = await fetch(`http://localhost:8080/api/flowers-catalog?q=${encodeURIComponent(searchQuery)}`);
const { data } = await response.json();
```

### 4. Paginación con "Load More"
```javascript
let currentPage = 1;

async function loadMore() {
  currentPage++;
  const response = await fetch(`http://localhost:8080/api/flowers-catalog?page=${currentPage}`);
  const { data } = await response.json();

  if (data.pagination.has_next_page) {
    // Mostrar botón "Cargar más"
  } else {
    // Ocultar botón "Cargar más"
  }
}
```

### 5. Filtro combinado (Categoría + Búsqueda + Ordenamiento)
```javascript
const filters = {
  category: 2,
  search: 'roja',
  sort: 'price',
  order: 'asc'
};

const params = new URLSearchParams();
if (filters.category) params.append('category', filters.category);
if (filters.search) params.append('q', filters.search);
params.append('sort', filters.sort);
params.append('order', filters.order);

const response = await fetch(`http://localhost:8080/api/flowers-catalog?${params}`);
```

### 6. Detalle de Producto
```javascript
const flowerId = 5;
const response = await fetch(`http://localhost:8080/api/flower-detail/${flowerId}`);
const { data } = await response.json();

// Renderizar galería de imágenes
data.images.forEach(image => {
  // Mostrar imagen
});
```

---

## 🔍 Búsqueda (Parámetro `q`)

La búsqueda es **case-insensitive** y busca en:
- **Nombre** de la flor
- **Descripción** de la flor

Ejemplos de búsquedas:
- `q=rosa` → Encuentra "Rosas Rojas", "Rosa Blanca", "Ramo de rosas"
- `q=amor` → Encuentra "Amor Eterno", "Ramo para el día del amor"
- `q=roja` → Encuentra "Rosas Rojas", "Tulipán Rojo"

---

## 📊 Paginación

### Metadata Incluida

La respuesta siempre incluye información completa de paginación:

```json
"pagination": {
  "current_page": 2,        // Página actual
  "per_page": 15,           // Items por página
  "total_items": 42,        // Total de items en la BD
  "total_pages": 3,         // Total de páginas
  "has_next_page": true,    // ¿Hay página siguiente?
  "has_prev_page": true,    // ¿Hay página anterior?
  "next_page": 3,           // Número de página siguiente
  "prev_page": 1            // Número de página anterior
}
```

### Navegación de Páginas

```javascript
// Página anterior
if (data.pagination.has_prev_page) {
  const prevPage = data.pagination.prev_page;
  fetch(`/api/flowers/search?page=${prevPage}`);
}

// Página siguiente
if (data.pagination.has_next_page) {
  const nextPage = data.pagination.next_page;
  fetch(`/api/flowers/search?page=${nextPage}`);
}
```

---

## ⚙️ Ordenamiento

### Campos Disponibles

| Campo | Descripción |
|-------|-------------|
| `name` | Nombre alfabético (default) |
| `price` | Precio numérico |
| `created_at` | Fecha de creación |

### Ejemplos

```bash
# Más baratos primero
curl "http://localhost:8080/api/flowers-catalog?sort=price&order=asc"

# Más caros primero
curl "http://localhost:8080/api/flowers-catalog?sort=price&order=desc"

# Más recientes primero
curl "http://localhost:8080/api/flowers-catalog?sort=created_at&order=desc"

# Alfabético A-Z
curl "http://localhost:8080/api/flowers-catalog?sort=name&order=asc"
```

---

## 🎯 Filtros Activos

La respuesta siempre incluye los filtros aplicados:

```json
"filters": {
  "category": 2,              // null si no hay filtro de categoría
  "search_query": "rosa",     // null si no hay búsqueda
  "sort_by": "price",         // Campo de ordenamiento actual
  "sort_order": "asc"         // Orden actual
}
```

Esto es útil para mantener el estado de los filtros en la UI.

---

## ⚠️ Notas Importantes

1. **Solo flores activas:** La API solo retorna flores con `is_active = true`
2. **Límite por página:** Máximo 50 items por página para prevenir sobrecarga
3. **Case-insensitive:** Búsquedas no distinguen mayúsculas/minúsculas
4. **Imágenes:** Si una flor no tiene imágenes, `images` será array vacío y `main_image` será `null`
5. **Categoría null:** Si una flor no tiene categoría, el campo `category` será `null`

---

## 🔄 CORS

Si necesitas acceder desde un dominio diferente, agrega headers CORS:

```php
// En Api.php o en FlowersPublicRoutes.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
```

---

## 🐛 Manejo de Errores

### Error 500 - Internal Server Error

```json
{
  "status": "error",
  "message": "Error searching flowers",
  "error": "Detailed error message"
}
```

**Causas comunes:**
- Base de datos no conectada
- Tabla flowers no existe
- Error en relaciones (category, images)

### Error 404 - Not Found (solo en `/flowers/{id}`)

```json
{
  "status": "error",
  "message": "Flower not found"
}
```

**Causas:**
- ID no existe
- Flor está inactiva (`is_active = false`)

### Error 400 - Bad Request (solo en `/flower-detail/{id}`)

```json
{
  "status": "error",
  "message": "Invalid flower ID"
}
```

**Causas:**
- ID no es numérico

---

## 📚 Referencias

- **Archivo de rutas:** `Core/Routes/FlowersPublicRoutes.php`
- **Modelo:** `App/Models/Flower.php`
- **Documentación API general:** `LANDING_API.md`
