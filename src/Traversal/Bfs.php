<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Traversal;

use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\Algorithms\Contracts\TraversalAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use SplQueue;

/**
 * Breadth-First Search traversal algorithm.
 *
 * Time complexity: O(V + E)
 * Space complexity: O(V)
 */
final class Bfs implements TraversalAlgorithmInterface
{
    public function traverse(GraphInterface $graph, string $startNodeId): array
    {
        if (!$graph->hasNode($startNodeId)) {
            return [];
        }

        $algoGraph = new AlgorithmGraph($graph);
        $startIdx = $algoGraph->ids()->index($startNodeId);
        $successors = $algoGraph->successors();

        $visited = array_fill(0, $algoGraph->nodeCount(), false);
        $result = [];
        $queue = new SplQueue();

        $queue->enqueue($startIdx);
        $visited[$startIdx] = true;

        while (!$queue->isEmpty()) {
            $u = $queue->dequeue();
            $result[] = $u;

            foreach ($successors[$u] ?? [] as $v) {
                if (!$visited[$v]) {
                    $visited[$v] = true;
                    $queue->enqueue($v);
                }
            }
        }

        // Convert back to node IDs
        $ids = $algoGraph->ids();
        return array_map(fn(int $i): string => $ids->id($i), $result);
    }
}