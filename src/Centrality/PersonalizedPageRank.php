<?php
namespace Mbsoft\Graph\Algorithms\Centrality;
use Mbsoft\Graph\Algorithms\Contracts\CentralityAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;
/** Random Walk with Restart / Personalized PageRank. */
final class PersonalizedPageRank implements CentralityAlgorithmInterface
{
    /** @var array<string,float> */
    private array $teleport; // unnormalized preferences

    public function __construct(
        array $teleportVector = [],     // nodeId => weight; empty means uniform
        private readonly float $dampingFactor = 0.85,
        private readonly int $maxIterations = 100,
        private readonly float $tolerance = 1e-6
    ) {
        if ($dampingFactor < 0 || $dampingFactor > 1) {
            throw new \InvalidArgumentException('Damping factor must be between 0 and 1');
        }
        $this->teleport = $teleportVector;
    }

    public function compute(GraphInterface $graph): array
    {
        $ag = new AlgorithmGraph($graph, needPredecessors: true);
        $N = $ag->nodeCount();

        if ($N === 0) {
            return [];
        }

        $ids = $ag->ids();
        $pred = $ag->predecessors();
        $out = $ag->successors();
        $outDegrees = array_map('count', $out);

        // Normalize teleport vector
        $teleportIdx = array_fill(0, $N, 0.0);
        if (empty($this->teleport)) {
            $teleportIdx = array_fill(0, $N, 1.0 / $N);
        } else {
            $sum = array_sum($this->teleport);
            if ($sum <= 0) {
                throw new \InvalidArgumentException('Teleport vector weights must sum to a positive value');
            }
            foreach ($this->teleport as $nodeId => $weight) {
                try {
                    $idx = $ids->index($nodeId);
                    $teleportIdx[$idx] = $weight / $sum;
                } catch (\InvalidArgumentException) {
                    // Node in teleport vector not in graph - ignore
                    continue;
                }
            }
            // Re-normalize if some nodes were ignored
            $finalSum = array_sum($teleportIdx);
            if ($finalSum === 0) {
                $teleportIdx = array_fill(0, $N, 1.0 / $N);
            } else {
                foreach ($teleportIdx as &$w) $w /= $finalSum;
            }
        }

        // Initialize ranks
        $ranks = array_fill(0, $N, 1.0 / $N);

        for ($iteration = 0; $iteration < $this->maxIterations; $iteration++) {
            $newRanks = array_fill(0, $N, 0.0);

            // Dangling node handling
            $danglingSum = 0.0;
            foreach ($outDegrees as $i => $degree) {
                if ($degree === 0) {
                    $danglingSum += $ranks[$i];
                }
            }

            for ($u = 0; $u < $N; $u++) {
                $rankSum = 0.0;
                foreach ($pred[$u] ?? [] as $v) {
                    $degree = $outDegrees[$v];
                    if ($degree > 0) {
                        $rankSum += $ranks[$v] / $degree;
                    }
                }

                $newRanks[$u] = (1.0 - $this->dampingFactor) * $teleportIdx[$u]
                    + $this->dampingFactor * ($rankSum + $danglingSum * $teleportIdx[$u]);
            }

            // Convergence check
            $delta = 0.0;
            for ($i = 0; $i < $N; $i++) {
                $delta += abs($newRanks[$i] - $ranks[$i]);
            }

            $ranks = $newRanks;

            if ($delta < $this->tolerance) {
                break;
            }
        }

        $result = [];
        foreach ($ranks as $idx => $rank) {
            $result[$ids->id($idx)] = $rank;
        }

        return $result;
    }
}
