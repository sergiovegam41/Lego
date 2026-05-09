---
tipo: class
capa: core
namespace: Core\Types
archivo: Core/Types/DimensionValue.php
loc: 178
deps: 2
dependents: 6
responsabilidad: Define y gestiona valores de dimensión con unidades explícitas, asegurando la consistencia y validación de tipos en aplicaciones web.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# DimensionValue

`Core\Types\DimensionValue`

📁 [Core/Types/DimensionValue.php](../../../Core/Types/DimensionValue.php)

> [!abstract] Responsabilidad
> Define y gestiona valores de dimensión con unidades explícitas, asegurando la consistencia y validación de tipos en aplicaciones web.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `DimensionValue` existe para representar valores de dimensión con unidades explícitas, asegurando la consistencia y validación de unidades y rangos. Esto es crucial en un sistema de dashboards donde las dimensiones de los componentes deben mantenerse coherentes y evitar errores comunes como mezclar unidades (por ejemplo, pixels vs porcentajes). Además, facilita la conversión a diferentes formatos utilizados por bibliotecas externas como AG Grid y CSS, lo que simplifica el desarrollo y mantiene un alto nivel de control sobre los estilos aplicados.
> 
> ### Métodos principales
> 
> 1. **`px(float $value): self`**
>    - Crea una instancia de `DimensionValue` con la unidad en pixels.
>    - Valida que el valor no sea negativo, lanzando una excepción si lo es.
> 
> 2. **`percent(float $value): self`**
>    - Crea una instancia de `DimensionValue` con la unidad en porcentaje.
>    - Valida que el valor esté entre 0 y 100, lanzando una excepción si no lo está.
> 
> 3. **`flex(float $grow, float $shrink = 1, string|int $basis = 'auto'): self`**
>    - Crea una instancia de `DimensionValue` con la unidad flex.
>    - Valida que los factores de crecimiento y encogimiento no sean negativos, lanzando excepciones si lo son.
> 
> 4. **`auto(): self`**
>    - Crea una instancia de `DimensionValue` con la unidad automática (basada en contenido).
> 
> 5. **`toAgGrid(): array`**
>    - Convierte el valor a un formato compatible con AG Grid.
>    - Utiliza `match` para determinar el formato correcto según la unidad.
> 
> 6. **`toCss(): string`**
>    - Convierte el valor a una cadena CSS válida.
>    - Utiliza `match` para generar la representación adecuada según la unidad.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class DimensionValue {
>         <<readonly>>
>         +float value
>         +DimensionUnit unit
>         +array params
>         +px(float $value): self
>         +percent(float $value): self
>         +flex(float $grow, float $shrink, string|int $basis): self
>         +auto(): self
>         +toAgGrid(): array
>         +toCss(): string
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `DimensionValue` se integra en el sistema al proporcionar una abstracción robusta para manejar valores de dimensión con unidades explícitas. Esto es especialmente útil en componentes que requieren estilos consistentes y validados, como tablas o grillas (`TableComponent`), donde las dimensiones pueden variar según la configuración del usuario o los requisitos de diseño. Además, su interfaz permite una fácil integración con bibliotecas externas como AG Grid, facilitando la generación de configuraciones de estilo adecuadas y manteniendo un alto nivel de control sobre el renderizado final.

## 🔗 Constantes referenciadas

- [[dimension-unit|DimensionUnit]]

## 📥 Type hints (parámetros)

- [[dimension-unit|DimensionUnit]]

## 👥 Es referenciado por

- [[auth-groups-config-component|AuthGroupsConfigComponent]] *(static_call)*
- [[column-dto|ColumnDto]] *(type_hint)*
- [[example-crud-component|ExampleCrudComponent]] *(static_call)*
- [[roles-config-component|RolesConfigComponent]] *(static_call)*
- [[tools-crud-component|ToolsCrudComponent]] *(static_call)*
- [[users-config-component|UsersConfigComponent]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.