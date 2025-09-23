<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Pathfinding;

use InvalidArgumentException;
use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\Algorithms\Contracts\PathfindingAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use SplPriorityQueue;

/**
 * Dijkstra's shortest path algorithm with proper priority queue handling.
 *
 * Time complexity: O((V + E) log V)
 * Space complexity: O(V)
 *
 * Requires non-negative edge weights.
 */
final class Dijkstra implements PathfindingAlgorithmInterface
{
    /** @var callable(array, string, string): float */
    private $weightCallback;

    public function __construct(?callable $weightCallback = null)
    {
        $this->weightCallback = $weightCallback ?? fn(array $attrs, string $from, string $to): float => $attrs['weight'] ?? 1.0;
    }

    public function find(GraphInterface $graph, string $startNodeId, string $endNodeId): ?PathResult
    {
        if (!$graph->hasNode($startNodeId) || !$graph->hasNode($endNodeId)) {
            return null;
        }

        $algoGraph = new AlgorithmGraph($graph);
        $startIdx = $algoGraph->ids()->index($startNodeId);
        $endIdx = $algoGraph->ids()->index($endNodeId);

        // Build neighbor and weight lists for cache efficiency
        [$neighbors, $weights] = $this->buildWeightStructure($graph, $algoGraph);

        $N = $algoGraph->nodeCount();
        $distances = array_fill(0, $N, INF);
        $previous = array_fill(0, $N, null);
        $visited = array_fill(0, $N, false);

        $distances[$startIdx] = 0.0;

        $pq = new SplPriorityQueue();
        $pq->setExtractFlags(SplPriorityQueue::EXTR_DATA);
        $pq->insert($startIdx, 0.0);

        while (!$pq->isEmpty()) {
            /** @var int $u */
            $u = $pq->extract();

            // Skip stale entries
            if ($visited[$u]) {
                continue;
            }

            $visited[$u] = true;

            if ($u === $endIdx) {
                break; // Found the shortest path to target
            }

            foreach ($neighbors[$u] ?? [] as $k => $v) {
                $weight = $weights[$u][$k];

                if ($weight < 0) {
                    throw new InvalidArgumentException('Dijkstra requires non-negative weights');
                }

                $alt = $distances[$u] + $weight;

                if ($alt < $distances[$v]) {
                    $distances[$v] = $alt;
                    $previous[$v] = $u;
                    $pq->insert($v, -$alt); // Negative for max-heap behavior
                }
            }
        }

        // Reconstruct path
        if ($distances[$endIdx] === INF) {
            return null;
        }

        $path = [];
        for ($v = $endIdx; $v !== null; $v = $previous[$v]) {
            $path[] = $v;
        }
        $path = array_reverse($path);

        // Convert back to node IDs
        $ids = $algoGraph->ids();
        $nodeIds = array_map(fn(int $i): string => $ids->id($i), $path);

        return new PathResult($nodeIds, $distances[$endIdx]);
    }

    private function buildWeightStructure(GraphInterface $graph, AlgorithmGraph $algoGraph): array
    {
        $neighbors = [];
        $weights = [];

        foreach ($graph->edges() as $edge) {
            $u = $algoGraph->ids()->index($edge->from);
            $v = $algoGraph->ids()->index($edge->to);
            $weight = ($this->weightCallback)($edge->attributes, $edge->from, $edge->to);

            $neighbors[$u][] = $v;
            $weights[$u][] = $weight;

            // Handle undirected graphs
            if (!$graph->isDirected()) {
                $neighbors[$v][] = $u;
                $weights[$v][] = $weight;
            }
        }

        return [$neighbors, $weights];
    }
}