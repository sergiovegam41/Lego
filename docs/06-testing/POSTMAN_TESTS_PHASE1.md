# 🧪 Pruebas Postman - FASE 1: Model-Driven API

## 📋 Setup

**Base URL:** `http://localhost/api`

**Headers comunes:**
```
Content-Type: application/json
Accept: application/json
```

---

## ✅ Test 1: Listar Productos (Paginación Básica)

### Request
```
GET http://localhost/api/products
```

### Response esperado (200 OK)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Producto 1",
      "sku": "SKU001",
      "description": "Descripción...",
      "price": "99.99",
      "stock": 50,
      "category": "electronics",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    // ... más productos
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

### ✓ Verificar:
- `success: true`
- Array `data` con productos
- Objeto `pagination` con metadata

---

## ✅ Test 2: Listar con Paginación Custom

### Request
```
GET http://localhost/api/products?page=2&limit=5
```

### Parámetros:
- `page`: Número de página (default: 1)
- `limit`: Elementos por página (min: 1, max: 100, default: 20)

### ✓ Verificar:
- `pagination.current_page` = 2
- `pagination.per_page` = 5
- `data` tiene 5 elementos (o menos si es última página)

---

## ✅ Test 3: Ordenamiento

### Request
```
GET http://localhost/api/products?sort=price&order=asc
```

### Parámetros:
- `sort`: Campo para ordenar (id, name, price, stock, created_at)
- `order`: Dirección (asc, desc)

### ✓ Verificar:
- Productos ordenados por precio ascendente
- `data[0].price` <= `data[1].price`

---

## ✅ Test 4: Búsqueda Global

### Request
```
GET http://localhost/api/products?search=laptop
```

### ✓ Verificar:
- Solo productos donde `name`, `description` o `sku` contienen "laptop"
- Búsqueda case-insensitive (ILIKE en PostgreSQL)

---

## ✅ Test 5: Filtros

### Request
```
GET http://localhost/api/products?filter[category]=electronics&filter[is_active]=1
```

### Parámetros de filtro:
- `filter[category]`: Filtrar por categoría
- `filter[is_active]`: Filtrar por estado (0=inactivo, 1=activo)

### ✓ Verificar:
- Solo productos de categoría "electronics"
- Solo productos activos (`is_active: true`)

---

## ✅ Test 6: Combinación de Parámetros

### Request
```
GET http://localhost/api/products?search=laptop&filter[category]=electronics&sort=price&order=asc&page=1&limit=10
```

### ✓ Verificar:
- Búsqueda + filtro + ordenamiento + paginación funcionan juntos
- 10 productos por página
- Ordenados por precio
- Solo categoría electronics
- Que contengan "laptop"

---

## ✅ Test 7: Obtener Producto por ID

### Request
```
GET http://localhost/api/products/1
```

### Response esperado (200 OK)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Producto 1",
    "sku": "SKU001",
    "description": "Descripción detallada",
    "price": "99.99",
    "stock": 50,
    "category": "electronics",
    "is_active": true,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### ✓ Verificar:
- `success: true`
- `data` es un objeto (no array)
- Contiene todos los campos del producto

---

## ✅ Test 8: Producto No Encontrado

### Request
```
GET http://localhost/api/products/99999
```

### Response esperado (404 Not Found)
```json
{
  "success": false,
  "message": "Resource not found",
  "error": "No resource found with ID: 99999"
}
```

### ✓ Verificar:
- Status code: 404
- `success: false`
- Mensaje de error claro

---

## ✅ Test 9: Crear Producto

### Request
```
POST http://localhost/api/products
Content-Type: application/json

{
  "name": "Nuevo Producto Test",
  "sku": "TEST001",
  "description": "Producto creado desde Postman",
  "price": 149.99,
  "stock": 100,
  "category": "electronics",
  "is_active": true
}
```

### Response esperado (201 Created)
```json
{
  "success": true,
  "message": "Resource created successfully",
  "data": {
    "id": 152,
    "name": "Nuevo Producto Test",
    "sku": "TEST001",
    "description": "Producto creado desde Postman",
    "price": "149.99",
    "stock": 100,
    "category": "electronics",
    "is_active": true,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

### ✓ Verificar:
- Status code: 201
- `success: true`
- `data.id` existe (auto-generado)
- `created_at` y `updated_at` están presentes

---

## ✅ Test 10: Crear sin Datos (Validación)

### Request
```
POST http://localhost/api/products
Content-Type: application/json

{}
```

### Response esperado (400 Bad Request)
```json
{
  "success": false,
  "message": "No data provided"
}
```

### ✓ Verificar:
- Status code: 400
- `success: false`
- Mensaje de validación

---

## ✅ Test 11: Actualizar Producto

### Request
```
PUT http://localhost/api/products/152
Content-Type: application/json

