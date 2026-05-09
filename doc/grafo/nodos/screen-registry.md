---
tipo: class
capa: core-registry
namespace: Core\Registry
archivo: Core/Registry/ScreenRegistry.php
loc: 217
deps: 1
dependents: 1
responsabilidad: Registra y gestiona la metadata de todos los screens LEGO, proporcionando métodos para obtener estructuras de menú y verificar visibilidad.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-registry
---
# ScreenRegistry

`Core\Registry\ScreenRegistry`

📁 [Core/Registry/ScreenRegistry.php](../../../Core/Registry/ScreenRegistry.php)

> [!abstract] Responsabilidad
> Registra y gestiona la metadata de todos los screens LEGO, proporcionando métodos para obtener estructuras de menú y verificar visibilidad.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ScreenRegistry` es un componente central de la arquitectura del framework LEGO, diseñado para gestionar y registrar todos los "screens" (pantallas o vistas) disponibles en el sistema. Su principal objetivo es proporcionar una fuente única y centralizada de información sobre estos screens, facilitando su registro, acceso y uso en diferentes partes del sistema, especialmente en la generación de menús.
> 
> La necesidad de esta clase surge de la complejidad que puede surgir al manejar múltiples pantallas en un dashboard orientado a componentes. Sin una estructura organizada para registrar y acceder a estos screens, sería difícil mantener una visión clara de todas las pantallas disponibles y sus propiedades, como visibilidad, jerarquía y metadatos asociados.
> 
> ### Métodos principales
> 
> 1. **register(string $screenClass): void**
>    - Registra un nuevo screen en el registro central. Asegura que la clase proporcionada implemente `ScreenInterface`, lo que garantiza que tenga los métodos necesarios para obtener su ID y metadata. Luego, almacena la clase en un array estático `$screens` utilizando el ID del screen como clave.
> 
> 2. **getMenuStructure(): array**
>    - Genera una estructura jerárquica de menú basada en todos los screens registrados. Comienza por identificar los screens raíz (sin parent) y luego ordena estos screens según su propiedad `order`. Para cada screen, construye un item de menú utilizando el método privado `buildMenuItem`, que también se encarga de agregar sus hijos si existen.
> 
> 3. **getVisible(): array**
>    - Retorna solo los screens que están marcados como visibles (`visible: true`). Esto es útil para generar menús que solo muestren las pantallas disponibles al usuario actual, según su rol o permisos.
> 
> 4. **buildMenuItem(array $meta, array $allScreens): array**
>    - Un método privado utilizado por `getMenuStructure` para construir un item de menú a partir de los metadatos de un screen y sus posibles hijos. Este método asegura que cada item de menú tenga todas las propiedades necesarias (`id`, `label`, `icon`, etc.) y, si hay hijos, los incluye recursivamente.
> 
> 5. **clear(): void**
>    - Un método útil para pruebas que limpia completamente el registro de screens y su metadata cacheada. Esto permite resetear el estado del registry entre diferentes ejecuciones de tests sin afectar otras partes del sistema.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ScreenRegistry {
>         +register(string $screenClass): void
>         +getMenuStructure(): array
>         +getVisible(): array
>         +buildMenuItem(array $meta, array $allScreens): array
>         +clear(): void
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `ScreenRegistry` se integra como una pieza clave en el sistema LEGO, especialmente en la generación y manejo de menús. Su rol centralizado permite que diferentes partes del sistema accedan a la información necesaria sobre los screens sin duplicar lógica o datos. Por ejemplo, tanto el componente responsable de generar el menú principal como cualquier otro módulo que necesite listar o verificar la disponibilidad de pantallas pueden utilizar `ScreenRegistry` para obtener esta información de manera consistente y actualizada.
> 
> Además, al implementar interfaces y asegurarse de que todas las clases registradas sean compatibles con `ScreenInterface`, `ScreenRegistry` garantiza una alta cohesión y flexibilidad en el sistema. Esto permite añadir nuevos screens sin modificar la lógica existente del registry, siempre y cuando cumplan con los requisitos definidos por la interfaz.
> 
> En resumen, `ScreenRegistry` actúa como un puente entre la definición de pantallas y su uso en diferentes partes del sistema, facilitando una gestión eficiente y centralizada de estos componentes esenciales.

## 🔗 Constantes referenciadas

- [[screen-interface|ScreenInterface]]

## 👥 Es referenciado por

- [[menu-structure|MenuStructure]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.