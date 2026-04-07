<?php

namespace Mbsoft\Graph\Algorithms\Centrality;
use Mbsoft\Graph\Algorithms\Contracts\CentralityAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;
/** Brandes betweenness centrality (unweighted baseline). */
final readonly class Betweenness implements CentralityAlgorithmInterface
{
    public function __construct(private bool $normalized = true) {}

    public function compute(GraphInterface $graph): array
    {
        $ag = new AlgorithmGraph($graph, needPredecessors: true);
        $N = $ag->nodeCount();
        if ($N === 0) return [];
        
        $ids = $ag->ids();
        $successors = $ag->successors();
        $betweenness = array_fill(0, $N, 0.0);

        for ($s = 0; $s < $N; $s++) {
            // BFS to find shortest paths
            $stack = [];
            $pred = array_fill(0, $N, []);
            $sigma = array_fill(0, $N, 0.0);
            $sigma[$s] = 1.0;
            $dist = array_fill(0, $N, -1);
            $dist[$s] = 0;
            
            $queue = [$s];
            $head = 0;

            while ($head < count($queue)) {
                $v = $queue[$head++];
                $stack[] = $v;
                
                foreach ($successors[$v] ?? [] as $w) {
                    // Path discovery
                    if ($dist[$w] < 0) {
                        $dist[$w] = $dist[$v] + 1;
                        $queue[] = $w;
                    }
                    
                    // Path counting
                    if ($dist[$w] === $dist[$v] + 1) {
                        $sigma[$w] += $sigma[$v];
                        $pred[$w][] = $v;
                    }
                }
            }

            // Accumulation
            $delta = array_fill(0, $N, 0.0);
            while (!empty($stack)) {
                $w = array_pop($stack);
                foreach ($pred[$w] as $v) {
                    $delta[$v] += ($sigma[$v] / $sigma[$w]) * (1.0 + $delta[$w]);
                }
                if ($w !== $s) {
                    $betweenness[$w] += $delta[$w];
                }
            }
        }

        // Normalization for directed graphs: 1 / ((N-1)(N-2))
        if ($this->normalized && $N > 2) {
            $scale = 1.0 / (($N - 1) * ($N - 2));
            foreach ($betweenness as &$val) {
                $val *= $scale;
            }
        }

        $result = [];
        foreach ($betweenness as $idx => $val) {
            $result[$ids->id($idx)] = $val;
        }

        return $result;
    }
}
