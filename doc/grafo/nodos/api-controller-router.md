---
tipo: class
capa: core-routing
namespace: Core\Routing
archivo: Core/Routing/ApiControllerRouter.php
loc: 338
deps: 3
dependents: 0
responsabilidad: "Registra automáticamente rutas de API desde controladores con el atributo #[ApiRoutes], soportando múltiples métodos HTTP y presets personalizados."
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-routing
---
# ApiControllerRouter

`Core\Routing\ApiControllerRouter`

📁 [Core/Routing/ApiControllerRouter.php](../../../Core/Routing/ApiControllerRouter.php)

> [!abstract] Responsabilidad
> Registra automáticamente rutas de API desde controladores con el atributo #[ApiRoutes], soportando múltiples métodos HTTP y presets personalizados.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ApiControllerRouter` fue creada para simplificar y automatizar el registro de rutas API en el framework PHP Lego. Su principal objetivo es facilitar el desarrollo de APIs RESTful al permitir que los desarrolladores definen rutas automáticamente a través del uso de atributos, eliminando la necesidad de escribir manualmente cada ruta en un archivo de configuración. Esto no solo agiliza el proceso de desarrollo sino que también reduce errores humanos y mejora la mantenibilidad del código.
> 
> ### Métodos principales
> 
> 1. **registerRoutes()**: Este método es el punto de entrada para registrar todas las rutas API automáticamente. Recorre los directorios especificados, detecta controladores con el atributo `#[ApiRoutes]`, y registra sus rutas según la configuración definida.
> 
> 2. **discoverControllers()**: Escanea recursivamente los directorios de controladores para encontrar clases que tengan el atributo `#[ApiRoutes]`. Almacena estas clases en un cache para evitar escaneos repetidos.
> 
> 3. **registerControllerRoutes()**: Registra las rutas específicas para un controlador dado. Utiliza la configuración del atributo `ApiRoutes` para determinar los métodos HTTP soportados y las acciones a registrar.
> 
> 4. **getClassFromFile()**: Extrae el nombre completo de una clase desde un archivo PHP, lo que es crucial para identificar y cargar correctamente los controladores.
> 
> 5. **isDevelopment()**: Verifica si la aplicación está en modo desarrollo, lo que permite habilitar logs adicionales para debugging.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant ApiControllerRouter as Router
>     participant Flight as Framework
>     participant Controller as ToolsController
>     participant DB as PostgreSQL
>     
>     Client->>ApiControllerRouter: registerRoutes()
>     ApiControllerRouter->>ApiControllerRouter: discoverControllers()
>     ApiControllerRouter->>ApiControllerRouter: registerControllerRoutes(Controller)
>     ApiControllerRouter->>Flight: route("GET /tools")
>     Flight->>Controller: new Controller(action)
>     Controller->>DB: Database operations
>     DB-->>Controller: Response data
>     Controller-->>Flight: 200 JSON
>     Flight-->>Client: API response
> ```
> 
> ### Cómo encaja
> 
> La clase `ApiControllerRouter` se integra como un componente central del sistema de ruteo en el framework Lego. Funciona junto con el framework `Flight`, que maneja las solicitudes HTTP, y los controladores definidos en el directorio `App/Controllers`. Al escanear automáticamente los controladores y registrar sus rutas basadas en el atributo `#[ApiRoutes]`, esta clase facilita la creación de APIs RESTful de manera eficiente y coherente.

## 🔗 Constantes referenciadas

- [[api-routes|ApiRoutes]]

## 📥 Type hints (parámetros)

- [[api-routes|ApiRoutes]]

## 📤 Tipos de retorno

- [[api-routes|ApiRoutes]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.