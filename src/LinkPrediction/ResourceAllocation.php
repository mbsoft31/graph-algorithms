<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\LinkPrediction;

use Mbsoft\Graph\Algorithms\Contracts\LinkPredictorInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;

/**
 * Resource Allocation Index for Link Prediction.
 *
 * The Resource Allocation index is based on the idea of resource allocation
 * processes taking place on networks. Each node has a unit of resource to
 * distribute equally among its neighbors.
 *
 * For nodes u and v, the index is:
 * RA(u,v) = sum over common neighbors w of 1/deg(w)
 *
 * This gives higher weight to rare common neighbors (low degree).
 */
final class ResourceAllocation implements LinkPredictorInterface
{
    /**
     * Compute the Resource Allocation score between two nodes.
     */
    public function score(GraphInterface $graph, string $u, string $v): float
    {
        $ag = new AlgorithmGraph($graph, needPredecessors: true);
        $ids = $ag->ids();

        // Convert node IDs to indices
        $uIdx = $ids->hasString($u) ? $ids->index($u) : null;
        $vIdx = $ids->hasString($v) ? $ids->index($v) : null;

        if ($uIdx === null || $vIdx === null) {
            return 0.0;
        }

        // Get neighbors for both directed and undirected graphs
        $uNeighbors = $this->getNeighbors($ag, $uIdx);
        $vNeighbors = $this->getNeighbors($ag, $vIdx);

        // Find common neighbors
        $commonNeighbors = array_intersect($uNeighbors, $vNeighbors);

        $score = 0.0;
        foreach ($commonNeighbors as $wIdx) {
            $wDegree = count($this->getNeighbors($ag, $wIdx));
            if ($wDegree > 0) { // Avoid division by zero
                $score += 1.0 / $wDegree;
            }
        }

        return $score;
    }

    /**
     * Compute top-k Resource Allocation scores from a given node.
     */
    public function scoresFrom(GraphInterface $graph, string $u, int $k = 20): array
    {
        $ag = new AlgorithmGraph($graph, needPredecessors: true);
        $ids = $ag->ids();

        $uIdx = $ids->hasString($u) ? $ids->index($u) : null;
        if ($uIdx === null) {
            return [];
        }

        $uNeighbors = $this->getNeighbors($ag, $uIdx);
        $uNeighborSet = array_flip($uNeighbors);

        $scores = [];

        // Consider all nodes that are not direct neighbors of u
        foreach ($ag->nodes() as $vIdx) {
            if ($vIdx === $uIdx || isset($uNeighborSet[$vIdx])) {
                continue; // Skip self and existing neighbors
            }

            $vNodeId = $ids->string($vIdx);
            $score = $this->score($graph, $u, $vNodeId);

            if ($score > 0.0) {
                $scores[$vNodeId] = $score;
            }
        }

        // Sort by score descending and return top-k
        arsort($scores);
        return array_slice($scores, 0, $k, true);
    }

    /**
     * Get all neighbors of a node (undirected view for directed graphs).
     */
    private function getNeighbors(AlgorithmGraph $ag, int $nodeIdx): array
    {
        $successors = $ag->successors()[$nodeIdx] ?? [];

        if ($ag->isDirected()) {
            // For directed graphs, consider both in and out neighbors
            $predecessors = $ag->predecessors()[$nodeIdx] ?? [];
            return array_unique(array_merge($successors, $predecessors));
        }

        return $successors;
    }
}