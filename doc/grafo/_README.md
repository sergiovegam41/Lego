# Grafo del Código

Este folder contiene una representación 100% derivada del código fuente PHP del proyecto. Cada archivo en `nodos/` es una clase, interface, trait o enum del codebase.

> [!warning] Generado automáticamente
> No editar archivos de este folder manualmente. Cualquier cambio se perderá al re-generar con `php lego docs:graph`.

## Cómo usarlo

1. Abrí `nodos/` en Obsidian
2. Activá el **Graph View** (`Ctrl+G`)
3. Filtrá por tags:
   - `tag:#grafo/tipo/component` — solo componentes
   - `tag:#grafo/capa/app-models` — solo modelos
   - `tag:#grafo/atributo/ApiCrudResource` — clases con CRUD automático

## Tipos de Relaciones Capturadas

| Tipo | Significado |
|------|-------------|
| `extends` | Herencia entre clases |
| `implements` | Interfaces implementadas |
| `uses_trait` | Traits usados |
| `attribute` | Atributos declarativos (#[ApiComponent], etc.) |
| `instantiates` | `new ClassName()` |
| `static_call` | `ClassName::method()` |
| `const_fetch` | `ClassName::CONST` |
| `type_hint` | Type hints en parámetros |
| `returns` | Tipos de retorno |

## Backup automático

Antes de cada regeneración se hace un backup del estado anterior en `.backup/`. Solo se mantiene **el último backup** — cada run lo sobrescribe.

Si querés revertir:
```bash
# desde la raíz del proyecto
rm -rf doc/grafo/nodos doc/grafo/_README.md doc/grafo/_stats.md
cp -r doc/grafo/.backup/* doc/grafo/
```

## Re-generar

```bash
php lego docs:graph                        # solo grafo
php lego docs:graph --with-descriptions    # + descripciones cortas (LLM)
php lego docs:graph --with-deep-analysis   # + análisis profundo + diagramas
```