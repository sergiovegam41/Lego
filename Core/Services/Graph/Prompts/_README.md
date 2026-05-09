# Prompts del LLM (skills)

Esta carpeta contiene los **system prompts** que el comando `php lego docs:graph` envía al LLM para generar contenido estructurado sobre el código.

Cada archivo `.md` define un "skill" — instrucciones específicas, formato esperado, ejemplos y reglas para una tarea concreta.

## Skills disponibles

| Archivo | Usado por | Output esperado |
|---------|-----------|-----------------|
| [short-description.md](short-description.md) | `--with-descriptions` | Una oración técnica describiendo la responsabilidad funcional |
| [deep-analysis.md](deep-analysis.md) | `--with-deep-analysis` | Análisis técnico profundo en markdown con diagrama Mermaid opcional |

## Por qué externalizar

Tener los prompts en archivos `.md` (no en strings PHP) permite:

1. **Iterar el prompt sin tocar código** — editar el `.md` y volver a correr
2. **Versionar los cambios en git** con diffs legibles
3. **Documentar la intención** de cada prompt como un humano puede leerla
4. **Compartir convenciones** entre el equipo (cualquier dev puede mejorar un prompt)

## Cómo se cargan

`PromptLibrary::load(string $skillName): string` lee el archivo correspondiente y retorna su contenido como string. El provider lo pasa como `system` message al LLM.

## Convenciones para escribir skills

- Empezar con la **misión clara** en una sola oración
- Definir **formato de salida** con ejemplos
- Listar **reglas estrictas** (qué hacer / qué NO hacer)
- Si aplica, mostrar **ejemplos de output bueno y malo**
- Cerrar con la **estructura exacta** que se espera

El usuario message acompaña con los datos concretos (nombre, código, contexto) — el system message tiene las reglas.
