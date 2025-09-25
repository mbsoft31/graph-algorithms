<?php
namespace Mbsoft\Graph\Algorithms\LinkPrediction;
use Mbsoft\Graph\Algorithms\Contracts\LinkPredictorInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;
final class CommonNeighbors implements LinkPredictorInterface
{
    public function score(GraphInterface $graph, string $u, string $v): float
    {
        $ag = new AlgorithmGraph($graph);
        // TODO: intersect neighbor sets of u and v on undirected view
        return 0.0;
    }
    public function scoresFrom(GraphInterface $graph, string $u, int $k = 20): array
    {
        // TODO: compute scores against non-neighbors of u, return top-k
        return [];
    }
}
