<?php

namespace Core\Services\Graph;

class ObsidianRenderer
{
    private const RELATIONSHIP_LABELS = [
        'extends'      => '🔼 Hereda de',
        'implements'   => '📐 Implementa',
        'uses_trait'   => '🧩 Usa traits',
        'attribute'    => '🏷️ Atributos declarativos',
        'instantiates' => '🆕 Instancia',
        'static_call'  => '⚡ Llamadas estáticas',
        'const_fetch'  => '🔗 Constantes referenciadas',
        'type_hint'    => '📥 Type hints (parámetros)',
        'returns'      => '📤 Tipos de retorno',
    ];

    private array $descriptions = [];
    private array $deepAnalyses = [];

    /**
     * Set de nodeIds "vivos" — nodos que ya existen como archivo en nodos/.
     * Wikilinks solo se renderizan a nodos vivos. A medida que aparecen nodos
     * nuevos, los vecinos vivos se reescriben para incluir las nuevas conexiones.
     */
    private array $liveNodes = [];

    public function __construct(
        private readonly Graph $graph,
        private readonly string $outputDir
    ) {}

    /**
     * Reemplaza el backup anterior con el estado actual del output.
     * Solo se mantiene UN backup — el inmediatamente anterior.
     *
     * @return string|null  Ruta del backup creado, o null si no había nada que backupear.
     */
    public function backupExisting(): ?string
    {
        $nodesDir = $this->outputDir . '/nodos';
        if (!is_dir($nodesDir) || empty(glob($nodesDir . '/*.md'))) {
            return null;
        }

        $backupDir = $this->outputDir . '/.backup';

        if (is_dir($backupDir)) {
            $this->deleteDirectory($backupDir);
        }
        @mkdir($backupDir, 0755, true);

        $this->copyDirectory($nodesDir, $backupDir . '/nodos');

        foreach (['_README.md', '_stats.md'] as $file) {
            $src = $this->outputDir . '/' . $file;
            if (file_exists($src)) {
                copy($src, $backupDir . '/' . $file);
            }
        }

        return $backupDir;
    }

    /**
     * Prepara el output: wipea nodos/ y escribe README/stats.
     * NO escribe ningún nodo — Obsidian ve el folder vacío inicialmente.
     *
     * Los nodos se escriben después, uno por uno, vía updateDescription()
     * o renderAllNodes() (este último solo si no hay LLM en el run).
     */
    public function prepare(): void
    {
        $this->ensureCleanOutput();
        $this->writeReadme();
        $this->writeStats();
    }

    /**
     * Escribe TODOS los nodos de golpe. Solo usar cuando NO hay LLM
     * (porque si hay LLM, los nodos aparecen uno a uno via updateDescription).
     */
    public function renderAllNodes(): array
    {
        // Marca todos como vivos antes de escribir, así los wikilinks renderizan completos.
        foreach ($this->graph->nodes() as $id => $meta) {
            $this->liveNodes[$id] = true;
        }

        $written = [];
        foreach ($this->graph->nodes() as $id => $meta) {
            $this->writeNode($id, $meta);
            $written[] = $id;
        }
        return $written;
    }

    /**
     * Actualiza la descripción corta de un nodo, lo marca como vivo,
     * y escribe su archivo .md. También reescribe los vecinos vivos
     * para que incluyan la nueva conexión hacia/desde este nodo.
     */
    public function updateDescription(string $nodeId, string $description): void
    {
        $this->descriptions[$nodeId] = $description;
        $isNewlyLive = !isset($this->liveNodes[$nodeId]);
        $this->liveNodes[$nodeId] = true;

        $node = $this->graph->getNode($nodeId);
        if (!$node) {
            return;
        }
        $this->writeNode($nodeId, $node);

        if ($isNewlyLive) {
            $this->rewriteLiveNeighbors($nodeId);
        }
    }

    /**
     * Actualiza el análisis profundo de un nodo y reescribe su archivo.
     * Si el nodo aún no era "vivo", también marca sus vecinos para reescritura.
     */
    public function updateDeepAnalysis(string $nodeId, string $analysis): void
    {
        $this->deepAnalyses[$nodeId] = $analysis;
        $isNewlyLive = !isset($this->liveNodes[$nodeId]);
        $this->liveNodes[$nodeId] = true;

        $node = $this->graph->getNode($nodeId);
        if (!$node) {
            return;
        }
        $this->writeNode($nodeId, $node);

        if ($isNewlyLive) {
            $this->rewriteLiveNeighbors($nodeId);
        }
    }

