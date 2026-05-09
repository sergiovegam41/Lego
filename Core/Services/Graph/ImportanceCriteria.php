<?php

namespace Core\Services\Graph;

class ImportanceCriteria
{
    private const HUB_THRESHOLD       = 7;
    private const CONTROLLER_LOC_MIN  = 100;
    private const SCREEN_LOC_MIN      = 150;

    private const CORE_LAYERS = [
        'core',
        'core-routing',
        'core-services',
        'core-components',
        'core-controllers',
        'core-registry',
    ];

    private const EXCLUDED_PATTERNS = [
        '/Core\/Services\/Graph\//',
        '/components\/shared\/(Buttons|Forms|Navigation|Essentials)\//',
        '/Renderer\.php$/',
    ];

    public function __construct(private readonly Graph $graph) {}

    public function isImportant(string $nodeId, array $node): bool
    {
        if ($this->matchesExcluded($node)) {
            return false;
        }

        if ($this->isAnchor($node))                       return true;
        if ($this->isCoreFoundation($node))               return true;
        if ($this->isHub($nodeId))                        return true;
        if ($this->isSubstantialController($node))        return true;
        if ($this->isLargeScreen($nodeId, $node))         return true;

        return false;
    }

    public function filterImportant(): array
    {
        $important = [];
        foreach ($this->graph->nodes() as $id => $node) {
            if ($this->isImportant($id, $node)) {
                $important[$id] = $node;
            }
        }
        return $important;
    }

    private function isAnchor(array $node): bool
    {
        return in_array($node['type'] ?? '', ['abstract-class', 'interface', 'trait'], true);
    }

    private function isCoreFoundation(array $node): bool
    {
        return in_array($node['layer'] ?? '', self::CORE_LAYERS, true);
    }

    private function isHub(string $nodeId): bool
    {
        return count($this->graph->getIncomingEdges($nodeId)) > self::HUB_THRESHOLD;
    }

    private function isSubstantialController(array $node): bool
    {
        $type = $node['type'] ?? '';
        $loc  = (int) ($node['loc'] ?? 0);
        return in_array($type, ['controller', 'command'], true) && $loc > self::CONTROLLER_LOC_MIN;
    }

    private function isLargeScreen(string $nodeId, array $node = []): bool
    {
        if (empty($node)) {
            $node = $this->graph->getNode($nodeId) ?? [];
        }
        if (($node['type'] ?? '') !== 'component') return false;
        if ((int)($node['loc'] ?? 0) <= self::SCREEN_LOC_MIN) return false;

        foreach ($this->graph->getOutgoingEdges($nodeId) as $edge) {
            if ($edge['type'] === 'implements') {
                $target = $this->graph->getNode($edge['to']);
                if (($target['short_name'] ?? '') === 'ScreenInterface') {
                    return true;
                }
            }
        }
        return false;
    }

    private function matchesExcluded(array $node): bool
    {
        $file = $node['file'] ?? '';
        foreach (self::EXCLUDED_PATTERNS as $pattern) {
            if (preg_match($pattern, $file)) {
                return true;
            }
        }
        return false;
    }
}
