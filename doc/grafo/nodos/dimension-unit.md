---
tipo: enum
capa: core
namespace: Core\Types
archivo: Core/Types/DimensionUnit.php
loc: 38
deps: 0
dependents: 2
responsabilidad: Define las unidades de medida permitidas para dimensiones de componentes, incluyendo pixels, porcentaje, flex y auto.
tags:
  - grafo
  - grafo/tipo/enum
  - grafo/capa/core
---
# DimensionUnit

`Core\Types\DimensionUnit`

📁 [Core/Types/DimensionUnit.php](../../../Core/Types/DimensionUnit.php)

> [!abstract] Responsabilidad
> Define las unidades de medida permitidas para dimensiones de componentes, incluyendo pixels, porcentaje, flex y auto.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `DimensionUnit` es un enumerado (`enum`) que define las unidades de medida permitidas para dimensiones de componentes en el framework PHP Lego. Su principal objetivo es proporcionar una abstracción clara y controlada sobre cómo se especifican los tamaños de los componentes, asegurando que solo se utilicen unidades válidas y estandarizadas. Esto ayuda a mantener la consistencia y evitar errores comunes relacionados con las dimensiones incorrectas o incompatibles.
> 
> ### Métodos principales
> 
> Aunque `DimensionUnit` es un enumerado y no contiene métodos propiamente dichos, sus **valores** son los elementos clave que definen su funcionalidad:
> 
> 1. **PIXELS**: Representa unidades de medida fijas absolutas en píxeles.
> 2. **PERCENT**: Define tamaños relativos al contenedor padre, expresados como porcentaje.
> 3. **FLEX**: Permite que los componentes se expandan o contraigan según el espacio disponible.
> 4. **AUTO**: Establece un tamaño automático basado en el contenido del componente.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     enum DimensionUnit {
>         PIXELS
>         PERCENT
>         FLEX
>         AUTO
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `DimensionUnit` se utiliza para definir y validar las unidades de medida en el sistema. Aunque no está extendida, implementada ni usada como trait por otras clases, su presencia es crucial para asegurar que todas las dimensiones de los componentes sean consistentes y correctas. Esta enumeración actúa como un conjunto de constantes predefinidas que se utilizan en diferentes partes del sistema donde se requiere especificar tamaños de componentes, garantizando una coherencia en la definición de estos valores.

## 👥 Es referenciado por

- [[dimension-value|DimensionValue]] *(const_fetch, type_hint)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.