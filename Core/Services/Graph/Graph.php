<?php

namespace Core\Services\Graph;

class Graph
{
    private array $nodes = [];
    private array $edges = [];

    public function addNode(string $id, array $metadata): void
    {
        if (!isset($this->nodes[$id])) {
            $this->nodes[$id] = $metadata;
            return;
        }

        $this->nodes[$id] = array_merge($this->nodes[$id], $metadata);
    }

    public function hasNode(string $id): bool
    {
        return isset($this->nodes[$id]);
    }

    public function getNode(string $id): ?array
    {
        return $this->nodes[$id] ?? null;
    }

    public function nodes(): array
    {
        return $this->nodes;
    }

    public function addEdge(string $from, string $to, string $type, array $meta = []): void
    {
        $this->edges[] = [
            'from' => $from,
            'to'   => $to,
            'type' => $type,
            'meta' => $meta,
        ];
    }

    public function edges(): array
    {
        return $this->edges;
    }

    public function getOutgoingEdges(string $nodeId): array
    {
        return array_filter($this->edges, fn($e) => $e['from'] === $nodeId);
    }

    public function getIncomingEdges(string $nodeId): array
    {
        return array_filter($this->edges, fn($e) => $e['to'] === $nodeId);
    }

    public function getEdgesByType(string $nodeId, string $type, string $direction = 'out'): array
    {
        $edges = $direction === 'out'
            ? $this->getOutgoingEdges($nodeId)
            : $this->getIncomingEdges($nodeId);

        return array_filter($edges, fn($e) => $e['type'] === $type);
    }

    public function stats(): array
    {
        $byType  = [];
        $byLayer = [];
        foreach ($this->nodes as $node) {
            $type  = $node['type']  ?? 'unknown';
            $layer = $node['layer'] ?? 'unknown';
            $byType[$type]   = ($byType[$type]   ?? 0) + 1;
            $byLayer[$layer] = ($byLayer[$layer] ?? 0) + 1;
        }

        $edgesByType = [];
        foreach ($this->edges as $edge) {
            $t = $edge['type'];
            $edgesByType[$t] = ($edgesByType[$t] ?? 0) + 1;
        }

        return [
            'total_nodes'  => count($this->nodes),
            'total_edges'  => count($this->edges),
            'by_type'      => $byType,
            'by_layer'     => $byLayer,
            'edges_by_type'=> $edgesByType,
        ];
    }
}
