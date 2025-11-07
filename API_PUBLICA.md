# 🌸 FloraFresh - API Pública

Documentación simple de la API externa para la landing page de FloraFresh.

**Base URL:** `http://your-domain.com/api`

---

## 🎯 Endpoints Disponibles

### 1. Landing Page - Datos Completos
### 2. Catálogo de Flores con Filtros

---

## 📋 1. Landing Page

Obtiene todos los datos necesarios para renderizar la landing page en una sola petición.

**Endpoint:**
```
GET /api/landing
```

**Ejemplo:**
```bash
curl http://your-domain.com/api/landing
```

```javascript
// JavaScript/Fetch
const response = await fetch('http://your-domain.com/api/landing');
const data = await response.json();

// data.data contiene:
// - hero: Banner principal
// - popularProducts: Productos destacados
// - categories: Categorías de flores
// - testimonials: Testimonios de clientes
```

**Respuesta:**
```json
{
  "status": "success",
  "timestamp": "2025-01-05T23:00:00+00:00",
  "data": {
    "hero": {
      "title": "FloraFresh",
      "subtitle": "Flores frescas para toda ocasión",
      "background_image": "https://...",
      "cta": {
        "label": "Ver Catálogo",
        "link": "/tienda"
      }
    },
    "popularProducts": {
      "title": "Nuestros Arreglos Más Populares",
      "products": [
        {
          "id": 1,
          "name": "Rosas Rojas Premium",
          "description": "Hermoso ramo de rosas rojas...",
          "price": 49.99,
          "currency": "USD",
          "image": "https://...",
          "tag": "Más Vendido",
          "available": true
        }
      ]
    },
    "categories": {
      "title": "Explora Nuestras Categorías",
      "items": [
        {
          "id": 1,
          "name": "Rosas",
          "description": "Hermosas rosas para toda ocasión",
          "slug": "rosas"
        }
      ]
    },
    "testimonials": {
      "title": "Lo que dicen nuestros clientes",
      "items": [
        {
          "id": 1,
          "author": "Ana Pérez",
          "message": "¡El ramo fue absolutamente espectacular!"
        }
      ]
    }
  }
}
```

**Uso típico:**
```javascript
async function cargarLandingPage() {
  const res = await fetch('http://your-domain.com/api/landing');
  const { data } = await res.json();

  // Renderizar hero
  document.getElementById('hero-title').textContent = data.hero.title;

  // Renderizar productos
  data.popularProducts.products.forEach(product => {
    // Tu código para mostrar cada producto
  });

  // Renderizar categorías
  data.categories.items.forEach(category => {
    // Tu código para mostrar cada categoría
  });

  // Renderizar testimonios
  data.testimonials.items.forEach(testimonial => {
    // Tu código para mostrar cada testimonio
  });
}
```

---

## 🌺 2. Catálogo de Flores

Busca y filtra flores con paginación automática.

**Endpoint:**
```
GET /api/flowers-catalog
```

### Parámetros de Búsqueda

| Parámetro | Descripción | Ejemplo |
|-----------|-------------|---------|
| `category` | Filtrar por ID de categoría | `?category=2` |
| `q` | Buscar en nombre y descripción | `?q=rosa` |
| `page` | Número de página (default: 1) | `?page=2` |
| `per_page` | Items por página (default: 15) | `?per_page=20` |
| `sort` | Ordenar por: `name`, `price`, `created_at` | `?sort=price` |
| `order` | Orden: `asc` o `desc` (default: asc) | `?order=desc` |

---

### 📌 Ejemplos Prácticos

#### 1️⃣ Todas las flores
```bash
GET /api/flowers-catalog
```
```javascript
const res = await fetch('http://your-domain.com/api/flowers-catalog');
const { data } = await res.json();
console.log(data.flowers); // Array de flores
```

#### 2️⃣ Filtrar por categoría
```bash
GET /api/flowers-catalog?category=2
```
```javascript
// Ver todas las "Rosas" (suponiendo que categoría 2 = Rosas)
const categoryId = 2;
const res = await fetch(`http://your-domain.com/api/flowers-catalog?category=${categoryId}`);
const { data } = await res.json();
```

#### 3️⃣ Buscar por texto
```bash
GET /api/flowers-catalog?q=amor
```
```javascript
// Buscar flores que contengan "amor" en el nombre o descripción
const searchTerm = 'amor';
const res = await fetch(`http://your-domain.com/api/flowers-catalog?q=${searchTerm}`);
const { data } = await res.json();
```

#### 4️⃣ Categoría + Búsqueda
```bash
GET /api/flowers-catalog?category=2&q=roja
```
```javascript
// Buscar "roja" dentro de la categoría "Rosas"
const res = await fetch('http://your-domain.com/api/flowers-catalog?category=2&q=roja');
const { data } = await res.json();
```

#### 5️⃣ Ordenar por precio (más barato primero)
```bash
GET /api/flowers-catalog?sort=price&order=asc
```
```javascript
const res = await fetch('http://your-domain.com/api/flowers-catalog?sort=price&order=asc');
const { data } = await res.json();
```

#### 6️⃣ Ordenar por precio (más caro primero)
```bash
GET /api/flowers-catalog?sort=price&order=desc
```
```javascript
const res = await fetch('http://your-domain.com/api/flowers-catalog?sort=price&order=desc');
const { data } = await res.json();
```

#### 7️⃣ Paginación
```bash
GET /api/flowers-catalog?page=2&per_page=20
```
```javascript
// Ver página 2 con 20 productos por página
const res = await fetch('http://your-domain.com/api/flowers-catalog?page=2&per_page=20');
const { data } = await res.json();

