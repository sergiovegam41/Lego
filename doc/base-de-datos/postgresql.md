# PostgreSQL

Motor de base de datos principal de Lego. Toda la información relacional del sistema vive aquí.

Relacionado: [[base-de-datos/modelos]] · [[base-de-datos/migraciones]] · [[infraestructura/docker]]

---

## Configuración

Variables en `.env`:

```
DB_HOST=db
DB_PORT=5432
DB_DATABASE=lego
DB_USERNAME=lego_user
DB_PASSWORD=secret
```

La conexión la establece Illuminate Eloquent en `Core/bootstrap.php` al arrancar.

## Versión y Motor

- PostgreSQL 17
- Corre en Docker (`services.db` en `docker-compose.yml`)
- Datos persistidos en volumen Docker nombrado

## Tablas del Sistema

| Tabla | Propósito |
|-------|-----------|
| `migrations` | Control de migraciones ejecutadas |
| `auth_users` | Usuarios del sistema |
| `auth_user_sessions` | Sesiones activas |
| `auth_roles` | Roles de usuario |
| `auth_groups` | Grupos de autenticación |
| `menu_items` | Estructura del menú lateral |
| `tools` | Herramientas del sistema |
| `tool_features` | Características de herramientas |
| `files` | Metadatos de archivos subidos |
| `entity_files` | Asociaciones archivo-entidad |
| `example_crud` | Demo CRUD |
| `example_crud_images` | Imágenes del demo |

## Convenciones

- **PKs**: `id` integer auto-increment (o UUID en tablas nuevas)
- **Timestamps**: columnas `created_at` y `updated_at`, actualizadas por triggers
- **Soft deletes**: `deleted_at` nullable (cuando aplica)
- **Nombres**: snake_case para tablas y columnas
- **Prefijos**: tablas de auth usan prefijo `auth_`

## Triggers Automáticos

PostgreSQL actualiza `updated_at` automáticamente en cada UPDATE mediante un trigger creado en las migraciones:

```sql
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';
```

## PgAdmin

Interfaz web para explorar la BD durante desarrollo:
- URL: `http://localhost:8081`
- Credenciales en `.env`: `PGADMIN_EMAIL`, `PGADMIN_PASSWORD`

## Consultas Directas (Helper)

Para casos donde Eloquent es excesivo, existe el helper `consultar()`:

```php
$resultados = consultar("SELECT * FROM menu_items WHERE level = 1");
```

> [!warning]
> Usar `consultar()` solo cuando sea necesario. Eloquent es la forma preferida — tiene protección contra SQL injection y maneja la conexión automáticamente.

## Visión

> Se añadirá soporte para múltiples conexiones de base de datos: la conexión principal para datos del sistema, y conexiones secundarias configurables por módulo. Esto permite que un módulo use una BD diferente sin afectar el resto del sistema.
