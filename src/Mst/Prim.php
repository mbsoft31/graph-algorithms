<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Mst;

use InvalidArgumentException;
use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use SplPriorityQueue;

/**
 * Result object for minimum spanning tree algorithms.
 */
final readonly class MstResult
{
    /**
     * @param list<array{from: string, to: string, weight: float}> $edges The edges in the MST
     * @param float $totalWeight The total weight of the MST
     */
    public function __construct(
        public array $edges,
        public float $totalWeight
    ) {}

    public function edgeCount(): int
    {
        return count($this->edges);
    }
}

/**
 * Prim's Minimum Spanning Tree algorithm.
 * 
 * Time complexity: O(E log V) with binary heap
 * Space complexity: O(V)
 * 
 * Finds the minimum spanning tree of a connected, undirected, weighted graph.
 */
final class Prim
{
    /** @var callable(array): float */
    private $weightCallback;

    public function __construct(?callable $weightCallback = null)
    {
        $this->weightCallback = $weightCallback ?? fn(array $attrs): float => $attrs['weight'] ?? 1.0;
    }

    /**
     * @param GraphInterface $graph Must be undirected and connected
     * @return MstResult|null The MST result, or null if graph is not connected
     */
    public function findMst(GraphInterface $graph): ?MstResult
    {
        if ($graph->isDirected()) {
            throw new InvalidArgumentException('Prim\'s algorithm requires an undirected graph');
        }

        $algoGraph = new AlgorithmGraph($graph);

        if ($algoGraph->nodeCount() === 0) {
            return new MstResult([], 0.0);
        }

        if ($algoGraph->nodeCount() === 1) {
            return new MstResult([], 0.0);
        }

        // Build edge weight mapping
        $edgeWeights = $this->buildEdgeWeights($graph, $algoGraph);

        $N = $algoGraph->nodeCount();
        $inMst = array_fill(0, $N, false);
        $key = array_fill(0, $N, INF);
        $parent = array_fill(0, $N, -1);

        // Start with node 0
        $key[0] = 0.0;

        $pq = new SplPriorityQueue();
        $pq->setExtractFlags(SplPriorityQueue::EXTR_DATA);

        for ($i = 0; $i < $N; $i++) {
            $pq->insert($i, -$key[$i]);
        }

        $mstEdges = [];
        $totalWeight = 0.0;
        $successors = $algoGraph->successors();

        while (!$pq->isEmpty()) {
            $u = $pq->extract();

            if ($inMst[$u]) {
                continue; // Skip stale entries
            }

            $inMst[$u] = true;

            // Add edge to MST (except for the root)
            if ($parent[$u] !== -1) {
                $parentNodeId = $algoGraph->ids()->id($parent[$u]);
                $currentNodeId = $algoGraph->ids()->id($u);
                $weight = $edgeWeights[$parent[$u]][$u];

                $mstEdges[] = [
                    'from' => $parentNodeId,
                    'to' => $currentNodeId,
                    'weight' => $weight
                ];
                $totalWeight += $weight;
            }

            // Update keys of adjacent vertices
            foreach ($successors[$u] ?? [] as $v) {
                if (!$inMst[$v]) {
                    $weight = $edgeWeights[$u][$v];
                    if ($weight < $key[$v]) {
                        $key[$v] = $weight;
                        $parent[$v] = $u;
                        $pq->insert($v, -$weight);
                    }
                }
            }
        }

        // Check if all nodes are connected
        if (count($mstEdges) !== $N - 1) {
            return null; // Graph is not connected
        }

        return new MstResult($mstEdges, $totalWeight);
    }

    private function buildEdgeWeights(GraphInterface $graph, AlgorithmGraph $algoGraph): array
    {
        $weights = [];

        foreach ($graph->edges() as $edge) {
            $u = $algoGraph->ids()->index($edge->from);
            $v = $algoGraph->ids()->index($edge->to);
            $weight = ($this->weightCallback)($edge->attributes);

            $weights[$u][$v] = $weight;
            $weights[$v][$u] = $weight; // Undirected graph
        }

        return $weights;
    }
}
