<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Traversal;

use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\Algorithms\Contracts\TraversalAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use SplStack;

/**
 * Depth-First Search traversal algorithm (iterative implementation).
 *
 * Time complexity: O(V + E)
 * Space complexity: O(V)
 */
final class Dfs implements TraversalAlgorithmInterface
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
        $stack = new SplStack();

        $stack->push($startIdx);

        while (!$stack->isEmpty()) {
            $u = $stack->pop();

            if (!$visited[$u]) {
                $visited[$u] = true;
                $result[] = $u;

                // Add neighbors in reverse order to maintain left-to-right traversal
                $neighbors = array_reverse($successors[$u] ?? []);
                foreach ($neighbors as $v) {
                    if (!$visited[$v]) {
                        $stack->push($v);
                    }
                }
            }
        }

        // Convert back to node IDs
        $ids = $algoGraph->ids();
        return array_map(fn(int $i): string => $ids->id($i), $result);
    }
}