<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Centrality;

use InvalidArgumentException;
use Mbsoft\Graph\Algorithms\Contracts\AuthorityHubAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;

/** HITS (hubs and authorities) centrality. */
final readonly class Hits implements AuthorityHubAlgorithmInterface
{
    public function __construct(
        private int $maxIterations = 100,
        private float $tolerance = 1e-6
    ) {
        if ($maxIterations < 1) {
            throw new InvalidArgumentException('Max iterations must be at least 1');
        }

        if ($tolerance <= 0) {
            throw new InvalidArgumentException('Tolerance must be positive');
        }
    }

    public function compute(GraphInterface $graph): array
    {
        $algoGraph = new AlgorithmGraph($graph, needPredecessors: true);

        if ($algoGraph->nodeCount() === 0) {
            return [];
        }

        $nodeCount = $algoGraph->nodeCount();
        $hubs = array_fill(0, $nodeCount, 1.0);
        $authorities = array_fill(0, $nodeCount, 1.0);

        for ($iteration = 0; $iteration < $this->maxIterations; $iteration++) {
            $nextAuthorities = $this->computeAuthorityScores($algoGraph, $hubs);
            $nextHubs = $this->computeHubScores($algoGraph, $nextAuthorities);

            $this->normalize($nextAuthorities);
            $this->normalize($nextHubs);

            $delta = $this->delta($authorities, $nextAuthorities) + $this->delta($hubs, $nextHubs);
            $authorities = $nextAuthorities;
            $hubs = $nextHubs;

            if ($delta < $this->tolerance) {
                break;
            }
        }

        $result = [];
        $ids = $algoGraph->ids();

        for ($i = 0; $i < $nodeCount; $i++) {
            $result[$ids->id($i)] = [
                'hub' => $hubs[$i],
                'authority' => $authorities[$i],
            ];
        }

        return $result;
    }

    /**
     * @param list<float> $hubs
     * @return list<float>
     */
    private function computeAuthorityScores(AlgorithmGraph $graph, array $hubs): array
    {
        $scores = array_fill(0, $graph->nodeCount(), 0.0);

        for ($nodeIdx = 0; $nodeIdx < $graph->nodeCount(); $nodeIdx++) {
            foreach ($graph->predecessors()[$nodeIdx] ?? [] as $predecessorIdx) {
                $scores[$nodeIdx] += $hubs[$predecessorIdx];
            }
        }

        return $scores;
    }

    /**
     * @param list<float> $authorities
     * @return list<float>
     */
    private function computeHubScores(AlgorithmGraph $graph, array $authorities): array
    {
        $scores = array_fill(0, $graph->nodeCount(), 0.0);

        for ($nodeIdx = 0; $nodeIdx < $graph->nodeCount(); $nodeIdx++) {
            foreach ($graph->successors()[$nodeIdx] ?? [] as $successorIdx) {
                $scores[$nodeIdx] += $authorities[$successorIdx];
            }
        }

        return $scores;
    }

    /**
     * @param list<float> $scores
     */
    private function normalize(array &$scores): void
    {
        $norm = sqrt(array_sum(array_map(
            fn (float $score): float => $score * $score,
            $scores,
        )));

        if ($norm === 0.0) {
            return;
        }

        foreach ($scores as $index => $score) {
            $scores[$index] = $score / $norm;
        }
    }

    /**
     * @param list<float> $previous
     * @param list<float> $current
     */
    private function delta(array $previous, array $current): float
    {
        $delta = 0.0;

        foreach ($previous as $index => $score) {
            $delta += abs($current[$index] - $score);
        }

        return $delta;
    }
}
