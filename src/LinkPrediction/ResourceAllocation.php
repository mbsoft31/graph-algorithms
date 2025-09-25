<?php
namespace Mbsoft\Graph\Algorithms\LinkPrediction;
use Mbsoft\Graph\Algorithms\Contracts\LinkPredictorInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;
final class ResourceAllocation implements LinkPredictorInterface
{
    public function score(GraphInterface $graph, string $u, string $v): float
    {
        $ag = new AlgorithmGraph($graph);
        // TODO: sum over common neighbors w of 1/deg(w)
        return 0.0;
    }
    public function scoresFrom(GraphInterface $graph, string $u, int $k = 20): array
    {
        // TODO: compute top-k by RA index
        return [];
    }
}
