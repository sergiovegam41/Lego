# Lego

Framework PHP para paneles de administración. Un componente es una clase PHP que devuelve HTML. Un módulo es una carpeta con un modelo y una migración, y de ahí Lego deduce pantallas, menú, permisos, API y comandos.

**Versión:** 2.0.0-alpha.

## En 30 segundos

```bash
php lego make:module Clients --label=Clientes
php lego migrate
```

Con eso, `/clients` tiene lista con búsqueda y orden, alta, edición y borrado con confirmación. También aparece en el menú, tiene permisos `clients.*`, responde en `POST /api/actions/clients_create` y se puede usar desde la consola:

```bash
php lego run clients_create --as=ana@ejemplo.com --set name="Florería Lima"
```

Los campos se declaran una sola vez:

```php
#[Module(label: 'Clientes', icon: 'people-outline')]
final class Client extends Record
{
    public static function fields(): array
    {
        return [
            Text::make('name', 'Nombre')->required()->searchable()->sortable(),
            Email::make('email', 'Correo')->required()->unique(),
            Money::make('credit', 'Crédito')->min(0),
        ];
    }
}
```

## Qué trae

- **Navegación sin recargar.** Pantallas abiertas como pestañas que conservan su estado. La URL siempre manda, así que atrás, adelante, F5 y los enlaces directos funcionan.
- **Seguro por defecto.** Sesiones en PostgreSQL, CSRF en toda modificación, rutas cerradas salvo las marcadas públicas y permisos por módulo y operación.
- **Una sola forma de ejecutar acciones** desde la pantalla, la API y la CLI. Todas pasan por validación, confirmación de lo destructivo y auditoría.
- **Usuarios y Roles incluidos**, hechos como un módulo más.
- **Sin build.** Módulos ES nativos y CSS con tokens, con tema claro y oscuro.
- **Listo para desplegar.** Imagen Docker autocontenida, configuración por variables de entorno y compatible con Dokploy.
- **Preparado para IA.** Cada acción tiene nombre, descripción, esquema de entrada y anotaciones; cada pantalla publica su catálogo de acciones.

## Empezar

```bash
composer install
cp .env.example .env            # completar DB_*
php lego install --email=admin@ejemplo.com
php lego serve                  # http://127.0.0.1:8000
```

Con Docker, en local o en Dokploy: [doc/despliegue.md](doc/despliegue.md).

## Documentación

| Documento | Para qué |
|---|---|
| [AGENTS.md](AGENTS.md) | Mapa del repositorio y reglas, para personas y agentes de IA |
| [doc/modulos.md](doc/modulos.md) | Crear módulos, campos y acciones |
| [doc/frontend.md](doc/frontend.md) | Shell, pantallas abiertas y JavaScript |
| [doc/ia.md](doc/ia.md) | Preparación para asistentes de IA: catálogo, anotaciones y activación |
| [doc/despliegue.md](doc/despliegue.md) | Docker, Dokploy y tests |
| [doc/lego2/decisiones.md](doc/lego2/decisiones.md) | Decisiones de diseño y su porqué |

## Tests

```bash
composer test                                       # sin base de datos se omiten los de Postgres
docker compose -f compose.test.yml run --rm tests   # suite completa con Postgres
docker compose -f compose.test.yml run --rm browser # escenarios de navegador con Playwright
```

## Licencia

MIT.
