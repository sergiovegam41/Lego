---
tipo: command
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/DocsGraphCommand.php
loc: 311
deps: 10
dependents: 0
responsabilidad: Genera un grafo del código fuente como notas de Obsidian con wikilinks, incluyendo descripciones y análisis profundos utilizando LLMs.
tags:
  - grafo
  - grafo/tipo/command
  - grafo/capa/core-commands
---
# DocsGraphCommand

`Core\Commands\DocsGraphCommand`

📁 [Core/Commands/DocsGraphCommand.php](../../../Core/Commands/DocsGraphCommand.php)

> [!abstract] Responsabilidad
> Genera un grafo del código fuente como notas de Obsidian con wikilinks, incluyendo descripciones y análisis profundos utilizando LLMs.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `DocsGraphCommand` existe para generar un grafo detallado del código fuente de un proyecto, representándolo como notas de Obsidian con wikilinks. Este grafo incluye descripciones y análisis profundos generados por modelos de lenguaje grande (LLMs), lo que facilita la comprensión y documentación del sistema. La necesidad de esta clase surge de la complejidad creciente de los proyectos modernos, donde es crucial tener una representación visual y detallada de las relaciones entre diferentes componentes.
> 
> ### Métodos principales
> 
> 1. **`execute()`**: Este método es el punto de entrada principal del comando. Se encarga de configurar y ejecutar todo el proceso de generación del grafo. Maneja opciones como el ámbito (`scope`) del análisis, la ruta de salida (`output`), y las banderas para incluir descripciones y análisis profundos.
> 
> 2. **`generateDescriptionsLive()`**: Este método se ocupa de generar descripciones cortas para cada nodo en el grafo utilizando un proveedor de LLMs. Utiliza una caché para evitar solicitudes innecesarias a la API del modelo, lo que mejora la eficiencia y reduce los costos.
> 
> 3. **`generateDeepAnalysesLive()`**: Este método realiza análisis profundos de los nodos más importantes en el grafo. Utiliza un analizador (`DeepAnalyzer`) para generar descripciones detalladas y diagramas Mermaid, validando estos últimos antes de su renderizado.
> 
> 4. **`progress()`**: Este método muestra una barra de progreso en la consola, indicando el avance del proceso de generación de descripciones y análisis.
> 
> 5. **`checkParserInstalled()`**: Verifica si la dependencia `nikic/php-parser` está instalada en el proyecto. Si no lo está, emite un error y proporciona instrucciones para su instalación.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant DocsGraphCommand as Command
>     participant GraphScanner as Scanner
>     participant ObsidianRenderer as Renderer
>     participant LlmProviderFactory as Factory
>     participant DescriptionCache as Cache
>     participant DeepAnalyzer as Analyzer
>     participant MermaidValidator as Validator
> 
>     Client->>DocsGraphCommand: execute()
>     DocsGraphCommand->>DocsGraphCommand: checkParserInstalled()
>     DocsGraphCommand->>GraphScanner: scan()
>     GraphScanner-->>DocsGraphCommand: graph
>     DocsGraphCommand->>ObsidianRenderer: prepare()
>     DocsGraphCommand->>LlmProviderFactory: create()
>     LlmProviderFactory-->>DocsGraphCommand: provider
>     DocsGraphCommand->>DescriptionCache: get()
>     DocsGraphCommand->>DocsGraphCommand: generateDescriptionsLive()
>     DocsGraphCommand->>DocsGraphCommand: generateDeepAnalysesLive()
>     DocsGraphCommand->>ObsidianRenderer: finalize()
> ```
> 
> ### Cómo encaja
> 
> La clase `DocsGraphCommand` se integra como un comando dentro del sistema de comandos de la aplicación, extendiendo la funcionalidad de `CoreCommand`. Su rol principal es facilitar la generación de documentación detallada y visual del código fuente, lo que complementa otras herramientas y procesos de desarrollo. La clase depende de varios servicios como `GraphScanner`, `ObsidianRenderer`, `LlmProviderFactory`, `DescriptionCache`, `DeepAnalyzer` y `MermaidValidator`, que trabajan en conjunto para proporcionar una representación completa y precisa del sistema.

## 🔼 Hereda de

- [[core-command|CoreCommand]]

## 🆕 Instancia

- [[deep-analyzer|DeepAnalyzer]]
- [[description-cache|DescriptionCache]]
- [[graph-scanner|GraphScanner]]
- [[importance-criteria|ImportanceCriteria]]
- [[mermaid-validator|MermaidValidator]]
- [[obsidian-renderer|ObsidianRenderer]]

## ⚡ Llamadas estáticas

- [[llm-provider-factory|LlmProviderFactory]]

## 📥 Type hints (parámetros)

- [[graph|Graph]]
- [[obsidian-renderer|ObsidianRenderer]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.