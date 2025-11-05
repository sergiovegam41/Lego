# 🌸 Landing Page API - Flora Fresh

## 📋 Descripción

API endpoint agregado que retorna **todos los datos necesarios para la landing page** en una sola request:
- Hero/Banner principal
- Productos populares (featured products)
- Categorías
- Testimonios de clientes

## 🚀 Endpoint

```
GET /api/landing
```

**Base URL:** `http://localhost:8080` (desarrollo) o tu dominio en producción

**URL completa:** `http://localhost:8080/api/landing`

## 📦 Estructura de Respuesta

```json
{
  "status": "success",
  "timestamp": "2025-01-05T23:00:00+00:00",
  "data": {
    "hero": { ... },
    "popularProducts": { ... },
    "categories": { ... },
    "testimonials": { ... }
  }
}
```

## 🎯 Datos Incluidos

### 1. Hero Section
Información del banner principal de la landing page.

**Fuente:** Tabla `heroes` (modelo `Hero`)

```json
"hero": {
  "title": "Welcome to Flora Fresh",
  "subtitle": "TIME TO BLOSSOM",
  "background_image": "https://cdn.example.com/landing/hero-flora.jpg",
  "cta": {
    "label": "Ver Colección",
    "link": "/tienda"
  }
}
```

**Lógica:**
- Retorna el hero **activo** (`is_active = true`)
- Con el `sort_order` más bajo (mayor prioridad)

### 2. Popular Products
Los 6 productos más populares o más vendidos.

**Fuente:** Tabla `featured_products` con relación a `flowers` e `images`

```json
"popularProducts": {
  "title": "Nuestros Arreglos Más Populares",
  "products": [
    {
      "id": "prod-001",
      "name": "Amor Eterno",
      "description": "Clásico ramo de rosas rojas premium.",
      "price": 49.99,
      "currency": "USD",
      "image": "https://storage.example.com/flowers/amor-eterno.jpg",
      "tag": "Más Vendido",  // Solo si tag = "best-seller"
      "available": true
    }
  ]
}
```

**Lógica:**
- Solo productos con `is_active = true`
- Solo featured products con tags: `"most-popular"` o `"best-seller"`
- Ordenados por `sort_order` ascendente
- Límite de 6 productos
- Incluye primera imagen del producto

**Tag mapping:**
- `"best-seller"` → Muestra como `"Más Vendido"`
- `"most-popular"` → No muestra tag (null)

### 3. Categories
Todas las categorías activas de productos.

**Fuente:** Tabla `categories`

```json
"categories": {
  "title": "Explora Nuestras Categorías",
  "items": [
    {
      "id": "cat-001",
      "name": "Rosas",
      "description": "Hermosas rosas para toda ocasión",
      "slug": "rosas"
    }
  ]
}
```

**Lógica:**
- Solo categorías con `is_active = true`
- Ordenadas alfabéticamente por nombre
- Slug generado automáticamente (lowercase, espacios → guiones)

### 4. Testimonials
Los 3 testimonios más recientes.

**Fuente:** Tabla `testimonials`

```json
"testimonials": {
  "title": "Lo que dicen nuestros clientes",
  "items": [
    {
      "id": "t-001",
      "author": "Ana Pérez",
      "message": "¡El ramo fue absolutamente espectacular! ..."
    }
  ]
}
```

**Lógica:**
- Solo testimonios con `is_active = true`
- Ordenados por fecha de creación (más recientes primero)
- Límite de 3 testimonios

## 🗄️ Requisitos de Base de Datos

### Tablas Necesarias

1. **`heroes`** (nueva)
   ```sql
   - id
   - title
   - subtitle
   - background_image
   - cta_label
   - cta_link
   - sort_order
   - is_active
   - created_at, updated_at
   ```

2. **`featured_products`** (existente)
   - Relacionada con `flowers`
   - Tags: `"most-popular"`, `"best-seller"`, etc.

3. **`flowers`** (existente)
   - Productos base

4. **`flower_images`** / `entity_file_associations`** (existente)
   - Imágenes de productos

5. **`categories`** (existente)
   - Categorías de productos

