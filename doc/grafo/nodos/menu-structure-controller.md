---
tipo: controller
capa: app-controllers
namespace: App\Controllers\Menu\Controllers
archivo: App/Controllers/Menu/Controllers/MenuStructureController.php
loc: 155
deps: 9
dependents: 0
responsabilidad: Obtiene y filtra la estructura del menú en formato JSON para el frontend, considerando los permisos de rol del usuario actual.
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
---
# MenuStructureController

`App\Controllers\Menu\Controllers\MenuStructureController`

📁 [App/Controllers/Menu/Controllers/MenuStructureController.php](../../../App/Controllers/Menu/Controllers/MenuStructureController.php)

> [!abstract] Responsabilidad
> Obtiene y filtra la estructura del menú en formato JSON para el frontend, considerando los permisos de rol del usuario actual.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MenuStructureController` es un controlador específico diseñado para proporcionar la estructura del menú de la aplicación en formato JSON. Su principal objetivo es asegurar que los usuarios solo vean los elementos del menú que están autorizados según su rol. Esta abstracción es necesaria porque el menú puede tener una jerarquía compleja y diferentes roles pueden tener acceso a diferentes partes del sistema, lo que requiere un control preciso sobre qué items se muestran.
> 
> ### Métodos principales
> 
> 1. **`getStructure()`**: Este método es el corazón de la clase. Se encarga de obtener los elementos raíz del menú desde la base de datos, filtrarlos según el rol del usuario actual y convertirlos en objetos `MenuItemDto`. Luego, envía una respuesta JSON con la estructura del menú.
> 
> 2. **`getCurrentUserRole()`**: Este método obtiene el rol del usuario actual a partir de los servicios de autenticación. Es crucial para determinar qué items del menú se deben mostrar al usuario.
> 
> 3. **`isItemAllowedForRole(MenuItem $item, ?string $userRole)`**: Este método verifica si un item de menú específico está permitido para el rol del usuario actual. Implementa la lógica de acceso basada en roles, asegurando que solo los usuarios con los permisos adecuados puedan ver ciertos elementos.
> 
> 4. **`buildMenuItemDto(MenuItem $item, string $hostName, ?string $userRole = null)`**: Este método es recursivo y se encarga de convertir un objeto `MenuItem` en un `MenuItemDto`, incluyendo sus hijos si los tiene. También determina la URL del item y el nombre a mostrar.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant MenuStructureController as Controller
>     participant MenuItem as Model
>     participant AuthServicesCore as AuthService
>     participant Response as ResponseHandler
>     participant MenuItemDto as DTO
> 
>     Client->>Controller: GET /api/menu/structure
>     Controller->>AuthService: getCurrentUserRole()
>     AuthService-->>Controller: userRole
>     Controller->>MenuItem: root()->visible()->orderBy('display_order')->get()
>     MenuItem-->>Controller: rootItems
>     Controller->>Controller: isItemAllowedForRole(item, userRole)
>     Controller->>MenuItem: children()->visible()->orderBy('display_order')->get()
>     MenuItem-->>Controller: childItems
>     Controller->>DTO: buildMenuItemDto(item, hostName, userRole)
>     DTO-->>Controller: menuItemDto
>     Controller->>Response: json(StatusCodes::HTTP_OK, ResponseDTO(success, message, menuArray))
>     Response-->>Client: JSON response
> ```
> 
> ### Cómo encaja
> 
> La clase `MenuStructureController` se integra dentro del sistema como un controlador de API que responde a solicitudes HTTP GET para obtener la estructura del menú. Se extiende de `CoreController`, lo que le proporciona funcionalidades básicas comunes a todos los controladores en el framework. Utiliza el modelo `MenuItem` para interactuar con la base de datos y obtiene información sobre el usuario actual a través del servicio de autenticación `AuthServicesCore`. Finalmente, envía una respuesta JSON utilizando el manejador de respuestas `Response`, asegurando que los datos se devuelvan en un formato compatible con el frontend.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🆕 Instancia

- [[menu-item-dto|MenuItemDto]]
- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[auth-services-core|AuthServicesCore]]
- [[menu-item|MenuItem]]
- [[response|Response]]

## 🔗 Constantes referenciadas

- [[status-codes|StatusCodes]]

## 📥 Type hints (parámetros)

- [[menu-item|MenuItem]]

## 📤 Tipos de retorno

- [[menu-item-dto|MenuItemDto]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.