// Saber si hay más páginas
if (data.pagination.has_next_page) {
  console.log('Hay más productos!');
}
```

---

### 📦 Respuesta del Catálogo

```json
{
  "status": "success",
  "timestamp": "2025-01-05T23:00:00+00:00",
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
      "category": null,
      "search_query": null,
      "sort_by": "name",
      "sort_order": "asc"
    }
  }
}
```

---

## 🎨 Componente React de Ejemplo

```jsx
import { useState, useEffect } from 'react';

function CatalogoFlores() {
  const [flores, setFlores] = useState([]);
  const [loading, setLoading] = useState(true);
  const [categoria, setCategoria] = useState('');
  const [busqueda, setBusqueda] = useState('');

  useEffect(() => {
    cargarFlores();
  }, [categoria, busqueda]);

  async function cargarFlores() {
    setLoading(true);

    // Construir URL con parámetros
    const params = new URLSearchParams();
    if (categoria) params.append('category', categoria);
    if (busqueda) params.append('q', busqueda);

    const url = `http://your-domain.com/api/flowers-catalog?${params}`;
    const res = await fetch(url);
    const { data } = await res.json();

    setFlores(data.flowers);
    setLoading(false);
  }

  return (
    <div>
      <input
        type="text"
        placeholder="Buscar flores..."
        value={busqueda}
        onChange={(e) => setBusqueda(e.target.value)}
      />

      <select value={categoria} onChange={(e) => setCategoria(e.target.value)}>
        <option value="">Todas las categorías</option>
        <option value="1">Rosas</option>
        <option value="2">Tulipanes</option>
        <option value="3">Girasoles</option>
      </select>

      {loading ? (
        <p>Cargando...</p>
      ) : (
        <div className="grid">
          {flores.map(flor => (
            <div key={flor.id}>
              <img src={flor.main_image} alt={flor.name} />
              <h3>{flor.name}</h3>
              <p>{flor.description}</p>
              <span>${flor.price}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
```

---

## 🔍 Búsqueda en Tiempo Real (JavaScript Vanilla)

```javascript
const searchInput = document.getElementById('search');
const resultsContainer = document.getElementById('results');

// Debounce para no hacer peticiones en cada tecla
let timeout;
searchInput.addEventListener('input', (e) => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    buscarFlores(e.target.value);
  }, 500); // Esperar 500ms después de que el usuario deja de escribir
});

async function buscarFlores(query) {
  if (!query) {
    resultsContainer.innerHTML = '';
    return;
  }

  const res = await fetch(`http://your-domain.com/api/flowers-catalog?q=${encodeURIComponent(query)}`);
  const { data } = await res.json();

  // Renderizar resultados
  resultsContainer.innerHTML = data.flowers.map(flor => `
    <div class="flower-card">
      <img src="${flor.main_image}" alt="${flor.name}">
      <h3>${flor.name}</h3>
      <p>${flor.price} ${flor.currency}</p>
    </div>
  `).join('');
}
```

---

## 🌐 CORS (Acceso desde otros dominios)

Si tu frontend está en un dominio diferente al backend, el CORS ya está configurado automáticamente para permitir peticiones desde cualquier origen.

```javascript
// Esto funcionará desde cualquier dominio
fetch('http://api.florafresh.com/api/landing')
  .then(res => res.json())
  .then(data => console.log(data));
```

---

## ⚠️ Notas Importantes

1. **Solo flores activas**: La API solo retorna flores con `is_active = true`
2. **Paginación**: Por defecto 15 items por página, máximo 50
3. **Búsqueda**: No distingue mayúsculas/minúsculas
4. **Imágenes**: Si una flor no tiene imágenes, `main_image` será `null`
5. **Categoría**: Si una flor no tiene categoría, `category` será `null`

---

## 🚀 Quick Start

```javascript
// 1. Cargar datos de la landing
async function init() {
  // Landing page
  const landing = await fetch('http://your-domain.com/api/landing').then(r => r.json());
  console.log('Hero:', landing.data.hero);
  console.log('Productos:', landing.data.popularProducts.products);

  // Catálogo de flores
  const catalogo = await fetch('http://your-domain.com/api/flowers-catalog').then(r => r.json());
  console.log('Flores:', catalogo.data.flowers);

  // Filtrar por categoría
  const rosas = await fetch('http://your-domain.com/api/flowers-catalog?category=2').then(r => r.json());
  console.log('Rosas:', rosas.data.flowers);

  // Buscar
  const resultados = await fetch('http://your-domain.com/api/flowers-catalog?q=amor').then(r => r.json());
  console.log('Búsqueda:', resultados.data.flowers);
}

init();
```

---

## 📚 Archivos de Referencia

- **Rutas Landing**: `Core/Routes/LandingRoutes.php`
- **Rutas Catálogo**: `Core/Routes/FlowersPublicRoutes.php`
- **Configuración CORS**: `Routes/Api.php` (líneas 67-85)
