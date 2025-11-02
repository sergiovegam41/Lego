# Sistema de Extensión Automática de Tokens

## Descripción General

Se ha implementado un sistema de extensión automática de tokens en el framework LEGO que actualiza la expiración del token de acceso cuando el usuario está activo, sin necesidad de que el frontend realice llamadas manuales al endpoint de refresh.

## Cómo Funciona

### 1. Protección de Componentes

Los componentes se protegen usando el atributo `#[ApiComponent]`:

```php
#[ApiComponent('/ruta-componente', methods: ['GET'], requiresAuth: true)]
class MiComponente extends CoreComponent
{
    // ...
}
```

**Parámetros:**
- `path`: Ruta del componente
- `methods`: Métodos HTTP permitidos (GET, POST, PUT, DELETE)
- `requiresAuth`: Si requiere autenticación (default: true)

### 2. Middleware de Autenticación

Cuando `requiresAuth: true`, el sistema ejecuta automáticamente:

**Flujo de Validación:**
```
Request → ApiRouteDiscovery → AdminMiddlewares::isAutenticated() →
LegoHelpers::isAutenticated() → AuthServicesCore::isAutenticated() →
Validación + Extensión de Token
```

**Ubicación del middleware:**
- [Core/Services/ApiRouteDiscovery.php:120](Core/Services/ApiRouteDiscovery.php#L120)

### 3. Sistema de Extensión Automática

#### Parámetros Configurables

En [Core/Services/AuthServicesCore.php:19-20](Core/Services/AuthServicesCore.php#L19-L20):

```php
private $token_extension_threshold = 3; // Segundos antes de expiración para extender
private $activity_check_interval = 1800; // 30 minutos entre extensiones
```

#### Lógica de Extensión

El método `extendTokenIfActive()` se ejecuta automáticamente en cada validación:

**IMPORTANTE:** Redis es la fuente de verdad para validación de tokens. Este método SIEMPRE actualiza Redis cuando el token está próximo a expirar, independientemente del rate limiting.

**Estrategia de Extensión:**

1. **Token Próximo a Expirar** (≤3 segundos):
   - ✅ **SIEMPRE** actualiza Redis con nuevo TTL (5 segundos)
   - Esto garantiza que el usuario activo nunca se desloguee

2. **Rate Limiting de Base de Datos** (30 minutos):
   - Si han pasado ≥30 minutos desde última extensión:
     - ✅ Actualiza Redis
     - ✅ Actualiza BD (`expires_at` + `last_activity_at`)
     - ✅ Actualiza cookie del navegador
     - ✅ Log: "Token extendido (Redis + BD)"

   - Si NO han pasado 30 minutos:
     - ✅ Actualiza Redis (evita logout)
     - ❌ NO actualiza BD (rate limiting)
     - ❌ NO actualiza cookie
     - ✅ Log: "Token extendido (solo Redis)"

**Razón del Rate Limiting:**
- Evitar writes excesivos a PostgreSQL
- Redis se actualiza siempre porque es crítico para autenticación
- BD solo se actualiza para auditoría y persistencia a largo plazo

**Implementación:** [Core/Services/AuthServicesCore.php:88-158](Core/Services/AuthServicesCore.php#L88-L158)

### 4. Estructura de Base de Datos

#### Tabla: `auth_user_sessions`

Nueva columna agregada:

```sql
last_activity_at TIMESTAMP DEFAULT NOW()
```

**Propósito:** Rastrear la última actividad del usuario para evitar extensiones excesivas.

**Migración:** [database/sql/migrations/20251026_add_last_activity_to_user_sessions.sql](database/sql/migrations/20251026_add_last_activity_to_user_sessions.sql)

#### Estructura Completa

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | SERIAL | ID único de la sesión |
| `auth_user_id` | INT | ID del usuario |
| `device_id` | VARCHAR(255) | ID del dispositivo |
| `refresh_token` | TEXT | Token de refresco (30 días) |
| `access_token` | TEXT | Token de acceso (5 segundos) |
| `firebase_token` | TEXT | Token para notificaciones push |
| `expires_at` | TIMESTAMP | Expiración del access_token |
| `refresh_expires_at` | TIMESTAMP | Expiración del refresh_token |
| `is_active` | BOOLEAN | Si la sesión está activa |
| `last_activity_at` | TIMESTAMP | Última actividad del usuario |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Última actualización |

### 5. Almacenamiento en Redis

**Clave:** `access_token:{valor_del_token}`

**Valor JSON:**
```json
{
    "auth_user_id": 1,
    "device_id": "device_123",
    "auth_group_id": "ADMINS",
    "role_id": "SUPERADMIN",
    "expires_at": "2025-10-26 10:30:45"
}
```

**TTL:** 5 segundos (se actualiza automáticamente en cada extensión)

## Flujo Completo de una Request Protegida

```
1. Usuario accede a /component/inicio (protegido)
   ↓
2. ApiRouteDiscovery verifica #[ApiComponent] con requiresAuth: true
   ↓
3. Ejecuta AdminMiddlewares::isAutenticated()
   ↓
4. Obtiene token de:
   - Header: Authorization: Bearer {token}
   - Cookie: access_token
   ↓
5. Busca sesión en Redis con clave: access_token:{token}
   ↓
6. Valida que no haya expirado
   ↓
7. EXTENSIÓN AUTOMÁTICA (si aplica):
   a. Verifica si quedan ≤3 segundos
   b. Verifica si pasaron ≥30 min desde última actividad
   c. Si ambas condiciones = true:
      - Actualiza Redis (nuevo TTL 5 seg)
      - Actualiza BD (expires_at + last_activity_at)
      - Actualiza Cookie
   ↓
8. Retorna ResponseDTO(success: true, sessionData)
   ↓
9. Renderiza el componente
```

## Casos de Uso

### Caso 1: Usuario Navega Activamente

**Escenario:** Usuario navegando entre componentes cada 2 segundos

**Comportamiento:**
- **T=0s:** Login → Token expira en 5 segundos
- **T=2s:** Navega a /component/inicio
  - Token tiene 3 segundos restantes (≤ threshold)
  - ✅ **Redis actualizado** → TTL renovado a 5 segundos
  - ✅ **BD actualizada** (primera extensión)
  - ✅ Cookie actualizada
  - Log: "Token extendido (Redis + BD)"

- **T=4s:** Navega a /component/dashboard
  - Token tiene 3 segundos restantes (≤ threshold)
  - ✅ **Redis actualizado** → TTL renovado a 5 segundos
  - ❌ **BD NO actualizada** (no han pasado 30 min)
  - ❌ Cookie NO actualizada
  - Log: "Token extendido (solo Redis)"

- **T=6s:** Navega a /component/profile
  - Token tiene 3 segundos restantes (≤ threshold)
  - ✅ **Redis actualizado** → TTL renovado a 5 segundos
  - ❌ **BD NO actualizada** (rate limiting)
  - Log: "Token extendido (solo Redis)"

**Resultado:**
- El usuario NUNCA se desloguea mientras navega activamente
- Redis se actualiza en cada request (crítico para autenticación)
- BD solo se actualiza cada 30 minutos (reduce writes innecesarios)

### Caso 2: Usuario Inactivo

**Escenario:** Usuario deja la pestaña abierta 10 minutos

**Comportamiento:**
- Sin requests = sin validaciones = sin extensiones
- Token expira en Redis después de 5 segundos
- Próxima request: Token expirado → 401 Unauthorized
- Frontend debe hacer refresh con el refresh_token

### Caso 3: Usuario Regresa Después de 35 Minutos

**Escenario:** Usuario regresa después de más de 30 minutos

**Comportamiento:**
1. Refresh token aún válido (30 días)
2. Frontend llama a `/auth/admin/refresh_token`
3. Backend genera nuevo access_token
4. Actualiza `last_activity_at` = NOW()
5. Usuario continúa navegando normalmente

## Endpoints Relacionados

### Login
```
POST /auth/{grupo}/login
Body: { "username": "...", "password": "...", "device_id": "..." }
```

Inicializa `last_activity_at` en la sesión.

### Refresh Token
```
POST /auth/{grupo}/refresh_token
Body: { "refresh_token": "...", "device_id": "..." }
```

Actualiza `last_activity_at` al renovar el token.

### Componentes Protegidos
```
GET /component/{nombre}
Header: Authorization: Bearer {access_token}
```

Valida y extiende automáticamente si aplica.

## Configuración

### Variables de Entorno

```env
JWT_SECRET=tu-secreto-super-seguro-aqui
```

### Ajustar Tiempos de Expiración

En [Core/Services/AuthServicesCore.php:16-20](Core/Services/AuthServicesCore.php#L16-L20):

```php
private $jwt_expire = 5; // Expiración del access_token (segundos)
private $refresh_token_expire = 2592000; // Expiración del refresh_token (30 días)
private $token_extension_threshold = 3; // Umbral para extender (segundos)
private $activity_check_interval = 1800; // Intervalo entre extensiones (30 minutos)
```

**Recomendaciones:**
- `jwt_expire`: Mantener bajo (5-10 seg) para mayor seguridad
- `token_extension_threshold`: 60-70% del jwt_expire
- `activity_check_interval`: Según la aplicación (15-60 min)

## Seguridad

### Ventajas del Sistema

1. **Tokens de Corta Duración**: Access tokens de solo 5 segundos reducen ventana de ataque
2. **Extensión Inteligente**: Solo se extiende si el usuario está activo
3. **Rate Limiting Incorporado**: El `activity_check_interval` evita extensiones abusivas
4. **Multi-Dispositivo**: Cada dispositivo tiene su propia sesión independiente
5. **Invalidación Centralizada**: Redis permite invalidar tokens instantáneamente
6. **Auditoría**: `last_activity_at` permite rastrear actividad del usuario

### Consideraciones

- **Redis Obligatorio**: El sistema requiere Redis funcionando
- **Sincronización de Tiempo**: Servidores deben tener clocks sincronizados (NTP)
- **HTTPS Recomendado**: Para proteger tokens en tránsito
- **HttpOnly Cookies**: Evita robo de tokens vía XSS

## Monitoreo y Logs

El sistema registra extensiones en el log de errores:

```
Token extendido para usuario 1 en dispositivo device_123. Nueva expiración: 2025-10-26 10:30:45
```

**Ubicación del log:** Según configuración de `error_log()` en PHP

## Migración

### Ejecutar Migración

1. Asegurarse de que la migración está registrada en `database/migrations.json`
2. Ejecutar:

```bash
php database/migrate.php
```

3. Verificar que la columna `last_activity_at` existe:

```sql
SELECT column_name
FROM information_schema.columns
WHERE table_name = 'auth_user_sessions'
  AND column_name = 'last_activity_at';
```

## Archivos Modificados

### Core
- [Core/Services/AuthServicesCore.php](Core/Services/AuthServicesCore.php)
  - Líneas 19-20: Configuración de extensión
  - Líneas 76: Llamada a `extendTokenIfActive()`
  - Líneas 88-158: Implementación de `extendTokenIfActive()`
  - Línea 210: Inicialización de `last_activity_at` en login
  - Línea 266: Actualización de `last_activity_at` en refresh

### Modelos
- [App/Models/UserSession.php](App/Models/UserSession.php)
  - Línea 22: Agregado `last_activity_at` a `$fillable`
  - Línea 29: Agregado `last_activity_at` a `$casts`

### Base de Datos
- [database/sql/migrations/20251026_add_last_activity_to_user_sessions.sql](database/sql/migrations/20251026_add_last_activity_to_user_sessions.sql)
- [database/migrations.json](database/migrations.json)

## Preguntas Frecuentes

### ¿Por qué el token tiene solo 5 segundos de duración?

Para minimizar la ventana de tiempo en que un token robado podría ser usado. Con extensión automática, esto es transparente para el usuario activo.

### ¿Qué pasa si Redis se cae?

Todas las validaciones fallarán con 401. Los usuarios deberán hacer refresh con su refresh_token (almacenado en BD PostgreSQL).

### ¿Cómo desactivo la extensión automática para un componente?

Establece `requiresAuth: false` en el atributo `#[ApiComponent]`:

```php
#[ApiComponent('/publico', methods: ['GET'], requiresAuth: false)]
```

### ¿Puedo ajustar los tiempos de extensión?

Sí, modifica las propiedades privadas en `AuthServicesCore`:
- `$token_extension_threshold`: Cuándo extender
- `$activity_check_interval`: Con qué frecuencia

### ¿Funciona para APIs externas?

Sí, el sistema funciona para cualquier grupo de autenticación (ADMINS, APIS, etc.) siempre que usen `#[ApiComponent]` con `requiresAuth: true`.

## Próximos Pasos Recomendados

1. **Monitoreo**: Implementar métricas de extensiones de token
2. **Alertas**: Notificar si hay patrones inusuales de extensión
3. **Dashboard**: Visualizar sesiones activas y su última actividad
4. **Configuración Dinámica**: Permitir ajustar tiempos vía panel de admin
5. **WebSockets**: Considerar invalidación de tokens en tiempo real

## Soporte

Para más información sobre el sistema de autenticación LEGO, consulta:
- [Documentación de ApiComponent](Core/Attributes/ApiComponent.php)
- [Documentación de ApiRouteDiscovery](Core/Services/ApiRouteDiscovery.php)
- [Middlewares de Admin](App/Controllers/Auth/Providers/AuthGroups/Admin/Middlewares/AdminMiddlewares.php)
