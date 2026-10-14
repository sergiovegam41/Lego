# Grupos de Autenticación

Lego permite múltiples grupos de autenticación independientes. Cada grupo tiene sus propios usuarios, roles, reglas y tokens — sin interferencia entre ellos.

Relacionado: [[autenticacion/sistema-auth]] · [[autenticacion/jwt]]

---

## ¿Qué es un Grupo?

Un grupo de autenticación es un dominio de seguridad aislado. Si tienes administradores y clientes en el mismo sistema, cada uno puede tener su propio proceso de login, sus propios roles y sus propias rutas protegidas.

## Grupos Existentes

| Grupo   | Ruta base      | Uso                                  |
| ------- | -------------- | ------------------------------------ |
| `admin` | `/auth/admin/` | Usuarios del panel de administración |
| `api`   | `/auth/api/`   | Acceso programático / integraciones  |

## Cómo se Usan

```
# Login del grupo admin
POST /auth/admin/login
{ "email": "admin@empresa.com", "password": "..." }

# Login del grupo api
POST /auth/api/login
{ "api_key": "...", "secret": "..." }
```

Cada grupo puede tener una estrategia de autenticación diferente.

## Aislamiento Ttaol

- Un token del grupo `admin` no funciona en rutas del grupo `api`
- Los roles del grupo `admin` no se mezclan con los del grupo `api`
- Cada grupo tiene su propia tabla de sesiones lógicamente separada por `group_id`

## Agregar un Nuevo Grupo

1. Crear el grupo en la tabla `auth_groups`
2. Registrar las rutas en `Routes/Api.php`:

```php
// Grupo 'clientes'
Flight::route('POST /auth/clientes/login', [ClientesAuthController::class, 'login']);
Flight::route('GET  /auth/clientes/me',    [ClientesAuthController::class, 'me']);
```

3. El middleware verifica automáticamente que el token pertenezca al grupo correcto.

## Modelo AuthGroup

```php
// App/Models/AuthGroup.php
// Tabla: auth_groups
// Campos: id, name, description, is_active
```

## Visión

> Los grupos de autenticación tendrán un panel de configuración dedicado donde se definen sus reglas, métodos de autenticación permitidos (password, OAuth, API key) y políticas de expiración de token, todo sin tocar código.
