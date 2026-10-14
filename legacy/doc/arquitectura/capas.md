# Capas del Framework

Lego separa responsabilidades en capas bien definidas. Cada capa tiene una única razón para cambiar.

Relacionado: [[arquitectura/vision-general]] · [[arquitectura/flujo-request]]

---

## Diagrama de Capas

```mermaid
graph TB
    subgraph Presentación
        C[components/]
    end
    subgraph Framework
        Core[Core/]
    end
    subgraph Lógica de Negocio
        App[App/Controllers/\nApp/Models/]
    end
    subgraph Configuración
        Routes[Routes/\nCore/Config/]
    end
    subgraph Assets
        Assets[assets/css/ · assets/js/]
    end
    subgraph Infraestructura
        DB[(PostgreSQL)]\nRedis[(Redis)]\nMinIO[(MinIO)]
    end

    Presentación --> Framework
    Framework --> Lógica de Negocio
    Lógica de Negocio --> Infraestructura
    Configuración --> Framework
    Assets --> Presentación
```

## Tabla de Capas

| Capa | Responsabilidad | Ubicación | Cambia cuando... |
|------|----------------|-----------|-----------------|
| **Presentación** | Componentes UI, HTML, estilos | `components/` | Cambia la UI de una feature |
| **Framework** | CoreComponent, Router, Atributos | `Core/` | Cambia el comportamiento del framework |
| **Lógica** | Controladores, Modelos | `App/` | Cambia la lógica de negocio |
| **Configuración** | Rutas, Menú, Registro | `Routes/`, `Core/Config/` | Cambia la estructura del sistema |
| **Assets** | CSS/JS globales | `assets/` | Cambia el diseño global |

## Namespaces

```php
// Componentes de aplicación
namespace Components\App\MiFeature;

// Componentes compartidos
namespace Components\Shared\Buttons;

// Core del framework
namespace Core\Components\CoreComponent;

// Controladores
namespace App\Controllers\MiFeature\Controllers;

// Modelos
namespace App\Models;
```

## Reglas de Dependencia

- **`components/`** puede usar `Core/` y `App/Models/`
- **`App/Controllers/`** puede usar `App/Models/` y servicios de `Core/`
- **`Core/`** no depende de `App/` ni de `components/`
- **`Routes/`** puede usar cualquier capa (es el punto de conexión)

> [!warning] Regla importante
> `Core/` nunca importa desde `App/` ni desde `components/`. La dependencia siempre fluye hacia el núcleo, nunca hacia afuera.

## Estructura de un Módulo Completo

Un módulo bien estructurado (ej: Usuarios) tiene piezas en cada capa:

```
components/App/UsersConfig/          ← Presentación
├── UsersConfigComponent.php
├── users-config.css
└── users-config.js

App/Controllers/UsersConfig/         ← Lógica
└── Controllers/
    └── UsersConfigController.php

App/Models/                          ← Datos
└── User.php

database/migrations/                 ← Infraestructura
└── 2024_01_01_000002_auth_users.php
```

## Visión

> La separación por capas permite escalar el equipo: un desarrollador puede trabajar en la capa de presentación sin tocar la lógica, y viceversa. A futuro, cada capa podría tener su propio conjunto de tests con reglas de arquitectura verificadas automáticamente.
