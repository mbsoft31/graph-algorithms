<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Pathfinding;

use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\Algorithms\Contracts\PathfindingAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;

/**
 * A* pathfinding algorithm with heuristic function.
 * 
 * Time complexity: O(b^d) where b is branching factor and d is depth
 * Space complexity: O(b^d)
 * 
 * Uses a heuristic function to guide search toward the goal.
 */
final class AStar implements PathfindingAlgorithmInterface
{
    /** @var callable(array, string, string): float */
    private $weightCallback;

    /** @var callable(string, string): float */
    private $heuristicCallback;

    /**
     * @param callable|null $weightCallback Function to extract weight from edge attributes
     * @param callable|null $heuristicCallback Function to estimate distance between two nodes
     */
    public function __construct(?callable $weightCallback = null, ?callable $heuristicCallback = null)
    {
        $this->weightCallback = $weightCallback ?? fn(array $attrs, string $from, string $to): float => $attrs['weight'] ?? 1.0;
        $this->heuristicCallback = $heuristicCallback ?? fn(string $from, string $to): float => 0.0; // Defaults to Dijkstra
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
        $previous = array_fill(0, $N, null);
        $visited = array_fill(0, $N, false);

        $gScore = array_fill(0, $N, INF);  // Cost from start
        $fScore = array_fill(0, $N, INF);  // gScore + heuristic

        $gScore[$startIdx] = 0.0;
        $fScore[$startIdx] = ($this->heuristicCallback)($startNodeId, $endNodeId);

        $pq = new \SplPriorityQueue();
        $pq->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
        $pq->insert($startIdx, -$fScore[$startIdx]);

        while (!$pq->isEmpty()) {
            /** @var int $current */
            $current = $pq->extract();

            // Skip stale entries
            if ($visited[$current]) {
                continue;
            }

            $visited[$current] = true;

            if ($current === $endIdx) {
                break; // Found path to target
            }

            foreach ($neighbors[$current] ?? [] as $k => $neighbor) {
                if ($visited[$neighbor]) {
                    continue;
                }

                $weight = $weights[$current][$k];

                if ($weight < 0) {
                    throw new \InvalidArgumentException('A* requires non-negative weights');
                }

                $tentativeGScore = $gScore[$current] + $weight;

                if ($tentativeGScore < $gScore[$neighbor]) {
                    $previous[$neighbor] = $current;
                    $gScore[$neighbor] = $tentativeGScore;

                    $neighborNodeId = $algoGraph->ids()->id($neighbor);
                    $heuristic = ($this->heuristicCallback)($neighborNodeId, $endNodeId);
                    $fScore[$neighbor] = $gScore[$neighbor] + $heuristic;

                    $pq->insert($neighbor, -$fScore[$neighbor]);
                }
            }
        }

        // Reconstruct path
        if ($gScore[$endIdx] === INF) {
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

        return new PathResult($nodeIds, $gScore[$endIdx]);
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
