---
tipo: controller
capa: app-controllers
namespace: App\Controllers\MenuConfig\Controllers
archivo: App/Controllers/MenuConfig/Controllers/MenuConfigController.php
loc: 622
deps: 8
dependents: 0
responsabilidad: Gestiona las operaciones CRUD del menú de navegación, permitiendo listar, actualizar y crear items con validación y manejo de roles.
atributos:
  - ApiRoutes
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
  - grafo/atributo/ApiRoutes
---
# MenuConfigController

`App\Controllers\MenuConfig\Controllers\MenuConfigController`

📁 [App/Controllers/MenuConfig/Controllers/MenuConfigController.php](../../../App/Controllers/MenuConfig/Controllers/MenuConfigController.php)

> [!abstract] Responsabilidad
> Gestiona las operaciones CRUD del menú de navegación, permitiendo listar, actualizar y crear items con validación y manejo de roles.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MenuConfigController` es un controlador específico diseñado para gestionar las operaciones CRUD (Crear, Leer, Actualizar, Eliminar) del menú de navegación en la aplicación. Su principal objetivo es proporcionar una interfaz API que permita a los usuarios listar, actualizar y crear items de menú con validación de campos y manejo adecuado de niveles jerárquicos. La necesidad de esta clase surge porque el menú de navegación es un componente crítico de la interfaz de usuario que requiere una gestión precisa para mantener su estructura y funcionalidad.
> 
> ### Métodos principales
> 
> 1. **list()**: Este método maneja solicitudes GET a `/api/menu-config/list` para listar todos los items del menú. Ordena los items primero por nivel, luego por `parent_id` (nulls last), y finalmente por `display_order`. Los resultados se devuelven como una respuesta JSON con un estado de éxito.
> 
> 2. **update()**: Este método maneja solicitudes POST a `/api/menu-config/update` para actualizar múltiples items del menú. Permite la actualización de campos específicos como `label`, `icon`, `display_order`, y `level`. También se encarga de calcular automáticamente el nivel jerárquico cuando se cambia el `parent_id`.
> 
> 3. **create()**: Este método maneja solicitudes POST a `/api/menu-config/create` para crear un nuevo item del menú. Valida los campos requeridos, genera un ID único y determina el nivel y orden de visualización adecuados.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant MenuConfigController as Controller
>     participant MenuItem as Model
>     participant DB as PostgreSQL
>     
>     Client->>Controller: GET /api/menu-config/list
>     Controller->>MenuItem: orderBy('level').orderByRaw('parent_id IS NULL, parent_id').orderBy('display_order')
>     MenuItem-->>DB: SELECT query
>     DB-->>MenuItem: Items data
>     MenuItem-->>Controller: Items array
>     Controller-->>Client: JSON response
>     
>     Client->>Controller: POST /api/menu-config/update
>     Controller->>Controller: validate body
>     Controller->>MenuItem: find(id)
>     MenuItem-->>DB: SELECT query
>     DB-->>MenuItem: Item data
>     MenuItem-->>Controller: Item instance
>     Controller->>MenuItem: fill(data).save()
>     MenuItem-->>DB: UPDATE query
>     DB-->>MenuItem: Success status
>     MenuItem-->>Controller: Refreshed item
>     Controller->>Controller: updateChildrenLevels(item, level)
>     Controller-->>Client: JSON response
>     
>     Client->>Controller: POST /api/menu-config/create
>     Controller->>Controller: validate body
>     Controller->>MenuItem: create(data)
>     MenuItem-->>DB: INSERT query
>     DB-->>MenuItem: New item ID
>     MenuItem-->>Controller: Item instance
>     Controller-->>Client: JSON response
> ```
> 
> ### Cómo encaja
> 
> La clase `MenuConfigController` se integra dentro del sistema como un controlador específico que extiende de `CoreController`. A través de la anotación `ApiRoutes`, define rutas específicas para las operaciones CRUD relacionadas con el menú. Este controlador interactúa directamente con el modelo `MenuItem` para realizar consultas y actualizaciones en la base de datos, asegurando que todas las operaciones sean validadas y gestionadas correctamente.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🏷️ Atributos declarativos

- [[api-routes|ApiRoutes]]

## 🆕 Instancia

- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[menu-item|MenuItem]]
- [[response|Response]]
- [[role|Role]]

## 🔗 Constantes referenciadas

- [[status-codes|StatusCodes]]

## 📥 Type hints (parámetros)

- [[menu-item|MenuItem]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.