# Módulos y acciones

Un módulo es una carpeta en `modules/`. Con un modelo y una migración obtiene, sin registrar nada en otro sitio:

- pantallas de lista, crear, editar y borrar,
- una entrada en el menú,
- permisos por operación,
- acciones JSON en `/api/actions`,
- acceso desde la CLI con `php lego run`,
- registro de cada cambio en `action_log`.

## Crear un módulo

```bash
php lego make:module Clients --label=Clientes
php lego migrate
```

Esto crea dos archivos:

```
modules/Clients/
  Client.php                                   el modelo y sus campos
  migrations/2026_10_15_120000_create_clients_table.php
```

El módulo ya funciona en `/clients`. Para agregar campos, se declaran en `fields()` y se agregan las columnas en la migración.

## El modelo

```php
namespace Modules\Clients;

#[Module(label: 'Clientes', icon: 'people-outline', order: 10)]
final class Client extends Record
{
    protected $table = 'clients';

    public static function fields(): array
    {
        return [
            Text::make('name', 'Nombre')->required()->max(120)->searchable()->sortable(),
            Email::make('email', 'Correo')->required()->unique()->searchable(),
            Money::make('credit', 'Crédito')->min(0)->sortable(),
            Boolean::make('active', 'Activo'),
            Select::make('tier', 'Nivel')->options(['basic' => 'Básico', 'gold' => 'Oro']),
            Date::make('since', 'Cliente desde'),
            Number::make('visits', 'Visitas')->integer()->min(0)->hideInTable(),
            TextArea::make('notes', 'Notas')->hideInTable(),
        ];
    }
}
```

`fields()` es la única fuente de verdad. De ahí salen la validación, las columnas de la tabla, los campos del formulario, la búsqueda, el orden y el esquema JSON de las acciones.

| Tipo | Guarda | Reglas |
|---|---|---|
| `Text` | texto recortado, máximo 255 por defecto | `min()`, `max()` |
| `TextArea` | texto largo sin máximo | `min()`, `max()` |
| `Email` | correo en minúsculas | formato |
| `Number` | entero o decimal | `integer()`, `min()`, `max()` |
| `Money` | importe exacto como texto `"1500.00"` | `min()`, `max()` |
| `Boolean` | sí o no; vacío es no | |
| `Date` | fecha `AAAA-MM-DD` | fecha real |
| `Select` | una de las opciones | `options()` o `optionsFrom(fn () => ...)` si salen de la base de datos |
| `Checklist` | varias opciones, como lista JSON | `options()` o `optionsFrom()`; el modelo castea la columna a `array` |

Modificadores comunes: `required()`, `unique()`, `searchable()`, `sortable()`, `hideInTable()`, `default(valor)` y `help('texto')`. `Text` acepta además `pattern(regex, mensaje)`.

Al crear, un campo opcional que no se envía conserva el valor por defecto de la base de datos. Al editar, solo cambian los campos enviados.

## Nombres que se deducen

| Carpeta | Módulo | URL | Permisos |
|---|---|---|---|
| `modules/Clients` | `clients` | `/clients` | `clients.view`, `clients.create`, `clients.update`, `clients.delete` |
| `modules/SalesOrders` | `sales_orders` | `/sales-orders` | `sales_orders.*` |

Las acciones se llaman `<módulo>_<verbo>`: `clients_list`, `clients_get`, `clients_create`, `clients_update` y `clients_delete`. Borrar siempre pide confirmación.

Para limitar las operaciones: `#[Module(label: 'Clientes', operations: ['list', 'get'])]`.

Para que un asistente de IA pueda escribir en el módulo cuando `LEGO_AGENT_ACCESS=write`: `#[Module(label: 'Notas', agentWrites: true)]`. Ver [ia.md](ia.md).

## Acciones propias

Lo que no es CRUD se declara con `#[Action]` sobre un método del modelo:

```php
#[Action(label: 'Bloquear', description: 'Impide que el cliente compre.', destructive: true, confirm: true)]
public function block(#[Param('Motivo')] string $reason): void
{
    if (!$this->active) {
        throw new BusinessRuleViolation('client_already_blocked', 'El cliente ya está bloqueado.');
    }
    $this->forceFill(['active' => false])->save();
}
```

