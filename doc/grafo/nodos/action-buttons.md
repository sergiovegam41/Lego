---
tipo: class
capa: core
namespace: Core\Helpers
archivo: Core/Helpers/ActionButtons.php
loc: 254
deps: 0
dependents: 0
responsabilidad: Genera cellRenderers de botones de acción dinámicos para tablas, utilizando el sistema de componentes del framework Lego y optimizando la carga de assets.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# ActionButtons

`Core\Helpers\ActionButtons`

📁 [Core/Helpers/ActionButtons.php](../../../Core/Helpers/ActionButtons.php)

> [!abstract] Responsabilidad
> Genera cellRenderers de botones de acción dinámicos para tablas, utilizando el sistema de componentes del framework Lego y optimizando la carga de assets.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ActionButtons` existe para simplificar y optimizar la generación de botones de acción dinámicos en tablas del framework Lego. Antes, cada tabla requería una implementación detallada y repetitiva de HTML/CSS para los botones de acción, lo que llevaba a duplicación de código y dificultades en el mantenimiento. La creación de `ActionButtons` resuelve este problema al proporcionar un método centralizado y flexible para generar estos botones utilizando componentes del framework Lego, lo que reduce la cantidad de código necesario y mejora la consistencia visual.
> 
> ### Métodos principales
> 
> 1. **dynamic(array $actions, array $config = []): string**
>    - Genera una función JavaScript que se utiliza como `cellRenderer` en tablas. Esta función crea botones de acción dinámicos utilizando el sistema de componentes del framework Lego.
>    - Utiliza la configuración predefinida para cada acción y permite personalizaciones adicionales a través de parámetros opcionales.
> 
> 2. **static(array $actions, array $config = []): string**
>    - Similar al método `dynamic`, pero genera botones estáticos sin utilizar componentes dinámicos. Este método es útil cuando se necesita evitar solicitudes asíncronas o no se requiere batch rendering.
> 
> 3. **generateButtonHtml(string $icon, string $variant, string $title, string $function, string $size): string**
>    - Genera el HTML inline para un botón individual basado en los parámetros proporcionados. Este método es privado y utilizado internamente por los métodos `dynamic` y `static`.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ActionButtons {
>         +dynamic(array $actions, array $config = []): string
>         +static(array $actions, array $config = []): string
>         -generateButtonHtml(string $icon, string $variant, string $title, string $function, string $size): string
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `ActionButtons` se integra en el sistema Lego como una herramienta de ayuda para facilitar la creación de tablas interactivas. Al utilizar componentes dinámicos del framework, optimiza el renderizado y reduce la duplicación de código. Esta clase no tiene relaciones directas con otras clases mencionadas, ya que se utiliza principalmente como un helper estático en el contexto de la generación de vistas para tablas.

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.