---
tipo: abstract-class
capa: core-controllers
namespace: Core\Controllers
archivo: Core/Controllers/CoreController.php
loc: 96
deps: 3
dependents: 15
responsabilidad: Define la clase abstracta base de controladores, encapsulando métodos HTTP y mapeo automático de rutas desde directorios y archivos JSON.
tags:
  - grafo
  - grafo/tipo/abstract-class
  - grafo/capa/core-controllers
---
# CoreController

`Core\Controllers\CoreController`

📁 [Core/Controllers/CoreController.php](../../../Core/Controllers/CoreController.php)

> [!abstract] Responsabilidad
> Define la clase abstracta base de controladores, encapsulando métodos HTTP y mapeo automático de rutas desde directorios y archivos JSON.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `CoreController` es una abstracción fundamental en el framework Lego, diseñada para proporcionar una base común y un conjunto de funcionalidades estándar a todos los controladores del sistema. Su principal objetivo es encapsular la lógica relacionada con los métodos HTTP y el mapeo automático de rutas a clases controladoras, lo que facilita la mantenibilidad y la escalabilidad del código. Al implementar la interfaz `CoreControllerContract`, asegura que todos los controladores heredados cumplan con un conjunto de requisitos básicos, como manejar solicitudes HTTP específicas.
> 
> ### Métodos principales
> 
> 1. **getMethod($request, $accion)**: Este método es el punto de entrada para procesar las solicitudes HTTP. Recibe la solicitud y la acción solicitada, validando si la acción es permitida antes de ejecutarla. Si la acción no está en la lista de métodos permitidos (`arrayMethods`), retorna un error.
> 
> 2. **validateMethod($accion)**: Un método privado que verifica si una acción dada está dentro del conjunto de métodos HTTP permitidos (`arrayMethods`). Retorna un mensaje de error si la acción no es válida, o `false` si es válida.
> 
> 3. **mapControllers()**: Este método estático se encarga de mapear automáticamente todas las clases controladoras ubicadas en el directorio especificado. Recorre las carpetas y archivos dentro del directorio `App/Controllers`, identifica las clases controladoras, y crea un mapa de rutas basado en los nombres de las clases y sus propiedades estáticas (`ROUTE`). Este mapa es utilizado para enrutar solicitudes HTTP a las clases controladoras correspondientes.
> 
> 4. **getMymapControllers()**: Otro método estático que lee un archivo JSON (`routeMap.json`) que contiene un mapa predefinido de rutas y controladores. Retorna este mapa como un array asociativo, o lanza una respuesta JSON con un error 500 si ocurre algún problema al leer el archivo.
> 
> 5. **getListNamesByDir(string $dir)**: Un método privado estático que lista todos los nombres de archivos en un directorio dado, excluyendo las entradas `.` y `..`. Este método es utilizado por `mapControllers()` para obtener la lista de carpetas y archivos dentro del directorio de controladores.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class CoreController {
>         <<abstract>>
>         +arrayMethods
>         +getMethod($request, $accion)
>         +validateMethod($accion)
>         +mapControllers()
>         +getMymapControllers() : array
>         +getListNamesByDir(string $dir) : array
>     }
>     CoreController <|-- RestfulController
>     CoreController <|-- AuthGroupsController
>     CoreController <|-- ComponentsController
>     CoreController <|-- ExampleCrudController
>     CoreController <|-- FilesController
>     CoreController <|-- MenuItemHierarchyController
>     CoreController <|-- MenuSearchController
>     CoreController <|-- MenuStructureController
>     CoreController <|-- MenuSystemItemsController
>     CoreController <|-- MenuConfigController
>     CoreController <|-- RolesConfigController
>     CoreController <|-- StorageController
> ```
> 
> ### Cómo encaja
> 
> La clase `CoreController` se integra como la base de todos los controladores del sistema, proporcionando una estructura común y funcionalidades básicas que son utilizadas por sus clases hijas. Los métodos `getMethod()` y `validateMethod()` manejan el procesamiento de solicitudes HTTP, asegurando que solo las acciones permitidas sean ejecutadas. El método `mapControllers()` permite un mapeo automático de rutas a controladores, simplificando la configuración del sistema y facilitando la adición de nuevos controladores. Además, el método `getMymapControllers()` proporciona una forma alternativa de cargar un mapa predefinido de rutas desde un archivo JSON, lo que puede ser útil para configuraciones más complejas o para mantener las rutas separadas del código fuente.

## 📐 Implementa

- [[core-controller-contract|CoreControllerContract]]

## 🆕 Instancia

- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[response|Response]]

## 👥 Es referenciado por

- [[auth-groups-controller|AuthGroupsController]] *(extends)*
- [[components-controller|ComponentsController]] *(extends)*
- [[example-crud-controller|ExampleCrudController]] *(extends)*
- [[files-controller|FilesController]] *(extends)*
- [[map-routes-command|MapRoutesCommand]] *(static_call)*
- [[menu-config-controller|MenuConfigController]] *(extends)*
- [[menu-item-hierarchy-controller|MenuItemHierarchyController]] *(extends)*
- [[menu-search-controller|MenuSearchController]] *(extends)*
- [[menu-structure-controller|MenuStructureController]] *(extends)*
- [[menu-system-items-controller|MenuSystemItemsController]] *(extends)*
- [[restful-controller|RestfulController]] *(extends)*
- [[roles-config-controller|RolesConfigController]] *(extends)*
- [[storage-controller|StorageController]] *(extends)*
- [[tools-controller|ToolsController]] *(extends)*
- [[users-config-controller|UsersConfigController]] *(extends)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.