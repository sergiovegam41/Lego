---
tipo: class
capa: core-components
namespace: Core\Components
archivo: Core/Components/ComponentRegistry.php
loc: 210
deps: 2
dependents: 2
responsabilidad: Registra y gestiona componentes dinámicos, validando colisiones de IDs y proporcionando métodos para renderizar individualmente o por lotes.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-components
---
# ComponentRegistry

`Core\Components\ComponentRegistry`

📁 [Core/Components/ComponentRegistry.php](../../../Core/Components/ComponentRegistry.php)

> [!abstract] Responsabilidad
> Registra y gestiona componentes dinámicos, validando colisiones de IDs y proporcionando métodos para renderizar individualmente o por lotes.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ComponentRegistry` es un registro centralizado de componentes dinámicos que juega un papel crucial en el framework Lego. Su principal objetivo es mantener una lista ordenada y controlada de todos los componentes disponibles, asegurando que cada componente tenga un identificador único (`ID`). Además, esta clase se encarga de validar colisiones de IDs a tiempo de ejecución, lo cual ayuda a prevenir errores comunes en la gestión de componentes. También proporciona métodos para renderizar componentes individualmente o por lotes, facilitando su uso en diferentes partes de la aplicación.
> 
> ### Métodos principales
> 
> 1. **register(string $id, string $class): void**
>    - Registra un componente en el registro global con un ID único y una clase asociada.
>    - Valida que no haya colisiones de IDs antes de registrar el nuevo componente.
>    - Lanza una excepción `ComponentIdCollisionException` si se detecta una colisión.
> 
> 2. **render(string $id, array $params): string**
>    - Renderiza un componente específico con los parámetros proporcionados.
>    - Verifica que el componente exista y que implemente la interfaz `DynamicComponentInterface`.
>    - Crea una instancia del componente y llama al método `renderWithParams` para generar el HTML.
> 
> 3. **renderBatch(string $id, array $paramsList): array**
>    - Renderiza múltiples instancias de un mismo componente en un solo lote.
>    - Limita el tamaño del lote a 100 elementos para prevenir abusos y optimizar el rendimiento.
>    - Maneja errores individuales dentro del lote, logueando los problemas pero no interrumpiendo el proceso global.
> 
> 4. **isRegistered(string $id): bool**
>    - Verifica si un componente específico está registrado en el registro global.
> 
> 5. **getClass(string $id): ?string**
>    - Retorna la clase asociada a un ID de componente, o `null` si el componente no existe.
> 
> 6. **getAll(): array**
>    - Retorna todos los componentes registrados en formato de mapeo de ID a clase.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ComponentRegistry {
>         +register(id: string, class: string): void
>         +render(id: string, params: array): string
>         +renderBatch(id: string, paramsList: array): array
>         +isRegistered(id: string): bool
>         +getClass(id: string): ?string
>         +getAll(): array
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `ComponentRegistry` se integra como un componente central en el sistema Lego. Se utiliza para registrar y gestionar todos los componentes dinámicos, asegurando que cada uno tenga un identificador único y funcione correctamente. Los métodos de renderizado permiten a otras partes del sistema solicitar la representación HTML de estos componentes de manera eficiente, ya sea individualmente o en lotes. Esta clase es fundamental para mantener el orden y la integridad en la gestión de componentes, facilitando su uso y reutilización en diferentes partes de la aplicación.

## 🆕 Instancia

- [[component-id-collision-exception|ComponentIdCollisionException]]

## 🔗 Constantes referenciadas

- [[dynamic-component-interface|DynamicComponentInterface]]

## 👥 Es referenciado por

- [[components-controller|ComponentsController]] *(static_call)*
- [[icon-button-component|IconButtonComponent]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.