    /**
     * Reescribe todos los nodos vivos que tienen alguna relación con $changedId.
     * Esto hace que las nuevas conexiones aparezcan en sus archivos.
     */
    private function rewriteLiveNeighbors(string $changedId): void
    {
        $neighbors = [];
        foreach ($this->graph->getOutgoingEdges($changedId) as $edge) {
            if (isset($this->liveNodes[$edge['to']])) {
                $neighbors[$edge['to']] = true;
            }
        }
        foreach ($this->graph->getIncomingEdges($changedId) as $edge) {
            if (isset($this->liveNodes[$edge['from']])) {
                $neighbors[$edge['from']] = true;
            }
        }
        unset($neighbors[$changedId]);

        foreach (array_keys($neighbors) as $neighborId) {
            $node = $this->graph->getNode($neighborId);
            if ($node) {
                $this->writeNode($neighborId, $node);
            }
        }
    }

    /**
     * Reescribe README y stats con los datos finales del run.
     */
    public function finalize(): void
    {
        $this->writeReadme();
        $this->writeStats();
    }

    private function copyDirectory(string $src, string $dst): void
    {
        @mkdir($dst, 0755, true);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $target = $dst . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                @mkdir($target, 0755, true);
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }
        @rmdir($dir);
    }

    private function ensureCleanOutput(): void
    {
        $nodesDir = $this->outputDir . '/nodos';
        if (is_dir($nodesDir)) {
            $files = glob($nodesDir . '/*.md');
            foreach ($files as $f) {
                @unlink($f);
            }
        } else {
            @mkdir($nodesDir, 0755, true);
        }

        if (!is_dir($this->outputDir)) {
            @mkdir($this->outputDir, 0755, true);
        }
    }

    private function writeNode(string $id, array $meta): string
    {
        // Solo conexiones a/desde nodos vivos. Si todavía no aparecieron,
        // no se renderizan los wikilinks — se actualizarán cuando aparezcan.
        $outgoing = array_filter(
            $this->graph->getOutgoingEdges($id),
            fn($e) => isset($this->liveNodes[$e['to']])
        );
        $incoming = array_filter(
            $this->graph->getIncomingEdges($id),
            fn($e) => isset($this->liveNodes[$e['from']])
        );

        $byType = [];
        foreach ($outgoing as $edge) {
            $byType[$edge['type']][] = $edge['to'];
        }

        $depsCount       = count($outgoing);
        $dependentsCount = count($incoming);
        $description     = $this->descriptions[$id] ?? null;

        $frontmatter = $this->renderFrontmatter($meta, $depsCount, $dependentsCount, $description);
        $body        = $this->renderBody($id, $meta, $byType, $incoming);

        $path = $this->outputDir . '/nodos/' . $id . '.md';
        file_put_contents($path, $frontmatter . "\n" . $body);
        return $path;
    }

    private function renderFrontmatter(array $meta, int $depsCount, int $dependentsCount, ?string $description = null): string
    {
        $tags = [
            'grafo',
            'grafo/tipo/' . $meta['type'],
            'grafo/capa/' . $meta['layer'],
        ];

        $attributes = $meta['attributes'] ?? [];
        $shortAttrs = array_map(fn($a) => $this->shortName($a), $attributes);
        foreach ($shortAttrs as $attr) {
            $tags[] = 'grafo/atributo/' . $attr;
        }

        $lines = [
            '---',
            'tipo: ' . $meta['type'],
            'capa: ' . $meta['layer'],
            'namespace: ' . ($meta['namespace'] ?: '(sin namespace)'),
            'archivo: ' . $meta['file'],
            'loc: ' . $meta['loc'],
            'deps: ' . $depsCount,
            'dependents: ' . $dependentsCount,
        ];

        if ($description !== null && $description !== '') {
            $lines[] = 'responsabilidad: ' . $this->yamlEscape($description);
        }

        if (!empty($shortAttrs)) {
            $lines[] = 'atributos:';
            foreach ($shortAttrs as $attr) {
                $lines[] = '  - ' . $attr;
            }
        }

        $lines[] = 'tags:';
        foreach ($tags as $tag) {
            $lines[] = '  - ' . $tag;
        }

        $lines[] = '---';

        return implode("\n", $lines);
    }

    private function renderBody(string $id, array $meta, array $byType, array $incoming): string
    {
        $sections = [];

        $sections[] = '# ' . $meta['short_name'];
        $sections[] = '';
        $sections[] = '`' . $meta['fqn'] . '`';
        $sections[] = '';
        $sections[] = '📁 [' . $meta['file'] . '](../../../' . $meta['file'] . ')';
        $sections[] = '';

        $description = $this->descriptions[$id] ?? null;
        if ($description !== null && $description !== '') {
            $sections[] = '> [!abstract] Responsabilidad';
            $sections[] = '> ' . $description;
            $sections[] = '';
        }

        $deep = $this->deepAnalyses[$id] ?? null;
        if ($deep !== null && $deep !== '') {
            $sections[] = '> [!example]- Análisis detallado';
            foreach (explode("\n", $deep) as $line) {
                $sections[] = '> ' . $line;
            }
            $sections[] = '';
        }

        foreach (self::RELATIONSHIP_LABELS as $type => $label) {
            if (empty($byType[$type])) {
                continue;
            }
            $sections[] = '## ' . $label;
            $sections[] = '';
            $unique = array_unique($byType[$type]);
            sort($unique);
            foreach ($unique as $targetId) {
                $targetMeta = $this->graph->getNode($targetId);
                $shortName  = $targetMeta['short_name'] ?? $targetId;
                $sections[] = '- [[' . $targetId . '|' . $shortName . ']]';
            }
            $sections[] = '';
        }

        if (!empty($incoming)) {
            $sections[] = '## 👥 Es referenciado por';
            $sections[] = '';
            $bySource = [];
            foreach ($incoming as $edge) {
                $bySource[$edge['from']][] = $edge['type'];
            }
            ksort($bySource);
            foreach ($bySource as $sourceId => $types) {
                $sourceMeta = $this->graph->getNode($sourceId);
                $shortName  = $sourceMeta['short_name'] ?? $sourceId;
                $typesText  = implode(', ', array_unique($types));
                $sections[] = '- [[' . $sourceId . '|' . $shortName . ']] *(' . $typesText . ')*';
            }
            $sections[] = '';
        }

        $sections[] = '---';
        $sections[] = '';
        $sections[] = '> [!info] Nota generada';
        $sections[] = '> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.';

        return implode("\n", $sections);
    }

    private function writeReadme(): void
    {
        $content = <<<MD
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
MD;

        file_put_contents($this->outputDir . '/_README.md', $content);
    }

    private function writeStats(): void
    {
        $stats = $this->graph->stats();

        $lines   = ['# Estadísticas del Grafo', ''];
        $lines[] = '> Generado el ' . date('Y-m-d H:i:s');
        $lines[] = '';
        $lines[] = '## Resumen';
        $lines[] = '';
        $lines[] = '- **Nodos totales:** ' . $stats['total_nodes'];
        $lines[] = '- **Aristas totales:** ' . $stats['total_edges'];
        $lines[] = '';

        $lines[] = '## Por tipo';
        $lines[] = '';
        $lines[] = '| Tipo | Cantidad |';
        $lines[] = '|------|----------|';
        arsort($stats['by_type']);
        foreach ($stats['by_type'] as $type => $count) {
            $lines[] = '| ' . $type . ' | ' . $count . ' |';
        }
        $lines[] = '';

        $lines[] = '## Por capa';
        $lines[] = '';
        $lines[] = '| Capa | Cantidad |';
        $lines[] = '|------|----------|';
        arsort($stats['by_layer']);
        foreach ($stats['by_layer'] as $layer => $count) {
            $lines[] = '| ' . $layer . ' | ' . $count . ' |';
        }
        $lines[] = '';

        $lines[] = '## Aristas por tipo';
        $lines[] = '';
        $lines[] = '| Relación | Cantidad |';
        $lines[] = '|----------|----------|';
        arsort($stats['edges_by_type']);
        foreach ($stats['edges_by_type'] as $type => $count) {
            $lines[] = '| ' . $type . ' | ' . $count . ' |';
        }
        $lines[] = '';

        $lines[] = '## Hubs (más referenciados)';
        $lines[] = '';
        $hubs = [];
        foreach ($this->graph->nodes() as $id => $meta) {
            $count = count($this->graph->getIncomingEdges($id));
            if ($count > 0) {
                $hubs[$id] = ['count' => $count, 'meta' => $meta];
            }
        }
        uasort($hubs, fn($a, $b) => $b['count'] <=> $a['count']);
        $hubs = array_slice($hubs, 0, 20, true);

        $lines[] = '| Clase | Veces referenciada |';
        $lines[] = '|-------|-------------------|';
        foreach ($hubs as $id => $info) {
            $lines[] = '| [[' . $id . '\|' . $info['meta']['short_name'] . ']] | ' . $info['count'] . ' |';
        }

        file_put_contents($this->outputDir . '/_stats.md', implode("\n", $lines));
    }

    private function shortName(string $fqn): string
    {
        $pos = strrpos($fqn, '\\');
        return $pos === false ? $fqn : substr($fqn, $pos + 1);
    }

    private function yamlEscape(string $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', $value);
        if (preg_match('/[:#"\'\\[\\]\\{\\}|>&!*%@`]/', $value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }
        return $value;
    }
}