{
  "name": "Producto Actualizado",
  "price": 199.99,
  "stock": 75
}
```

### Response esperado (200 OK)
```json
{
  "success": true,
  "message": "Resource updated successfully",
  "data": {
    "id": 152,
    "name": "Producto Actualizado",
    "sku": "TEST001",
    "price": "199.99",
    "stock": 75,
    "updated_at": "2024-01-15T10:35:00.000000Z"
    // ... otros campos sin cambios
  }
}
```

### ✓ Verificar:
- Status code: 200
- Campos actualizados tienen nuevos valores
- Campos no enviados mantienen valores originales
- `updated_at` cambió

---

## ✅ Test 12: Actualizar Producto Inexistente

### Request
```
PUT http://localhost/api/products/99999
Content-Type: application/json

{
  "name": "Test"
}
```

### Response esperado (404 Not Found)
```json
{
  "success": false,
  "message": "Resource not found",
  "error": "No resource found with ID: 99999"
}
```

### ✓ Verificar:
- Status code: 404
- `success: false`

---

## ✅ Test 13: Eliminar Producto

### Request
```
DELETE http://localhost/api/products/152
```

### Response esperado (200 OK)
```json
{
  "success": true,
  "message": "Resource deleted successfully"
}
```

### ✓ Verificar:
- Status code: 200
- `success: true`
- Intentar GET del mismo ID retorna 404

---

## ✅ Test 14: Eliminar Producto Inexistente

### Request
```
DELETE http://localhost/api/products/99999
```

### Response esperado (404 Not Found)
```json
{
  "success": false,
  "message": "Resource not found",
  "error": "No resource found with ID: 99999"
}
```

---

## 📊 Checklist de Validación

### Funcionalidad Básica
- [ ] GET /api/products retorna lista con paginación
- [ ] GET /api/products/{id} retorna producto individual
- [ ] POST /api/products crea nuevo producto
- [ ] PUT /api/products/{id} actualiza producto
- [ ] DELETE /api/products/{id} elimina producto

### Paginación
- [ ] `?page=X` cambia la página
- [ ] `?limit=X` cambia elementos por página
- [ ] Metadata de paginación es correcta (current_page, total, etc.)
- [ ] Límite máximo de 100 elementos por página se respeta

### Ordenamiento
- [ ] `?sort=name&order=asc` ordena ascendente
- [ ] `?sort=price&order=desc` ordena descendente
- [ ] Solo campos sortable permitidos funcionan
- [ ] Campos no sortable son ignorados

### Filtros
- [ ] `?filter[category]=X` filtra por categoría
- [ ] `?filter[is_active]=1` filtra por activos
- [ ] Múltiples filtros funcionan simultáneamente
- [ ] Solo campos filterable permitidos funcionan

### Búsqueda
- [ ] `?search=texto` busca en name, description, sku
- [ ] Búsqueda es case-insensitive
- [ ] Búsqueda con múltiples palabras funciona
- [ ] Búsqueda vacía retorna todos

### Validación
- [ ] Crear sin datos retorna 400
- [ ] Actualizar sin datos retorna 400
- [ ] ID inexistente retorna 404
- [ ] Errores de base de datos retornan 422

### Combinaciones
- [ ] Búsqueda + filtros + ordenamiento + paginación funcionan juntos
- [ ] Parámetros inválidos son ignorados sin error
- [ ] Respuestas tienen estructura consistente

---

## 🐛 Problemas Comunes

### Error: "Class 'Core\Attributes\ApiCrudResource' not found"
**Solución:** Verificar que el autoloader reconoce el namespace. Ejecutar `composer dump-autoload`.

### Error: "No routes registered"
**Solución:** Verificar que `ApiCrudRouter::registerRoutes()` se llama en `Routes/Api.php`.

### Error: 500 Internal Server Error
**Solución:** Revisar logs en `logs/error.log` para ver el error específico.

### Paginación no funciona
**Solución:** Verificar que la tabla `products` existe y tiene datos.

### Búsqueda no retorna resultados
**Solución:** Verificar que los campos searchable tienen datos y que la búsqueda usa ILIKE (PostgreSQL).

---

## 📝 Logs Esperados

Al iniciar la aplicación, deberías ver en logs:

```
[ApiCrudRouter] ✓ Registered CRUD for Product at /api/products
[ApiCrudRouter] Registered 5 CRUD endpoints for 1 models
```

Si ves esto, el sistema está funcionando correctamente.

---

## 🎉 Resultado Esperado

Si todos los tests pasan:
- ✅ El decorador `#[ApiCrudResource]` está funcionando
- ✅ Las rutas se generan automáticamente
- ✅ El AbstractCrudController maneja todas las operaciones
- ✅ Paginación, filtros, búsqueda y ordenamiento funcionan
- ✅ El sistema está listo para FASE 2 (TableComponent)

---

**Última actualización:** 2025-01-11
