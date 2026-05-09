---
tipo: controller
capa: app-controllers
namespace: App\Controllers\Menu\Controllers
archivo: App/Controllers/Menu/Controllers/MenuItemHierarchyController.php
loc: 164
deps: 6
dependents: 0
responsabilidad: Obtiene y devuelve la jerarquía completa de un item de menú, incluyendo sus ancestros, hijos y hermanos, con sus respectivas relaciones y datos.
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
---
# MenuItemHierarchyController

`App\Controllers\Menu\Controllers\MenuItemHierarchyController`

📁 [App/Controllers/Menu/Controllers/MenuItemHierarchyController.php](../../../App/Controllers/Menu/Controllers/MenuItemHierarchyController.php)

> [!abstract] Responsabilidad
> Obtiene y devuelve la jerarquía completa de un item de menú, incluyendo sus ancestros, hijos y hermanos, con sus respectivas relaciones y datos.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MenuItemHierarchyController` existe para resolver un problema específico relacionado con la navegación y la representación de menús en la aplicación. En aplicaciones que utilizan paneles de administración basados en componentes, es común tener estructuras de menú complejas donde los elementos pueden estar anidados y ocultos. Este controlador se encarga de proporcionar una vista detallada de la jerarquía de un elemento de menú específico, incluyendo sus ancestros (padres, abuelos, etc.), el propio elemento y todos sus hijos, tanto visibles como ocultos. Esto es crucial para permitir que los usuarios interactúen con elementos del menú que están ocultos o anidados en niveles profundos.
> 
> ### Métodos principales
> 
> 1. **`__construct()`**
>    - Este método se ejecuta al instanciar la clase y llama a `getHierarchy()`, que es el punto de entrada para obtener la jerarquía del menú.
> 
> 2. **`getHierarchy()`**
>    - Este método maneja la lógica principal de obtención de la jerarquía de un elemento de menú. Recibe el ID del item desde los parámetros de consulta, verifica su existencia y luego carga sus ancestros, hijos y hermanos. Después, construye una estructura completa que incluye estos elementos y devuelve esta información como respuesta JSON.
> 
> 3. **`getAllChildren(MenuItem $item, string $hostName): array`**
>    - Este método es recursivo y se encarga de obtener todos los hijos de un elemento de menú, incluyendo aquellos que están ocultos. Para cada hijo, también obtiene sus propios hijos de manera recursiva.
> 
> 4. **`buildItemData(MenuItem $item, string $hostName): array`**
>    - Este método construye un array con los datos relevantes de un elemento de menú. Incluye información como el ID, la etiqueta, la URL (si es aplicable), el icono y otros atributos que definen cómo se debe mostrar el item en el menú.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant MenuItemHierarchyController as Controller
>     participant MenuItem as Model
>     participant ResponseDTO as DTO
>     participant StatusCodes as Codes
>     participant Flight as Router
> 
>     Client->>Controller: GET /api/menu/item-hierarchy/{id}
>     Controller->>Flight: request()->query['id']
>     alt id no existe
>         Controller-->>Client: 400 JSON (ID requerido)
>     else id existe
>         Controller->>MenuItem: with('parent')->find($itemId)
>         alt item no encontrado
>             Controller-->>Client: 404 JSON (Item no encontrado)
>         else item encontrado
>             Controller->>Controller: getAllChildren($item, $HOST_NAME)
>             Controller->>Controller: buildItemData($item, $HOST_NAME)
>             Controller->>ResponseDTO: new ResponseDTO(true, 'Jerarquía obtenida correctamente', $hierarchy)
>             Controller-->>Client: 200 JSON
>         end
>     end
> ```
> 
> ### Cómo encaja
> 
> La clase `MenuItemHierarchyController` se integra dentro del sistema como un controlador específico que maneja solicitudes relacionadas con la jerarquía de elementos de menú. Se extiende de `CoreController`, lo que significa que hereda funcionalidades básicas y estructura comunes para los controladores en el framework. Este controlador no tiene clases que lo extiendan, implemente o use como trait, ya que su responsabilidad es bastante específica y autónoma. La clase se instanciaría directamente cuando se reciba una solicitud HTTP a la ruta `/api/menu/item-hierarchy/{id}`, y su función principal es proporcionar una representación detallada de la jerarquía del menú en formato JSON, lo cual es útil para interfaces de usuario que necesitan renderizar menus dinámicos.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🆕 Instancia

- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[menu-item|MenuItem]]
- [[response|Response]]

## 🔗 Constantes referenciadas

- [[status-codes|StatusCodes]]

## 📥 Type hints (parámetros)

- [[menu-item|MenuItem]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.