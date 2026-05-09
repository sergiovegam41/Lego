# Redis

Redis es la capa de caché y almacenamiento clave-valor de Lego. Se usa para sesiones, caché de queries y datos efímeros de alta velocidad.

Relacionado: [[infraestructura/docker]] · [[autenticacion/sistema-auth]]

---

## Para Qué se Usa

| Uso | Por qué Redis |
|-----|---------------|
| Sesiones de usuario | Más rápido que BD, expira automáticamente |
| Caché de queries pesadas | Evita golpear PostgreSQL |
| Rate limiting | Contadores atómicos de alto rendimiento |
| Bloqueos distribuidos | Coordinación entre workers |

## Configuración

Variables en `.env`:

```
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_USER=
REDIS_PASSWORD=
```

En Docker, el host es `redis` (nombre del servicio en `docker-compose.yml`).

## Cliente PHP

Lego usa `predis/predis`:

```php
use Predis\Client;

$redis = new Client([
    'scheme' => 'tcp',
    'host'   => env('REDIS_HOST'),
    'port'   => env('REDIS_PORT'),
    'password' => env('REDIS_PASSWORD'),
]);
```

## Operaciones Comunes

```php
// SET con expiración (TTL en segundos)
$redis->setex('clave', 3600, 'valor');

// GET
$valor = $redis->get('clave');

// DELETE
$redis->del('clave');

// Incrementar contador atómicamente
$redis->incr('rate_limit:user:123');

// Verificar si existe
if ($redis->exists('clave')) { ... }

// Listar claves por patrón (CUIDADO en producción)
$claves = $redis->keys('session:*');
```

## Convenciones de Nombres

```
{categoria}:{subcategoria}:{id}
```

Ejemplos:

| Clave | Contenido |
|-------|-----------|
| `session:user:123` | Datos de sesión del usuario 123 |
| `cache:menu:structure` | Árbol del menú cacheado |
| `rate_limit:ip:192.168.1.1` | Contador de requests por IP |
| `lock:report:generation:5` | Lock para generar el reporte 5 |

## TTL Recomendados

| Tipo de dato | TTL |
|-------------|-----|
| Sesión activa | 24 horas (renovable) |
| Caché de query | 5-15 minutos |
| Rate limit | 1 minuto a 1 hora |
| Lock | 30 segundos |

## Invalidación de Caché

Cuando un dato cacheado cambia, el código que lo modifica debe invalidar la clave:

```php
// Al actualizar el menú
MenuItem::create([...]);
$redis->del('cache:menu:structure');
```

## Visión

> Redis tendrá un wrapper de alto nivel: `Cache::remember('clave', 3600, fn() => Producto::all())` que maneja la lógica de "si existe, retorna; si no, ejecuta y cachea". Sin que el código de negocio toque la API de Redis directamente. También se usará para Pub/Sub entre instancias del servidor cuando se escale horizontalmente.
