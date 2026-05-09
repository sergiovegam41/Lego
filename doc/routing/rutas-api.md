# Rutas de API

Las rutas de API retornan JSON. Son el backend del SPA — los componentes JavaScript las llaman para leer y escribir datos.

Relacionado: [[routing/tres-capas]] · [[api/atributos]] · [[api/crud-automatico]] · [[api/controladores]] · [[autenticacion/sistema-auth]]

Código: `Routes/Api.php`

---

## Categorías de Rutas

### 1. Menú

| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET` | `/api/menu/search?q=texto` | Busca items por texto |
| `GET` | `/api/menu/structure` | Árbol completo de items visibles |
| `GET` | `/api/menu/system-items` | Items ocultos para dropdown de config |
| `GET` | `/api/menu/item-hierarchy` | Ancestros + item + descendientes |

### 2. Controladores Auto-Registrados

Clases con `#[ApiRoutes]` se registran automáticamente:

```php
#[ApiRoutes('/reportes', preset: 'custom', actions: [
    'generar' => ['POST'],
    'descargar' => ['GET'],
])]
class ReportesController extends CoreController { }
// → POST /api/reportes/generar
// → GET  /api/reportes/descargar
```

### 3. CRUD Automático desde Modelos

Modelos con `#[ApiCrudResource]` generan 5 endpoints:

```
GET    /api/productos        → lista paginada
GET    /api/productos/{id}   → un registro
POST   /api/productos        → crear
PUT    /api/productos/{id}   → actualizar
DELETE /api/productos/{id}   → eliminar
```

Ver [[api/crud-automatico]].

### 4. GET Automático desde Modelos

Modelos con `#[ApiGetResource]` generan 2 endpoints de solo lectura:

```
GET /api/get/productos        → lista paginada (para TableComponent)
GET /api/get/productos/{id}   → un registro
```

Ver [[api/get-automatico]].

### 5. Autenticación

```
POST /auth/{grupo}/login
POST /auth/{grupo}/logout
POST /auth/{grupo}/refresh_token
GET  /auth/{grupo}/me
```

Los grupos son independientes: `admin`, `api`, y cualquier grupo personalizado. Ver [[autenticacion/grupos-auth]].

### 6. Render de Componentes

| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET` | `/api/components/render` | Renderiza un componente via JS |
| `POST` | `/api/components/batch` | Renderiza múltiples componentes |
| `GET` | `/api/components/list` | Lista de componentes registrados (debug) |

## Formato de Respuesta

Todas las rutas de API retornan JSON con estructura consistente:

```json
// Éxito
{ "data": { ... }, "meta": { "total": 100, "page": 1 } }

// Error
{ "error": "Mensaje de error", "code": 422 }
```

## Autenticación JWT

Todas las rutas de API requieren JWT por defecto, excepto las de autenticación (`/auth/*`). El token va en el header:

```
Authorization: Bearer eyJ...
```

Ver [[autenticacion/jwt]].

## Visión

> Las rutas de API tendrán soporte para webhooks outgoing: cuando un recurso cambia, Lego puede notificar a sistemas externos automáticamente. También se añadirá rate limiting por grupo de autenticación y versionado de API (`/api/v2/`) sin romper las rutas existentes.
