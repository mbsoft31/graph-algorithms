<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Support;

use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\Support\IndexMap;

/**
 * @internal This is a private support class for the algorithms package.
 * 
 * A read-only, internal data structure that converts any GraphInterface 
 * into a highly optimized, integer-indexed representation. This conversion 
 * happens once at the beginning of any algorithm's execution.
 */
final class AlgorithmGraph
{
    private IndexMap $ids;

    /** @var array<int, list<int>> Successors adjacency list. */
    private array $successors = [];

    /** @var array<int, list<int>> Predecessors adjacency list. */
    private array $predecessors = [];

    /** @var list<int> All node indices. */
    private array $nodes = [];

    private int $nodeCount;
    private int $edgeCount = 0;
    private bool $directed;

    public function __construct(
        GraphInterface $graph,
        bool $needPredecessors = false
    ) {
        $this->ids = new IndexMap();
        $this->directed = $graph->isDirected();

        // First pass: index all nodes
        $nodeIds = $graph->nodes();
        foreach ($nodeIds as $nodeId) {
            $this->nodes[] = $this->ids->index($nodeId);
        }
        $this->nodeCount = count($this->nodes);

        // Second pass: build integer adjacency lists
        foreach ($nodeIds as $nodeId) {
            $u = $this->ids->index($nodeId);
            $this->successors[$u] = [];

            foreach ($graph->successors($nodeId) as $successorId) {
                $v = $this->ids->index($successorId);
                $this->successors[$u][] = $v;
                $this->edgeCount++;
            }
        }

        if ($needPredecessors) {
            // Build reverse adjacency from successors in one pass
            $this->predecessors = array_fill(0, $this->nodeCount, []);
            foreach ($this->successors as $u => $numbers) {
                foreach ($numbers as $v) {
                    $this->predecessors[$v][] = $u;
                }
            }
        }
    }

    // Accessors (no references to prevent mutation)
    public function ids(): IndexMap
    {
        return $this->ids;
    }

    /**
     * @return list<int>
     */
    public function nodes(): array
    {
        return $this->nodes;
    }

    /**
     * @return array<int, list<int>>
     */
    public function successors(): array
    {
        return $this->successors;
    }

    /**
     * @return array<int, list<int>>
     */
    public function predecessors(): array
    {
        return $this->predecessors;
    }

    public function nodeCount(): int
    {
        return $this->nodeCount;
    }

    public function edgeCount(): int
    {
        return $this->edgeCount;
    }

    public function isDirected(): bool
    {
        return $this->directed;
    }
}
