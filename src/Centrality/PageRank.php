<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Centrality;

use InvalidArgumentException;
use Mbsoft\Graph\Algorithms\Contracts\CentralityAlgorithmInterface;
use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;

/**
 * PageRank centrality algorithm with damping factor and convergence detection.
 *
 * Time complexity: O(k * (V + E)) where k is the number of iterations
 * Space complexity: O(V)
 */
final readonly class PageRank implements CentralityAlgorithmInterface
{
    public function __construct(
        private float $dampingFactor = 0.85,
        private int   $maxIterations = 100,
        private float $tolerance = 1e-6
    ) {
        if ($dampingFactor < 0 || $dampingFactor > 1) {
            throw new InvalidArgumentException('Damping factor must be between 0 and 1');
        }
    }

    public function compute(GraphInterface $graph): array
    {
        $algoGraph = new AlgorithmGraph($graph, needPredecessors: true);

        if ($algoGraph->nodeCount() === 0) {
            return [];
        }

        $out = $algoGraph->successors();
        $pred = $algoGraph->predecessors();
        $N = $algoGraph->nodeCount();

        // Initialize all ranks to 1/N
        $ranks = array_fill(0, $N, 1.0 / $N);

        for ($iteration = 0; $iteration < $this->maxIterations; $iteration++) {
            $newRanks = array_fill(0, $N, 0.0);

            // Cache out-degrees and calculate dangling sum
            $outDegrees = array_map('count', $out);
            $danglingSum = 0.0;
            foreach ($outDegrees as $i => $degree) {
                if ($degree === 0) {
                    $danglingSum += $ranks[$i];
                }
            }

            // Calculate new ranks
            for ($u = 0; $u < $N; $u++) {
                $rankSum = 0.0;
                foreach ($pred[$u] ?? [] as $v) {
                    $degree = $outDegrees[$v];
                    if ($degree > 0) {
                        $rankSum += $ranks[$v] / $degree;
                    }
                }

                $newRanks[$u] = (1.0 - $this->dampingFactor) / $N
                    + $this->dampingFactor * ($rankSum + $danglingSum / $N);
            }

            // Check for convergence using L1 norm
            $delta = 0.0;
            for ($i = 0; $i < $N; $i++) {
                $delta += abs($newRanks[$i] - $ranks[$i]);
            }

            $ranks = $newRanks;

            if ($delta < $this->tolerance) {
                break;
            }
        }

        // Map integer-indexed ranks back to string IDs
        $result = [];
        $ids = $algoGraph->ids();
        foreach ($ranks as $nodeIdx => $rank) {
            $result[$ids->id($nodeIdx)] = $rank;
        }

        return $result;
    }
}
