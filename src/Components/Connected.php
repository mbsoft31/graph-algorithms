<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Components;

use Mbsoft\Graph\Algorithms\Contracts\ComponentsFinderInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;

/**
 * Connected components on an undirected view.
 *
 * Directed graphs are treated as weakly connected graphs by merging successors
 * and predecessors for each node.
 */
final class Connected implements ComponentsFinderInterface
{
    /**
     * @return list<list<string>>
     */
    public function findComponents(GraphInterface $graph): array
    {
        $algoGraph = new AlgorithmGraph($graph, needPredecessors: true);

        if ($algoGraph->nodeCount() === 0) {
            return [];
        }

        $visited = array_fill(0, $algoGraph->nodeCount(), false);
        $components = [];

        foreach ($algoGraph->nodes() as $nodeIdx) {
            if ($visited[$nodeIdx]) {
                continue;
            }

            $components[] = $this->collectComponent($algoGraph, $nodeIdx, $visited);
        }

        return $components;
    }

    /**
     * @param array<int, bool> $visited
     * @return list<string>
     */
    private function collectComponent(AlgorithmGraph $graph, int $startIdx, array &$visited): array
    {
        $component = [];
        $stack = [$startIdx];
        $visited[$startIdx] = true;

        while ($stack !== []) {
            $nodeIdx = array_pop($stack);
            $component[] = $graph->ids()->id($nodeIdx);

            foreach ($this->neighbors($graph, $nodeIdx) as $neighborIdx) {
                if ($visited[$neighborIdx]) {
                    continue;
                }

                $visited[$neighborIdx] = true;
                $stack[] = $neighborIdx;
            }
        }

        return $component;
    }

    /**
     * @return list<int>
     */
    private function neighbors(AlgorithmGraph $graph, int $nodeIdx): array
    {
        return array_values(array_unique(array_merge(
            $graph->successors()[$nodeIdx] ?? [],
            $graph->predecessors()[$nodeIdx] ?? [],
        )));
    }
}
