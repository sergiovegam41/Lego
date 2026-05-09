<?php

namespace Core\Services\Graph;

class DeepAnalyzer
{
    public function __construct(
        private readonly Graph $graph,
        private readonly LlmProvider $provider,
        private readonly DescriptionCache $cache,
        private readonly MermaidValidator $validator,
        private readonly string $projectRoot,
        /** @var array<string,string>  Map nodeId → short description */
        private readonly array $shortDescriptions = []
    ) {}

    /**
     * Genera análisis profundos para los nodos importantes.
     *
     * @return array{
     *   analyses: array<string,string>,
     *   stats: array{candidates: int, processed: int, cache_hits: int, api_calls: int, fails: int, mermaid_stripped: int}
     * }
     */
    public function analyze(
        ?callable $progressCallback = null,
        ?callable $onAnalysisReady = null,
        bool $force = false
    ): array {
        $criteria   = new ImportanceCriteria($this->graph);
        $candidates = $criteria->filterImportant();

        $analyses = [];
        $cacheHits = 0;
        $apiCalls  = 0;
        $fails     = 0;
        $stripped  = 0;
        $i         = 0;
        $total     = count($candidates);

        foreach ($candidates as $id => $node) {
            $i++;
            $absoluteFile = $this->projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $node['file']);
            if (!file_exists($absoluteFile)) {
                continue;
            }

            $code = file_get_contents($absoluteFile);
            $hash = md5($code . '::deep::' . $this->provider->getProviderName() . '::' . $this->provider->getModel());

            if (!$force) {
                $cached = $this->cache->get($node['file'], $hash);
                if ($cached !== null) {
                    $analyses[$id] = $cached;
                    $cacheHits++;
                    if ($onAnalysisReady) {
                        $onAnalysisReady($id, $cached);
                    }
                    if ($progressCallback) {
                        $progressCallback($i, $total, "cache: {$node['short_name']}");
                    }
                    continue;
                }
            }

            $context = $this->buildContext($id, $node);
            $rawAnalysis = $this->provider->describeDeep($context, $code);

            if ($rawAnalysis === null) {
                $fails++;
                if ($progressCallback) {
                    $progressCallback($i, $total, "FAIL:  {$node['short_name']}");
                }
                continue;
            }

            $processed = $this->validator->processResponse($rawAnalysis);

            if ($processed !== $rawAnalysis) {
                $stripped++;
            }

            $analyses[$id] = $processed;
            $this->cache->put($node['file'], $hash, $processed, $this->provider->getModel());
            $apiCalls++;

            if ($onAnalysisReady) {
                $onAnalysisReady($id, $processed);
            }
            if ($progressCallback) {
                $progressCallback($i, $total, "API:   {$node['short_name']}");
            }

            if ($apiCalls % 5 === 0) {
                $this->cache->save();
            }
        }

        $this->cache->save();

        return [
            'analyses' => $analyses,
            'stats'    => [
                'candidates'       => $total,
                'processed'        => $cacheHits + $apiCalls,
                'cache_hits'       => $cacheHits,
                'api_calls'        => $apiCalls,
                'fails'            => $fails,
                'mermaid_stripped' => $stripped,
            ],
        ];
    }

    private function buildContext(string $nodeId, array $node): array
    {
        $extends       = $this->joinOutgoing($nodeId, 'extends');
        $implements    = $this->joinOutgoing($nodeId, 'implements');
        $traits        = $this->joinOutgoing($nodeId, 'uses_trait');
        $attributes    = $this->joinOutgoing($nodeId, 'attribute');

        $extendedBy    = $this->joinIncoming($nodeId, 'extends', 12);
        $implementedBy = $this->joinIncoming($nodeId, 'implements', 12);
        $usedAsTraitBy = $this->joinIncoming($nodeId, 'uses_trait', 12);
        $referencedBy  = $this->joinIncoming($nodeId, 'instantiates', 8);

        return [
            'short_name'        => $node['short_name'],
            'type'              => $node['type'],
            'file'              => $node['file'],
            'extends'           => $extends       ?: 'ninguna',
            'implements'        => $implements    ?: 'ninguna',
            'traits'            => $traits        ?: 'ninguno',
            'attributes'        => $attributes    ?: 'ninguno',
            'extended_by'       => $extendedBy    ?: 'ninguna',
            'implemented_by'    => $implementedBy ?: 'ninguna',
            'used_as_trait_by'  => $usedAsTraitBy ?: 'ninguna',
            'instantiated_by'   => $referencedBy  ?: 'ninguna',
            'deps'              => count($this->graph->getOutgoingEdges($nodeId)),
            'dependents'        => count($this->graph->getIncomingEdges($nodeId)),
            'short_description' => $this->shortDescriptions[$nodeId] ?? '(sin descripción previa)',
        ];
    }

    private function joinOutgoing(string $nodeId, string $edgeType, int $limit = 0): string
    {
        return $this->joinEdges($this->graph->getEdgesByType($nodeId, $edgeType, 'out'), 'to', $limit);
    }

    private function joinIncoming(string $nodeId, string $edgeType, int $limit = 0): string
    {
        return $this->joinEdges($this->graph->getEdgesByType($nodeId, $edgeType, 'in'), 'from', $limit);
    }

    private function joinEdges(array $edges, string $direction, int $limit): string
    {
        $names = [];
        foreach ($edges as $edge) {
            $target = $this->graph->getNode($edge[$direction]);
            if ($target) {
                $names[] = $target['short_name'];
            }
        }
        $names = array_values(array_unique($names));
        $total = count($names);
        if ($limit > 0 && $total > $limit) {
            $shown = array_slice($names, 0, $limit);
            return implode(', ', $shown) . ' (+' . ($total - $limit) . ' más)';
        }
        return implode(', ', $names);
    }
}
