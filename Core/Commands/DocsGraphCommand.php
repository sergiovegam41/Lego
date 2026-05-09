<?php

namespace Core\Commands;

use Core\Services\Graph\DeepAnalyzer;
use Core\Services\Graph\DescriptionCache;
use Core\Services\Graph\Graph;
use Core\Services\Graph\GraphScanner;
use Core\Services\Graph\ImportanceCriteria;
use Core\Services\Graph\LlmProviderFactory;
use Core\Services\Graph\MermaidValidator;
use Core\Services\Graph\ObsidianRenderer;

class DocsGraphCommand extends CoreCommand
{
    protected string $name = 'docs:graph';
    protected string $description = 'Genera un grafo del código fuente como notas de Obsidian con wikilinks';
    protected string $signature = 'docs:graph [--scope=Core,App,components] [--output=doc/grafo] [--with-descriptions] [--with-deep-analysis] [--force-descriptions] [--force-deep]';

    public function execute(): bool
    {
        $projectRoot = realpath(__DIR__ . '/../../');
        if ($projectRoot === false) {
            $this->error("No se pudo determinar la raíz del proyecto");
            return false;
        }

        if (!$this->checkParserInstalled()) {
            return false;
        }

        $scope             = $this->option('scope', 'Core,App,components,Routes');
        $output            = $this->option('output', 'doc/grafo');
        $withDescriptions  = (bool) $this->option('with-descriptions', false);
        $withDeepAnalysis  = (bool) $this->option('with-deep-analysis', false);
        $forceDescriptions = (bool) $this->option('force-descriptions', false);
        $forceDeep         = (bool) $this->option('force-deep', false);

        if ($withDeepAnalysis && !$withDescriptions) {
            $this->info("--with-deep-analysis activa --with-descriptions automáticamente");
            $withDescriptions = true;
        }

        $scanPaths  = array_map('trim', explode(',', $scope));
        $outputPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $output);

        $this->info("Escaneando proyecto...");
        $this->line("  Raíz:    " . $projectRoot);
        $this->line("  Scope:   " . implode(', ', $scanPaths));
        $this->line("  Output:  " . $output);
        if ($withDescriptions) {
            $this->line("  LLM:     activado (descripciones cortas)");
        }
        if ($withDeepAnalysis) {
            $this->line("  Deep:    activado (análisis profundo + diagramas)");
        }
        $this->line('');

        $startTime = microtime(true);

        // 1. Escaneo del código
        $scanner = new GraphScanner($scanPaths, $projectRoot);
        $graph   = $scanner->scan();

        $errors = $scanner->getErrors();
        if (!empty($errors)) {
            $this->warning("Se encontraron " . count($errors) . " errores de parseo:");
            foreach (array_slice($errors, 0, 5) as $err) {
                $this->line("  - " . $err['file'] . ': ' . $err['message']);
            }
            if (count($errors) > 5) {
                $this->line('  ... y ' . (count($errors) - 5) . ' más');
            }
            $this->line('');
        }

        $stats = $graph->stats();
        $this->success("Escaneo completo:");
        $this->line("  Nodos:   " . $stats['total_nodes']);
        $this->line("  Aristas: " . $stats['total_edges']);
        $this->line('');

