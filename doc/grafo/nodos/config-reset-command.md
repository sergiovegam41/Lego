---
tipo: command
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/ConfigResetCommand.php
loc: 129
deps: 3
dependents: 0
responsabilidad: Resetea la configuración de Lego a valores por defecto, con opciones para resetear solo el menú y sin confirmación.
tags:
  - grafo
  - grafo/tipo/command
  - grafo/capa/core-commands
---
# ConfigResetCommand

`Core\Commands\ConfigResetCommand`

📁 [Core/Commands/ConfigResetCommand.php](../../../Core/Commands/ConfigResetCommand.php)

> [!abstract] Responsabilidad
> Resetea la configuración de Lego a valores por defecto, con opciones para resetear solo el menú y sin confirmación.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ConfigResetCommand` existe para proporcionar una herramienta de línea de comandos que permite al usuario resetear la configuración de Lego a sus valores por defecto. Este comando es especialmente útil durante el desarrollo y pruebas, ya que facilita la restauración rápida de la configuración inicial sin tener que realizar cambios manuales en el código o la base de datos. Además, ofrece opciones para limitar el reseteo solo al menú y omitir confirmaciones, lo que mejora la eficiencia del proceso.
> 
> ### Métodos principales
> 
> 1. **`execute()`**: Este es el método principal que se ejecuta cuando se invoca el comando. Se encarga de procesar las opciones proporcionadas (`--menu` y `--force`) y determinar qué configuración resetear. También maneja la confirmación del usuario antes de realizar los cambios.
> 
> 2. **`resetMenu()`**: Este método se encarga de restablecer la estructura del menú a sus valores por defecto. Utiliza una función recursiva para insertar los elementos del menú y sus hijos en la base de datos, asegurándose de mantener la jerarquía correcta.
> 
> 3. **`ask()`**: Este método es un helper que permite preguntar al usuario una pregunta específica a través de la línea de comandos. Se utiliza para solicitar confirmación antes de proceder con el reseteo de la configuración.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ConfigResetCommand {
>         +execute(): bool
>         +resetMenu(): bool
>         +ask(string $question): string
>     }
>     CoreCommand <|-- ConfigResetCommand
> ```
> 
> ### Cómo encaja
> 
> La clase `ConfigResetCommand` se integra dentro del sistema de comandos de Lego, heredando de la clase base `CoreCommand`. Esta estructura permite que el comando sea registrado y ejecutado a través del sistema de línea de comandos centralizado. El método `execute()` maneja las opciones proporcionadas por el usuario y decide qué acciones realizar, mientras que `resetMenu()` se encarga de los detalles específicos de restablecer la configuración del menú. Este comando es útil para desarrolladores y administradores que necesitan restaurar rápidamente la configuración predeterminada de Lego sin perder datos importantes o realizar cambios manuales.

## 🔼 Hereda de

- [[core-command|CoreCommand]]

## ⚡ Llamadas estáticas

- [[menu-item|MenuItem]]
- [[menu-structure|MenuStructure]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.