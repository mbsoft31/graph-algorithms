<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Topological;

use InvalidArgumentException;
use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use SplQueue;

/**
 * Topological sort using Kahn's algorithm.
 *
 * Time complexity: O(V + E)
 * Space complexity: O(V)
 */
final class TopologicalSort
{
    /**
     * @param GraphInterface $graph Must be a directed acyclic graph (DAG)
     * @return list<string> Topologically sorted node IDs
     * @throws CycleDetectedException If the graph contains cycles
     */
    public function sort(GraphInterface $graph): array
    {
        if (!$graph->isDirected()) {
            throw new InvalidArgumentException('Topological sort requires a directed graph');
        }

        $algoGraph = new AlgorithmGraph($graph, needPredecessors: true);
        $successors = $algoGraph->successors();
        $predecessors = $algoGraph->predecessors();

        // Calculate in-degrees
        $inDegree = array_map('count', $predecessors);

        // Find nodes with no incoming edges
        $queue = new SplQueue();
        foreach ($inDegree as $nodeIdx => $degree) {
            if ($degree === 0) {
                $queue->enqueue($nodeIdx);
            }
        }

        $result = [];
        $processedCount = 0;

        while (!$queue->isEmpty()) {
            $u = $queue->dequeue();
            $result[] = $u;
            $processedCount++;

            // Remove this node from the graph
            foreach ($successors[$u] ?? [] as $v) {
                $inDegree[$v]--;
                if ($inDegree[$v] === 0) {
                    $queue->enqueue($v);
                }
            }
        }

        // Check for cycles
        if ($processedCount !== $algoGraph->nodeCount()) {
            throw new CycleDetectedException();
        }

        // Convert back to node IDs
        $ids = $algoGraph->ids();
        return array_map(fn(int $i): string => $ids->id($i), $result);
    }
}