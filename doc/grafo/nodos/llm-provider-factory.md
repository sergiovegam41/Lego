---
tipo: class
capa: core-services
namespace: Core\Services\Graph
archivo: Core/Services/Graph/LlmProviderFactory.php
loc: 50
deps: 5
dependents: 1
responsabilidad: Crea y configura instancias de proveedores de LLM basadas en variables de entorno, validando requisitos específicos para cada uno.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-services
---
# LlmProviderFactory

`Core\Services\Graph\LlmProviderFactory`

📁 [Core/Services/Graph/LlmProviderFactory.php](../../../Core/Services/Graph/LlmProviderFactory.php)

> [!abstract] Responsabilidad
> Crea y configura instancias de proveedores de LLM basadas en variables de entorno, validando requisitos específicos para cada uno.

## 🆕 Instancia

- [[groq-provider|GroqProvider]]
- [[ollama-provider|OllamaProvider]]

## 📤 Tipos de retorno

- [[groq-provider|GroqProvider]]
- [[llm-provider|LlmProvider]]
- [[ollama-provider|OllamaProvider]]

## 👥 Es referenciado por

- [[docs-graph-command|DocsGraphCommand]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.