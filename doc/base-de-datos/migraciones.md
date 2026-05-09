# Migraciones

Las migraciones controlan la evolución del esquema de base de datos. Cada migración es un archivo PHP con instrucciones SQL.

Relacionado: [[base-de-datos/postgresql]] · [[base-de-datos/modelos]]

Código: `database/migrations/`

Flujo: [[flows/crear-migracion]]

---

## Estructura de una Migración

```php
// database/migrations/2025_12_01_000001_create_productos.php

return new class {
    public function up(): void
    {
        DB::statement("
            CREATE TABLE productos (
                id          SERIAL PRIMARY KEY,
                nombre      VARCHAR(100) NOT NULL,
                precio      DECIMAL(10,2) NOT NULL DEFAULT 0,
                categoria   VARCHAR(50),
                activo      BOOLEAN NOT NULL DEFAULT true,
                created_at  TIMESTAMP DEFAULT NOW(),
                updated_at  TIMESTAMP DEFAULT NOW()
            )
        ");

        // Trigger para updated_at automático
        DB::statement("
            CREATE TRIGGER update_productos_updated_at
            BEFORE UPDATE ON productos
            FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS productos");
    }
};
```

## Nomenclatura de Archivos

```
YYYY_MM_DD_NNNNNN_descripcion.php
```

| Parte | Ejemplo | Descripción |
|-------|---------|-------------|
| Fecha | `2025_12_01` | Fecha de creación |
| Número | `000001` | Orden dentro del mismo día |
| Descripción | `create_productos` | Qué hace la migración |

## Ejecutar Migraciones

```bash
php lego migrate        # Ejecuta las pendientes
php lego migrate:fresh  # Borra todo y re-ejecuta desde cero
php lego migrate:status # Muestra estado de cada migración
```

## Migraciones del Sistema

| Archivo | Crea |
|---------|------|
| `000001_migrations` | Tabla de control de migraciones |
| `000002_auth_users` | Usuarios (`auth_users`) |
| `000003_auth_user_sessions` | Sesiones activas |
| `files` | Metadatos de archivos |
| `entity_files` | Asociaciones archivo-entidad |
| `example_crud` | Tabla demo CRUD |
| `example_crud_images` | Imágenes del demo |
| `menu_items` | Estructura del menú |
| `tools` | Herramientas |
| `tool_features` | Características de herramientas |
| `auth_roles` | Roles |
| `auth_groups` | Grupos de autenticación |

## Seeds

Algunos datos iniciales se cargan con seeds (no son migraciones de estructura):

```bash
php lego config:reset  # Re-siembra la estructura del menú desde MenuStructure.php
```

## Convenciones SQL

- Usar `SERIAL` o `UUID` para PKs
- Siempre incluir `created_at` y `updated_at`
- Crear trigger de `updated_at` para cada tabla nueva
- Usar `VARCHAR` con límite explícito, no `TEXT` sin límite
- FKs con `ON DELETE` explícito (`CASCADE`, `SET NULL`, o `RESTRICT`)

## Visión

> Las migraciones tendrán un modo de "migración cero-downtime": en vez de `ALTER TABLE` que bloquea la tabla, usará estrategias como añadir la columna como nullable primero, rellenar datos, luego agregar la restricción. Crítico cuando la base de datos tiene millones de registros.
