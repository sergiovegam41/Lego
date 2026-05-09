---
tipo: class
capa: core-components
namespace: Core\Components\Table
archivo: Core/Components/Table/TableConfig.php
loc: 280
deps: 2
dependents: 1
responsabilidad: "Define y encapsula la configuración de un componente de tabla, detectando automáticamente parámetros desde modelos decorados con #[ApiGetResource] o permitiendo una configuración manual."
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-components
---
# TableConfig

`Core\Components\Table\TableConfig`

📁 [Core/Components/Table/TableConfig.php](../../../Core/Components/Table/TableConfig.php)

> [!abstract] Responsabilidad
> Define y encapsula la configuración de un componente de tabla, detectando automáticamente parámetros desde modelos decorados con #[ApiGetResource] o permitiendo una configuración manual.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `TableConfig` existe para encapsular y facilitar la configuración de componentes de tabla (`TableComponent`) en el framework Lego. Su principal objetivo es simplificar la creación de tablas dinámicas al permitir una configuración automática basada en modelos decorados con el atributo `#[ApiGetResource]`. Esto reduce la cantidad de código boilerplate necesario y hace que la integración de nuevas tablas sea más intuitiva y rápida.
> 
> ### Métodos principales
> 
> 1. **fromModel**: Este método crea una instancia de `TableConfig` a partir de un modelo decorado con el atributo `#[ApiGetResource]`. Verifica que la clase del modelo exista y obtiene la configuración del atributo para establecer propiedades como el endpoint API, tipo de paginación, cantidad por página, campos ordenables, filtrables y buscables.
> 
> 2. **fromManual**: Este método permite crear una instancia de `TableConfig` manualmente, sin depender de un modelo. Aunque actualmente no está implementado completamente, proporciona la opción para configurar tablas que no siguen el patrón automático basado en modelos.
> 
> 3. **getApiEndpoint**: Retorna el endpoint completo de la API para el modelo. Asegura que el endpoint tenga el prefijo `/api/` si no lo tiene ya.
> 
> 4. **getPaginationType**: Obtiene el tipo de paginación configurado (`offset`, `cursor`, `page`). Si no está especificado, retorna `offset` por defecto.
> 
> 5. **toArray**: Convierte la configuración de la tabla en un array asociativo, lo que facilita su paso a JavaScript para ser utilizado en el frontend.
> 
> 6. **toJson**: Retorna la configuración de la tabla como una cadena JSON, ideal para ser consumida por scripts del lado del cliente.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class TableConfig {
>         +fromModel(modelClass: string, componentId: string): self
>         +fromManual(apiEndpoint: string, componentId: string, options: array): self
>         +getApiEndpoint(): string
>         +getPaginationType(): string
>         +getPerPage(): int
>         +toArray(): array
>         +toJson(): string
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `TableConfig` se integra como parte del sistema de componentes de tabla en el framework Lego. Su principal conexión es con los modelos decorados con `#[ApiGetResource]`, que proporcionan la configuración necesaria para generar tablas dinámicas. Aunque no está extendida, implementada ni usada como trait por otras clases, `TableConfig` se utiliza directamente para crear instancias de configuración que luego son utilizadas por el componente de tabla (`TableComponent`) para renderizar y gestionar la interfaz de usuario.

## 🔗 Constantes referenciadas

- [[api-get-resource|ApiGetResource]]

## 📥 Type hints (parámetros)

- [[api-get-resource|ApiGetResource]]

## 👥 Es referenciado por

- [[table-component|TableComponent]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.