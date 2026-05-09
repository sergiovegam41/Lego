---
tipo: command
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/MakeComponentCommand.php
loc: 226
deps: 1
dependents: 0
responsabilidad: Orquesta la creación de nuevos componentes Lego, generando archivos PHP, CSS y JS con estructura predefinida.
tags:
  - grafo
  - grafo/tipo/command
  - grafo/capa/core-commands
---
# MakeComponentCommand

`Core\Commands\MakeComponentCommand`

📁 [Core/Commands/MakeComponentCommand.php](../../../Core/Commands/MakeComponentCommand.php)

> [!abstract] Responsabilidad
> Orquesta la creación de nuevos componentes Lego, generando archivos PHP, CSS y JS con estructura predefinida.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MakeComponentCommand` existe para facilitar la creación de nuevos componentes Lego en el framework PHP Lego. Este comando es crucial porque abstrae el proceso complejo de generar múltiples archivos y directorios necesarios para un componente, permitiendo a los desarrolladores crear rápidamente estructuras consistentes y funcionales. La necesidad de esta clase surge de la repetitividad inherentemente alta en la creación manual de componentes con sus respectivos archivos PHP, CSS y JS, así como la posibilidad de errores humanos que podrían surgir durante este proceso.
> 
> ### Métodos principales
> 
> 1. **`execute()`**: Este es el método principal del comando. Se encarga de procesar los argumentos y opciones proporcionados por el usuario al ejecutar el comando `make:component`. Valida que se haya especificado un nombre para el componente, obtiene las opciones de tipo y ruta, y luego llama a la función `createComponent()` para generar la estructura del componente.
> 
> 2. **`createComponent()`**: Este método es responsable de crear la estructura física del componente en el sistema de archivos. Crea los directorios necesarios y llama a otros métodos para generar los archivos PHP, CSS y JS con contenido predeterminado basado en plantillas.
> 
> 3. **`createPhpFile()`, `createCssFile()`, `createJsFile()`**: Estos tres métodos se encargan de crear los archivos individuales del componente. Cada uno recibe la ruta del directorio del componente, el nombre y el espacio de nombres (en el caso de PHP), y luego escribe el contenido correspondiente en un archivo.
> 
> 4. **`getPhpTemplate()`, `getCssTemplate()`, `getJsTemplate()`**: Estos métodos proporcionan las plantillas de código para los archivos PHP, CSS y JS respectivamente. Utilizan variables como el nombre del componente y su espacio de nombres para personalizar el contenido generado.
> 
> 5. **`kebabCase()`**: Este método convierte un string en formato PascalCase a kebab-case, lo que es útil para generar nombres de archivos CSS y JS consistentes con las convenciones de nomenclatura del framework.
> 
> 6. **`displayComponentInfo()`**: Después de crear el componente, este método muestra información detallada sobre la ubicación de los archivos generados y un ejemplo de cómo usar el nuevo componente en el código.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant Command as MakeComponentCommand
>     participant CoreCommand
>     participant FileSystem as File System
> 
>     Client->>Command: make:component ComponentName --type=default --path=App
>     Command->>CoreCommand: execute()
>     CoreCommand-->>Command: true
>     Command->>FileSystem: create directory components/App/ComponentName
>     Command->>FileSystem: write ComponentNameComponent.php
>     Command->>FileSystem: write componentName.css
>     Command->>FileSystem: write componentName.js
>     FileSystem-->>Command: success
>     Command->>Client: Component created successfully!
> ```
> 
> ### Cómo encaja
> 
> La clase `MakeComponentCommand` se integra dentro del sistema de comandos del framework PHP Lego. Como extensión de la clase `CoreCommand`, hereda funcionalidades básicas para el manejo de comandos, lo que facilita su integración con otros comandos similares en el sistema. Su rol principal es simplificar y automatizar la creación de componentes, reduciendo así el tiempo y el error humano asociado con la generación manual de archivos y estructuras de directorios.

## 🔼 Hereda de

- [[core-command|CoreCommand]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.