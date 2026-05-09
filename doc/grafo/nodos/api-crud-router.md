---
tipo: class
capa: core-routing
namespace: Core\Routing
archivo: Core/Routing/ApiCrudRouter.php
loc: 244
deps: 3
dependents: 0
responsabilidad: "Registra automáticamente rutas CRUD para modelos con el atributo #[ApiCrudResource] mediante introspección de PHP attributes."
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-routing
---
# ApiCrudRouter

`Core\Routing\ApiCrudRouter`

📁 [Core/Routing/ApiCrudRouter.php](../../../Core/Routing/ApiCrudRouter.php)

> [!abstract] Responsabilidad
> Registra automáticamente rutas CRUD para modelos con el atributo #[ApiCrudResource] mediante introspección de PHP attributes.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ApiCrudRouter` existe para automatizar el registro de rutas CRUD (Create, Read, Update, Delete) para modelos que utilizan el atributo PHP `#[ApiCrudResource]`. Este enfoque implementa la filosofía "Convention over Configuration", permitiendo a los desarrolladores definir rápidamente endpoints RESTful sin tener que escribir manualmente cada ruta y su correspondiente controlador. La necesidad de esta clase surge para simplificar el proceso de creación de APIs, reduciendo errores humanos y acelerando el desarrollo al minimizar la configuración repetitiva.
> 
> ### Métodos principales
> 
> 1. **registerRoutes()**: Este método es el punto de entrada principal que escanea los directorios de modelos, detecta las clases con el atributo `#[ApiCrudResource]` y registra automáticamente las rutas CRUD correspondientes. También proporciona un log en desarrollo para verificar las rutas registradas.
> 
> 2. **discoverModels()**: Este método recorre los directorios especificados en `$modelPaths`, busca archivos PHP, y utiliza introspección de atributos para identificar clases que tengan el atributo `#[ApiCrudResource]`. Devuelve un array con la configuración del atributo para cada modelo encontrado.
> 
> 3. **registerModelRoutes()**: Este método registra las rutas CRUD específicas para un modelo dado. Utiliza el endpoint y el controlador definidos en la configuración del atributo `#[ApiCrudResource]` para registrar cinco rutas: listado, obtención individual, creación, actualización y eliminación.
> 
> 4. **getClassFromFile()**: Este método extrae el nombre de la clase desde un archivo PHP, considerando su namespace si está presente. Es utilizado internamente por `discoverModels()` para identificar las clases en los archivos encontrados.
> 
> 5. **isDevelopment()**: Este método verifica si la aplicación se está ejecutando en modo desarrollo, lo que determina si se deben registrar logs adicionales para debugging.
> 
> ### Diagrama
> 
> ```mermaid
> flowchart TD
>     ApiCrudRouter[ApiCrudRouter] --> discoverModels[discoverModels()]
>     ApiCrudRouter --> registerModelRoutes[registerModelRoutes()]
>     discoverModels -->|Array de modelos| registerModelRoutes
>     registerModelRoutes -->|Registra rutas CRUD| Flight[Flight]
> ```
> 
> ### Cómo encaja
> 
> La clase `ApiCrudRouter` se integra en el sistema como un componente que facilita la creación de APIs RESTful. Funciona en conjunto con el framework PHP **Lego** y utiliza la biblioteca **Flight** para manejar las rutas HTTP. Al escanear los directorios de modelos y registrar automáticamente las rutas CRUD, `ApiCrudRouter` simplifica significativamente el proceso de desarrollo de APIs, permitiendo a los desarrolladores centrarse más en la lógica de negocio que en la configuración repetitiva de rutas.

## 🔗 Constantes referenciadas

- [[api-crud-resource|ApiCrudResource]]

## 📥 Type hints (parámetros)

- [[api-crud-resource|ApiCrudResource]]

## 📤 Tipos de retorno

- [[api-crud-resource|ApiCrudResource]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.