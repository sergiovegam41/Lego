---
tipo: class
capa: core-routing
namespace: Core\Routing
archivo: Core/Routing/ApiGetRouter.php
loc: 227
deps: 3
dependents: 0
responsabilidad: "Registra automáticamente rutas GET para modelos con el atributo #[ApiGetResource], utilizando introspección de PHP attributes y conectando con AbstractGetController."
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-routing
---
# ApiGetRouter

`Core\Routing\ApiGetRouter`

📁 [Core/Routing/ApiGetRouter.php](../../../Core/Routing/ApiGetRouter.php)

> [!abstract] Responsabilidad
> Registra automáticamente rutas GET para modelos con el atributo #[ApiGetResource], utilizando introspección de PHP attributes y conectando con AbstractGetController.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> `ApiGetRouter` es una clase diseñada para automatizar el registro de rutas GET en un sistema basado en componentes. Su principal objetivo es facilitar la creación de endpoints API RESTful específicamente para operaciones de lectura (GET) en modelos que utilizan el atributo `#[ApiGetResource]`. Esta abstracción se implementa para simplificar el desarrollo y mantener una estructura consistente en aplicaciones que utilizan componentes como `TableComponent` para mostrar datos.
> 
> ### Métodos principales
> 
> 1. **registerRoutes()**: Este método es el punto de entrada principal de la clase. Se encarga de escanear los directorios de modelos, detectar aquellos con el atributo `#[ApiGetResource]`, y registrar las rutas GET correspondientes utilizando el microframework Flight.
> 
> 2. **discoverModels()**: Este método recorre los directorios especificados en `$modelPaths` para encontrar archivos PHP que contengan clases con el atributo `#[ApiGetResource]`. Utiliza la reflexión de PHP para verificar si una clase tiene este atributo y, en caso afirmativo, lo agrega a un array de modelos.
> 
> 3. **registerModelRoutes()**: Una vez que se han detectado los modelos con el atributo `#[ApiGetResource]`, este método registra las rutas GET específicas para cada modelo. Crea dos rutas: una para listar todos los registros (`GET /api/get/{resource}`) y otra para obtener un registro específico por ID (`GET /api/get/resource/{id}`). Utiliza el controlador especificado en la configuración del atributo para manejar estas solicitudes.
> 
> 4. **getClassFromFile()**: Este método lee el contenido de un archivo PHP y extrae el nombre completo de la clase, incluyendo su namespace. Es utilizado para identificar las clases que se encuentran en los archivos de modelos.
> 
> 5. **isDevelopment()**: Verifica si la aplicación está en modo desarrollo utilizando variables de entorno. Esto permite habilitar o deshabilitar ciertas funcionalidades como el registro de logs detallados.
> 
> ### Diagrama
> 
> ```mermaid
> flowchart TD
>     ApiGetRouter -->|registerRoutes()| Flight
>     ApiGetRouter -->|discoverModels()| ReflectionClass
>     ApiGetRouter -->|registerModelRoutes()| AbstractGetController
> ```
> 
> ### Cómo encaja
> 
> `ApiGetRouter` se integra en el sistema como un componente de ruteo específico para operaciones GET. Funciona junto con el microframework Flight para manejar las solicitudes HTTP y la reflexión de PHP para detectar modelos con el atributo `#[ApiGetResource]`. Este enfoque permite una configuración flexible y automática de endpoints API, facilitando la integración con componentes como `TableComponent` que requieren acceso a datos a través de APIs RESTful.

## 🔗 Constantes referenciadas

- [[api-get-resource|ApiGetResource]]

## 📥 Type hints (parámetros)

- [[api-get-resource|ApiGetResource]]

## 📤 Tipos de retorno

- [[api-get-resource|ApiGetResource]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.