6. **`testimonials`** (existente)
   - Testimonios de clientes

### Ejecutar Migration

```bash
php public/index.php migrate
```

Esto creará la tabla `heroes` e insertará un hero por defecto.

## 🔧 Configuración Inicial

### 1. Crear Hero
Puedes insertar heroes manualmente o crear un CRUD admin para gestionarlos:

```sql
INSERT INTO heroes (title, subtitle, background_image, cta_label, cta_link, sort_order, is_active, created_at, updated_at)
VALUES ('Welcome to Flora Fresh', 'TIME TO BLOSSOM', 'https://your-cdn.com/hero.jpg', 'Ver Colección', '/tienda', 0, 1, NOW(), NOW());
```

### 2. Configurar Featured Products
Los productos deben estar marcados como destacados con los tags correctos:

```sql
-- Marcar productos como "Más Vendido"
UPDATE featured_products SET tag = 'best-seller', is_active = 1 WHERE product_id IN (1, 2, 3);

-- Marcar productos como populares
UPDATE featured_products SET tag = 'most-popular', is_active = 1 WHERE product_id IN (4, 5, 6);
```

### 3. Verificar Imágenes
Asegúrate de que los productos tengan imágenes asociadas en `flower_images` o `entity_file_associations`.

## 📝 Ejemplo de Uso

### JavaScript/Fetch

```javascript
async function loadLandingPage() {
  try {
    const response = await fetch('http://localhost:8080/api/landing');
    const data = await response.json();

    if (data.status === 'success') {
      // Hero section
      const hero = data.data.hero;
      document.getElementById('hero-title').textContent = hero.title;
      document.getElementById('hero-subtitle').textContent = hero.subtitle;

      // Popular products
      const products = data.data.popularProducts.products;
      products.forEach(product => {
        // Renderizar producto
      });

      // Categories
      const categories = data.data.categories.items;
      // Renderizar categorías

      // Testimonials
      const testimonials = data.data.testimonials.items;
      // Renderizar testimonios
    }
  } catch (error) {
    console.error('Error loading landing page:', error);
  }
}
```

### cURL

```bash
curl http://localhost:8080/api/landing
```

### Axios

```javascript
import axios from 'axios';

const { data } = await axios.get('http://localhost:8080/api/landing');
console.log(data.data.popularProducts);
```

## ⚠️ Notas Importantes

1. **Datos Vacíos:** Si alguna sección no tiene datos (ej: no hay heroes activos), esa sección será `null` o un array vacío según corresponda.

2. **Performance:** Esta API hace 4 queries a la base de datos. Si tienes muchos datos, considera agregar caché.

3. **Imágenes:** Las URLs de imágenes vienen directamente de la base de datos. Asegúrate de que sean URLs válidas y accesibles.

4. **Currency:** Actualmente hardcodeado como `"USD"`. Si necesitas múltiples monedas, agregar campo `currency` a la tabla `flowers`.

5. **Tags Adicionales:** Si agregas más tags a featured products, actualiza la query en `LandingRoutes.php` línea 37.

## 🔄 Actualización de Datos

Los datos se obtienen en **tiempo real** de la base de datos en cada request. No hay caché por defecto.

Para actualizar los datos que muestra la API:
- **Hero:** Editar/crear registros en tabla `heroes`
- **Products:** Editar registros en `featured_products` y `flowers`
- **Categories:** Editar registros en `categories`
- **Testimonials:** Editar registros en `testimonials`

## 📚 Referencias

- **Modelo Hero:** `App/Models/Hero.php`
- **Rutas API:** `Core/Routes/LandingRoutes.php`
- **Migration:** `database/migrations/2025_01_05_000002_create_heroes_table.php`

## 🐛 Debugging

Si el endpoint retorna error 500:

1. Verificar logs del servidor
2. Verificar que la migration de heroes se haya ejecutado
3. Verificar que existan productos con featured_products activos
4. Verificar relaciones entre tablas (foreign keys)

```bash
# Ver logs de PHP
tail -f /var/log/php/error.log

# O verificar en navegador
# Network tab → Response → Ver error message
```