        // 2. Backup del estado anterior
        $renderer  = new ObsidianRenderer($graph, $outputPath);
        $backupDir = $renderer->backupExisting();
        if ($backupDir !== null) {
            $relativeBackup = str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $backupDir);
            $relativeBackup = str_replace('\\', '/', $relativeBackup);
            $this->info("Backup del estado anterior: {$relativeBackup}");
            $this->line('');
        }

        // 3. Preparar output: wipea nodos/ + escribe README/stats
        $this->info("Preparando output (wipe nodos/ + estructura inicial)...");
        $renderer->prepare();

        if (!$withDescriptions) {
            // Sin LLM: escribir todos los nodos de una.
            $written = $renderer->renderAllNodes();
            $this->success("  " . count($written) . " nodos escritos.");
            $this->line('');
        } else {
            // Con LLM: el folder queda vacío y los nodos aparecen uno por uno
            // a medida que el LLM va respondiendo.
            $this->success("  Output preparado. Los nodos aparecerán uno a uno con su descripción.");
            $this->line('');
        }

        // 4. Descripciones cortas (live: cada una se escribe apenas llega)
        $shortDescriptions = [];
        if ($withDescriptions) {
            $shortDescriptions = $this->generateDescriptionsLive($graph, $projectRoot, $outputPath, $renderer, $forceDescriptions);
        }

        // 5. Análisis profundos (live: cada uno se escribe apenas llega)
        if ($withDeepAnalysis) {
            $this->generateDeepAnalysesLive($graph, $projectRoot, $outputPath, $renderer, $shortDescriptions, $forceDeep);
        }

        // 6. Finalize: re-escribe README y stats con datos finales
        $renderer->finalize();

        $elapsed = round(microtime(true) - $startTime, 2);

        $totalNodes = count($graph->nodes());

        $this->line('');
        $this->success("Generación completa:");
        $this->line("  Notas escritas:  {$totalNodes}");
        $this->line("  Stats:           {$output}/_stats.md");
        $this->line("  README:          {$output}/_README.md");
        $this->line("  Tiempo total:    {$elapsed}s");
        $this->line('');
        $this->info("Abrí Obsidian → Graph View para ver el grafo completo.");

        return true;
    }

    private function generateDescriptionsLive(
        Graph $graph,
        string $projectRoot,
        string $outputPath,
        ObsidianRenderer $renderer,
        bool $force
    ): array {
        try {
            $provider = LlmProviderFactory::create();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return [];
        }

        $providerName = $provider->getProviderName();
        $model        = $provider->getModel();

        $cachePath = $outputPath . '/.cache/descriptions.json';
        $cache     = new DescriptionCache($cachePath);

        $nodes = $graph->nodes();
        $total = count($nodes);

        $this->info("Generando descripciones cortas (provider: {$providerName}, modelo: {$model})...");
        $this->line("  Total nodos: {$total}");
        $this->line("  Caché:       " . ($cache->size() > 0 ? "{$cache->size()} entradas existentes" : "vacío"));
        $this->line('');

        $descriptions = [];
        $cacheHits = 0;
        $apiCalls  = 0;
        $apiFails  = 0;
        $i         = 0;

        foreach ($nodes as $id => $meta) {
            $i++;
            $absoluteFile = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $meta['file']);
            if (!file_exists($absoluteFile)) {
                continue;
            }

            $code = file_get_contents($absoluteFile);
            $hash = md5($code . '::' . $providerName . '::' . $model);

            if (!$force) {
                $cached = $cache->get($meta['file'], $hash);
                if ($cached !== null) {
                    $descriptions[$id] = $cached;
                    $renderer->updateDescription($id, $cached);
                    $cacheHits++;
                    $this->progress($i, $total, "cache: {$meta['short_name']}");
                    continue;
                }
            }

            $description = $provider->describe(
                $meta['short_name'],
                $meta['type'],
                $meta['file'],
                $code
            );

            if ($description !== null) {
                $descriptions[$id] = $description;
                $renderer->updateDescription($id, $description);
                $cache->put($meta['file'], $hash, $description, $model);
                $apiCalls++;
                $this->progress($i, $total, "API:   {$meta['short_name']}");

                if ($apiCalls % 10 === 0) {
                    $cache->save();
                }
            } else {
                $apiFails++;
                $this->progress($i, $total, "FAIL:  {$meta['short_name']}");
            }
        }

        $cache->save();

        $this->line('');
        $this->success("Descripciones listas:");
        $this->line("  Cache hits:      {$cacheHits}");
        $this->line("  API calls:       {$apiCalls}");
        if ($apiFails > 0) {
            $this->warning("  API fails:       {$apiFails}");
        }
        $this->line('');

        return $descriptions;
    }

    private function generateDeepAnalysesLive(
        Graph $graph,
        string $projectRoot,
        string $outputPath,
        ObsidianRenderer $renderer,
        array $shortDescriptions,
        bool $force
    ): void {
        try {
            $provider = LlmProviderFactory::create();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return;
        }

        $providerName = $provider->getProviderName();
        $model        = $provider->getModel();

        $cachePath = $outputPath . '/.cache/deep-analyses.json';
        $cache     = new DescriptionCache($cachePath);
        $validator = new MermaidValidator();

        $analyzer = new DeepAnalyzer(
            graph: $graph,
            provider: $provider,
            cache: $cache,
            validator: $validator,
            projectRoot: $projectRoot,
            shortDescriptions: $shortDescriptions
        );

        $total = count((new ImportanceCriteria($graph))->filterImportant());

        $this->info("Generando análisis profundos (provider: {$providerName}, modelo: {$model})...");
        $this->line("  Candidatos importantes: {$total}");
        $this->line("  Caché:                  " . ($cache->size() > 0 ? "{$cache->size()} entradas existentes" : "vacío"));
        $this->line('');

        $result = $analyzer->analyze(
            progressCallback: fn($i, $tot, $label) => $this->progress($i, $tot, $label),
            onAnalysisReady: fn($nodeId, $analysis) => $renderer->updateDeepAnalysis($nodeId, $analysis),
            force: $force
        );

        $stats = $result['stats'];

        $this->line('');
        $this->success("Análisis profundos listos:");
        $this->line("  Cache hits:      {$stats['cache_hits']}");
        $this->line("  API calls:       {$stats['api_calls']}");
        if ($stats['fails'] > 0) {
            $this->warning("  API fails:       {$stats['fails']}");
        }
        if ($stats['mermaid_stripped'] > 0) {
            $this->warning("  Mermaid descart.:{$stats['mermaid_stripped']} (sintaxis inválida)");
        }
        $this->line('');
    }

    private function progress(int $current, int $total, string $label): void
    {
        $percent = round(($current / $total) * 100);
        echo "\r  [{$current}/{$total}] {$percent}%  {$label}" . str_repeat(' ', 20);
        if ($current === $total) {
            echo "\n";
        }
    }

    private function checkParserInstalled(): bool
    {
        if (class_exists(\PhpParser\ParserFactory::class)) {
            return true;
        }

        $this->error("La dependencia 'nikic/php-parser' no está instalada.");
        $this->line('');
        $this->info("Instalala con:");
        $this->line("  composer install");
        return false;
    }
}
