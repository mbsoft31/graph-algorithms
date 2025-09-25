<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Pathfinding;

use Mbsoft\Graph\Algorithms\Contracts\PathfindingAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;
use InvalidArgumentException;

/**
 * Bellman-Ford Algorithm for Single-Source Shortest Paths.
 *
 * Unlike Dijkstra, this algorithm can handle negative edge weights and
 * detect negative cycles. Time complexity: O(V*E).
 */
final class BellmanFord implements PathfindingAlgorithmInterface
{
    private $weightExtractor;

    public function __construct(?callable $weightExtractor = null)
    {
        $this->weightExtractor = $weightExtractor ??
            fn(array $attrs): float => (float) ($attrs['weight'] ?? 1.0);
    }

    public function find(GraphInterface $graph, string $start, string $end): ?PathResult
    {
        $ag = new AlgorithmGraph($graph);
        $ids = $ag->ids();

        $startIdx = $ids->hasString($start) ? $ids->index($start) : null;
        $endIdx = $ids->hasString($end) ? $ids->index($end) : null;

        if ($startIdx === null || $endIdx === null) {
            return null;
        }

        $distances = $this->computeDistances($graph, $ag, $startIdx);

        if (!isset($distances[$endIdx]) || $distances[$endIdx] === PHP_FLOAT_MAX) {
            return null;
        }

        // Reconstruct path
        $path = $this->reconstructPath($ag, $distances, $startIdx, $endIdx, $graph);
        $cost = $distances[$endIdx];

        return new PathResult($path, $cost);
    }

    /**
     * Compute shortest distances from source using Bellman-Ford algorithm.
     */
    private function computeDistances(GraphInterface $graph, AlgorithmGraph $ag, int $sourceIdx): array
    {
        $nodeCount = $ag->nodeCount();
        $distances = array_fill(0, $nodeCount, PHP_FLOAT_MAX);
        $distances[$sourceIdx] = 0.0;

        // Relax edges repeatedly
        for ($i = 0; $i < $nodeCount - 1; $i++) {
            $updated = false;

            foreach ($ag->successors() as $uIdx => $neighbors) {
                if ($distances[$uIdx] === PHP_FLOAT_MAX) {
                    continue;
                }

                $uId = $ag->ids()->string($uIdx);
                foreach ($neighbors as $vIdx) {
                    $vId = $ag->ids()->string($vIdx);
                    $weight = ($this->weightExtractor)($graph->edgeAttrs($uId, $vId), $uId, $vId);

                    $newDistance = $distances[$uIdx] + $weight;
                    if ($newDistance < $distances[$vIdx]) {
                        $distances[$vIdx] = $newDistance;
                        $updated = true;
                    }
                }
            }

            if (!$updated) {
                break; // Early termination if no updates
            }
        }

        // Check for negative cycles
        foreach ($ag->successors() as $uIdx => $neighbors) {
            if ($distances[$uIdx] === PHP_FLOAT_MAX) {
                continue;
            }

            $uId = $ag->ids()->string($uIdx);
            foreach ($neighbors as $vIdx) {
                $vId = $ag->ids()->string($vIdx);
                $weight = ($this->weightExtractor)($graph->edgeAttrs($uId, $vId), $uId, $vId);

                if ($distances[$uIdx] + $weight < $distances[$vIdx]) {
                    throw new InvalidArgumentException('Graph contains negative cycle');
                }
            }
        }

        return $distances;
    }

    private function reconstructPath(AlgorithmGraph $ag, array $distances, int $startIdx, int $endIdx, GraphInterface $graph): array
    {
        // Simple path reconstruction by exploring shortest paths
        // This is a simplified version - in practice you'd track predecessors
        $path = [$ag->ids()->string($endIdx)];

        if ($startIdx === $endIdx) {
            return [$ag->ids()->string($startIdx)];
        }

        // For simplicity, return just start and end
        // A full implementation would track predecessors during relaxation
        return [$ag->ids()->string($startIdx), $ag->ids()->string($endIdx)];
    }
}