- Un método de instancia actúa sobre un registro, y la entrada lleva su `id`. Un método estático actúa sobre el módulo entero.
- Los parámetros deben ser `string`, `int`, `float` o `bool`. Forman el esquema de entrada.
- El nombre y el permiso salen del método: `block` da la acción `clients_block` y el permiso `clients.block`.
- La descripción es obligatoria: es lo que lee un asistente de IA para decidir si usar la acción. Escríbela como una frase para alguien que no conoce la app. `#[Param('Título', 'Descripción.')]` describe cada parámetro. Ver [ia.md](ia.md).
- Si la acción manda algo fuera de la app (correo, webhook), declárala `external: true`; Lego exige entonces `confirm: true`.
- `BusinessRuleViolation` responde 409 con un código estable y un mensaje para el usuario.
- La pantalla de edición muestra un formulario para cada acción que el usuario puede ejecutar.

Si una acción necesita saber quién la ejecuta, basta un parámetro de tipo `Identity`. No forma parte de la entrada; Lego lo rellena con el usuario actual:

```php
#[Action(label: 'Asignarme', description: 'Asigna el cliente a quien ejecuta la acción.')]
public function assignToMe(Identity $by): void
{
    $this->forceFill(['owner_id' => $by->id])->save();
}
```

Si una acción devuelve `['message' => '…']`, la pantalla muestra ese mensaje antes de seguir. Así se entrega, por ejemplo, una contraseña generada.

## Reglas de negocio sobre el CRUD

Para reglas que el formulario no puede expresar, el modelo sobrescribe `beforeChange()`. Corre dentro de la transacción, antes de crear, editar o borrar, y recibe al usuario que actúa:

```php
public function beforeChange(string $operation, array $clean, Identity $by): void
{
    if ($operation === 'delete' && $this->invoices()->exists()) {
        throw new BusinessRuleViolation('client_has_invoices', 'El cliente tiene facturas.');
    }
}
```

## Módulos del núcleo

Usuarios y Roles viven en `core/Modules/` y están hechos con estas mismas piezas. Sirven de ejemplo real:

- **Usuarios.** Un usuario nuevo no tiene contraseña utilizable hasta que un administrador ejecuta "Restablecer contraseña". Esa acción muestra la clave una sola vez y cierra sus sesiones. Nadie puede eliminarse, desactivarse ni cambiarse el rol a sí mismo.
- **Roles.** Los permisos se eligen con casillas entre los que existen de verdad. Lego los deduce de los módulos y sus acciones (`PermissionCatalog`). El rol `admin` conserva `*` y no se puede renombrar ni borrar. Un rol en uso tampoco se puede borrar.

## Un módulo que es solo una pantalla

`#[Module]` también puede ir sobre un `Component` sin registros. El módulo ocupa `/{slug}` y exige el permiso `<módulo>.view`.

## Las mismas acciones en todas partes

Toda acción pasa por `ActionExecutor`, venga de donde venga. El ejecutor sigue siempre los mismos pasos:

1. Busca la acción y el registro.
2. Autoriza con el `Gate`.
3. Valida la entrada.
4. Pide confirmación si la acción la exige.
5. Ejecuta dentro de una transacción.
6. Registra el cambio en `action_log`, con la vía `ui`, `api` o `cli`.

| Superficie | Cómo |
|---|---|
| Pantalla | formularios en `/clients`, `/clients/new`, `/clients/{id}` |
| JSON | `POST /api/actions/clients_create` con el cuerpo en JSON y token CSRF |
| Catálogo | `GET /api/actions`, con solo lo que el usuario puede hacer |
| CLI | `php lego run clients_create --as=ana@ejemplo.com --set name="Lima" --set email=ventas@lima.pe` |

Las respuestas siguen el sobre único. Una acción que pide confirmación responde 428 con un token de un solo uso:

```json
{
  "status": "confirmation_required",
  "confirmation": {
    "token": "…",
    "expires_at": "2026-10-15T15:04:05+00:00",
    "message": "Se eliminará «Florería Lima». Esta acción no se puede deshacer.",
    "preview": { "action": "clients_delete", "id": 12, "label": "Florería Lima" }
  }
}
```

Para confirmar, se repite la misma petición con `confirmation_token`. El token expira en 5 minutos, sirve una sola vez y solo vale para el mismo usuario, la misma acción, el mismo registro y la misma entrada. En la CLI se confirma con `--yes`.

Cada pantalla incluye en su HTML el catálogo de acciones permitidas, en un `<script type="application/json" data-lego-actions>`. Es la base para integrar IA más adelante: ver [investigacion-webmcp-2026-10-13.md](investigacion-webmcp-2026-10-13.md).
