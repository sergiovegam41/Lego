---
tipo: class
capa: core
namespace: Core\Exceptions
archivo: Core/Exceptions/ComponentIdCollisionException.php
loc: 31
deps: 0
dependents: 1
responsabilidad: Lanza una excepción cuando dos componentes intentan usar el mismo ID.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# ComponentIdCollisionException

`Core\Exceptions\ComponentIdCollisionException`

📁 [Core/Exceptions/ComponentIdCollisionException.php](../../../Core/Exceptions/ComponentIdCollisionException.php)

> [!abstract] Responsabilidad
> Lanza una excepción cuando dos componentes intentan usar el mismo ID.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ComponentIdCollisionException` existe para resolver un problema fundamental en el sistema de componentes del framework Lego: asegurar que cada componente tenga un identificador único (`ID`). En un entorno donde múltiples componentes pueden registrarse dinámicamente, es crucial evitar colisiones de IDs para prevenir comportamientos inesperados o conflictos en la interfaz de usuario. Esta excepción se lanza cuando el sistema detecta que dos componentes intentan usar el mismo ID, lo que interrumpe el flujo normal de operaciones y obliga al desarrollador a resolver el conflicto.
> 
> ### Métodos principales
> 
> - **`__construct(string $message = "", int $code = 0, ?\Throwable $previous = null)`**: Este es el constructor estándar de la excepción. Permite inicializar la excepción con un mensaje personalizado, un código de error y una excepción previa (si existe). Este método heredado de `RuntimeException` se utiliza para proporcionar detalles específicos sobre la colisión detectada.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ComponentIdCollisionException {
>         +__construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `ComponentIdCollisionException` se utiliza exclusivamente por la clase `ComponentRegistry`. Cuando el registro de componentes detecta que dos componentes intentan usar el mismo ID, lanza esta excepción para informar al desarrollador del conflicto. Este mecanismo asegura que los IDs de los componentes sean únicos en tiempo de ejecución, lo cual es crucial para mantener la integridad y la funcionalidad del sistema de componentes.

## 👥 Es referenciado por

- [[component-registry|ComponentRegistry]] *(instantiates)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.