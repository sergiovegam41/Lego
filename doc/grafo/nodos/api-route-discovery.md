---
tipo: class
capa: core-services
namespace: Core\Services
archivo: Core/Services/ApiRouteDiscovery.php
loc: 152
deps: 4
dependents: 0
responsabilidad: Descubre y registra automáticamente todas las rutas API de componentes utilizando introspección de atributos y escaneo recursivo de directorios.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-services
---
# ApiRouteDiscovery

`Core\Services\ApiRouteDiscovery`

📁 [Core/Services/ApiRouteDiscovery.php](../../../Core/Services/ApiRouteDiscovery.php)

> [!abstract] Responsabilidad
> Descubre y registra automáticamente todas las rutas API de componentes utilizando introspección de atributos y escaneo recursivo de directorios.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ApiRouteDiscovery` existe para automatizar el proceso de descubrimiento y registro de rutas API de componentes dentro del framework Lego. Este es un problema común en aplicaciones que utilizan una arquitectura modular, donde cada componente puede tener sus propias rutas API definidas. La creación de esta clase resuelve la necesidad de mantener manualmente un registro de todas las rutas, lo cual puede volverse tedioso y propenso a errores con el tiempo.
> 
> ### Métodos principales
> 
> 1. **discover()**: Este método es el punto de entrada para el proceso de descubrimiento de rutas API. Verifica si ya se ha realizado la descarga previamente para evitar duplicados, luego busca todos los archivos de componentes en un directorio específico y registra sus rutas API.
> 
> 2. **findComponentFiles()**: Este método escanea recursivamente el directorio de componentes para encontrar todos los archivos que terminan con "Component.php". Ordena estos archivos por longitud del path para asegurar que las rutas más específicas se registran antes que las menos específicas.
> 
> 3. **registerApiRoute()**: Dado un archivo de componente, este método verifica si la clase definida en el archivo tiene el atributo `ApiComponent`. Si lo hace, extrae la configuración del atributo y registra cada uno de los métodos HTTP especificados.
> 
> 4. **registerMethod()**: Este método registra una ruta API específica para un método HTTP dado. Utiliza la biblioteca Flight para definir la ruta y asocia una función anónima que maneja la solicitud, verificando si se requiere autenticación y luego renderizando el componente correspondiente.
> 
> 5. **extractClassName()**: Este método extrae el nombre de la clase desde un archivo PHP dado. Utiliza expresiones regulares para buscar tanto el namespace como la definición de la clase en el contenido del archivo.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ApiRouteDiscovery {
>         +discover()
>         +findComponentFiles(path: string): array
>         +registerApiRoute(filePath: string)
>         +registerMethod(method: string, config: ApiComponent, className: string)
>         +extractClassName(filePath: string): ?string
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `ApiRouteDiscovery` se integra en el sistema como una herramienta de descubrimiento automático de rutas API. No tiene relaciones directas con otras clases mencionadas en las relaciones entrantes, ya que es un servicio independiente que opera sobre archivos y atributos PHP. Su función principal es facilitar la gestión de rutas API en un entorno modular, permitiendo a los desarrolladores definir rutas simplemente anotando sus componentes con el atributo `ApiComponent`, sin necesidad de registrar manualmente cada ruta.

## ⚡ Llamadas estáticas

- [[admin-middlewares|AdminMiddlewares]]
- [[response|Response]]

## 🔗 Constantes referenciadas

- [[api-component|ApiComponent]]

## 📥 Type hints (parámetros)

- [[api-component|ApiComponent]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.