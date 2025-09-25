<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Decomposition;

use Mbsoft\Graph\Algorithms\Contracts\CoreDecompositionInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;

/**
 * K-Core Decomposition Algorithm.
 *
 * The k-core of a graph is the maximal subgraph in which each vertex has
 * at least k neighbors within the subgraph. The core number of a vertex
 * is the largest value k such that the vertex exists in the k-core.
 *
 * This implementation uses the peeling algorithm which repeatedly removes
 * vertices with degree less than the current k value.
 */
final class KCore implements CoreDecompositionInterface
{
    /**
     * Compute the k-core decomposition using the peeling algorithm.
     *
     * @return array<string, int> Array mapping node IDs to their core numbers
     */
    public function compute(GraphInterface $graph): array
    {
        $ag = new AlgorithmGraph($graph, needPredecessors: true);
        $ids = $ag->ids();

        if ($ag->nodeCount() === 0) {
            return [];
        }

        // Initialize degrees and core numbers
        $degrees = [];
        $coreNumbers = [];

        foreach ($ag->nodes() as $nodeIdx) {
            $neighbors = $this->getNeighbors($ag, $nodeIdx);
            $degrees[$nodeIdx] = count($neighbors);
            $coreNumbers[$nodeIdx] = 0;
        }

        // Create degree buckets for efficient minimum degree finding
        $maxDegree = max($degrees);
        $degreeBuckets = array_fill(0, $maxDegree + 1, []);
        $bucketPosition = []; // Track position of each node in its bucket

        foreach ($degrees as $nodeIdx => $degree) {
            $degreeBuckets[$degree][] = $nodeIdx;
            $bucketPosition[$nodeIdx] = count($degreeBuckets[$degree]) - 1;
        }

        $processed = [];

        // Process nodes in order of increasing degree
        for ($i = 0; $i < $ag->nodeCount(); $i++) {
            // Find node with minimum degree among unprocessed nodes
            $minDegree = 0;
            while ($minDegree <= $maxDegree && empty($degreeBuckets[$minDegree])) {
                $minDegree++;
            }

            if ($minDegree > $maxDegree) {
                break;
            }

            // Remove node with minimum degree
            $nodeIdx = array_shift($degreeBuckets[$minDegree]);
            $coreNumbers[$nodeIdx] = $minDegree;
            $processed[$nodeIdx] = true;

            // Update degrees of unprocessed neighbors
            $neighbors = $this->getNeighbors($ag, $nodeIdx);
            foreach ($neighbors as $neighborIdx) {
                if (isset($processed[$neighborIdx])) {
                    continue; // Already processed
                }

                $oldDegree = $degrees[$neighborIdx];
                if ($oldDegree > $minDegree) {
                    $newDegree = $oldDegree - 1;
                    $degrees[$neighborIdx] = $newDegree;

                    // Remove from old bucket and add to new bucket
                    $pos = array_search($neighborIdx, $degreeBuckets[$oldDegree]);
                    if ($pos !== false) {
                        unset($degreeBuckets[$oldDegree][$pos]);
                        $degreeBuckets[$oldDegree] = array_values($degreeBuckets[$oldDegree]);
                    }

                    $degreeBuckets[$newDegree][] = $neighborIdx;
                }
            }
        }

        // Convert indices back to node IDs
        $result = [];
        foreach ($coreNumbers as $nodeIdx => $coreNumber) {
            $nodeId = $ids->string($nodeIdx);
            $result[$nodeId] = $coreNumber;
        }

        return $result;
    }

    /**
     * Get all neighbors of a node (undirected view).
     */
    private function getNeighbors(AlgorithmGraph $ag, int $nodeIdx): array
    {
        $successors = $ag->successors()[$nodeIdx] ?? [];

        if ($ag->isDirected()) {
            // For k-core, we typically consider the undirected version
            $predecessors = $ag->predecessors()[$nodeIdx] ?? [];
            return array_unique(array_merge($successors, $predecessors));
        }

        return $successors;
    }
}