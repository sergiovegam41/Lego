---
tipo: trait
capa: core
namespace: Core\Providers
archivo: Core/providers/StringMethods.php
loc: 73
deps: 0
dependents: 4
responsabilidad: Proporciona métodos para manipular y formatear cadenas de texto, incluyendo la conversión a camelCase, la limpieza HTML, y el formato de fechas en español.
tags:
  - grafo
  - grafo/tipo/trait
  - grafo/capa/core
---
# StringMethods

`Core\Providers\StringMethods`

📁 [Core/providers/StringMethods.php](../../../Core/providers/StringMethods.php)

> [!abstract] Responsabilidad
> Proporciona métodos para manipular y formatear cadenas de texto, incluyendo la conversión a camelCase, la limpieza HTML, y el formato de fechas en español.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `StringMethods` es un **trait** diseñado para proporcionar funcionalidades de manipulación y formateo de cadenas de texto a través del sistema. Su principal objetivo es facilitar tareas comunes como la conversión de cadenas a camelCase, la limpieza de HTML entities y el formato de fechas en español. Este trait se utiliza en varias partes del framework **Lego** para mejorar la reutilización de código y mantener una consistencia en la manejo de strings.
> 
> ### Métodos principales
> 
> 1. **showString(string $srt)**
>    - Esta función toma un string, limpia las HTML entities (reemplazando `&amp;` con `&`) y luego aplica `htmlentities()` para asegurar que el texto esté correctamente codificado en HTML.
> 
> 2. **toCamelCase(string $string)**
>    - Convierte una cadena de texto en formato camelCase. Divide la cadena por espacios, convierte cada palabra a minúsculas, capitaliza la primera letra de cada palabra y luego las une sin espacios.
> 
> 3. **dateToString($fecha)**
>    - Formatea una fecha dada en un string legible en español. Utiliza el objeto `DateTime` para manejar la fecha y luego llama al método `toSpanis()` para traducir los nombres de días y meses al español.
> 
> 4. **toSpanis($fecha)**
>    - Traduce los nombres de días y meses en inglés a su equivalente en español. Reemplaza cada nombre de día y mes utilizando arrays predefinidos que mapean los valores de inglés a español.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class StringMethods {
>         +showString(string $srt) : string
>         +toCamelCase(string $string) : string
>         +dateToString($fecha)
>         +toSpanis($fecha)
>     }
>     HeaderComponent ..|> StringMethods
>     MainComponent ..|> StringMethods
>     MenuItemComponent ..|> StringMethods
>     MenuComponent ..|> StringMethods
> ```
> 
> ### Cómo encaja
> 
> La clase `StringMethods` se utiliza como un **trait** por varias clases componentes del framework **Lego**, incluyendo `HeaderComponent`, `MainComponent`, `MenuItemComponent` y `MenuComponent`. Estas clases heredan los métodos de `StringMethods` para realizar operaciones de manipulación y formateo de strings de manera consistente. La inclusión de estos métodos en un trait permite que el código sea más modular, facilitando la reutilización y mantenimiento del sistema.

## 👥 Es referenciado por

- [[header-component|HeaderComponent]] *(uses_trait)*
- [[main-component|MainComponent]] *(uses_trait)*
- [[menu-component|MenuComponent]] *(uses_trait)*
- [[menu-item-component|MenuItemComponent]] *(uses_trait)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.