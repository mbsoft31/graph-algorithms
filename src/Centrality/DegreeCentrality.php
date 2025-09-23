<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Centrality;

use InvalidArgumentException;
use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\Algorithms\Contracts\CentralityAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;

/**
 * Degree centrality algorithm.
 *
 * Time complexity: O(V + E)
 * Space complexity: O(V)
 *
 * Computes in-degree, out-degree, or total degree centrality for each node.
 */
final class DegreeCentrality implements CentralityAlgorithmInterface
{
    public const string IN_DEGREE = 'in';
    public const string OUT_DEGREE = 'out';
    public const string TOTAL_DEGREE = 'total';

    public function __construct(
        private readonly string $mode = self::TOTAL_DEGREE,
        private readonly bool $normalized = false
    ) {
        if (!in_array($mode, [self::IN_DEGREE, self::OUT_DEGREE, self::TOTAL_DEGREE])) {
            throw new InvalidArgumentException('Mode must be in, out, or total');
        }
    }

    public function compute(GraphInterface $graph): array
    {
        $algoGraph = new AlgorithmGraph($graph, needPredecessors: true);

        if ($algoGraph->nodeCount() === 0) {
            return [];
        }

        $successors = $algoGraph->successors();
        $predecessors = $algoGraph->predecessors();
        $N = $algoGraph->nodeCount();

        $scores = [];
        $maxScore = 0.0;

        for ($i = 0; $i < $N; $i++) {
            $inDegree = count($predecessors[$i] ?? []);
            $outDegree = count($successors[$i] ?? []);

            $score = match ($this->mode) {
                self::IN_DEGREE => $inDegree,
                self::OUT_DEGREE => $outDegree,
                self::TOTAL_DEGREE => $graph->isDirected() ? $inDegree + $outDegree : $outDegree,
            };

            $scores[$i] = (float) $score;
            $maxScore = max($maxScore, $scores[$i]);
        }

        // Normalize if requested
        if ($this->normalized && $maxScore > 0) {
            $denominator = $graph->isDirected() ? ($N - 1) : ($N - 1);
            foreach ($scores as $i => $score) {
                $scores[$i] = $score / $denominator;
            }
        }

        // Convert back to node IDs
        $result = [];
        $ids = $algoGraph->ids();
        foreach ($scores as $nodeIdx => $score) {
            $result[$ids->id($nodeIdx)] = $score;
        }

        return $result;
    }
}
