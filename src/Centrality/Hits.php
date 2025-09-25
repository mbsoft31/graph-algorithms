<?php
namespace Mbsoft\Graph\Algorithms\Centrality;
use Mbsoft\Graph\Algorithms\Contracts\AuthorityHubAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;
/** HITS (hubs and authorities). */
final class Hits implements AuthorityHubAlgorithmInterface
{
    public function __construct(
        private readonly int $maxIterations = 100,
        private readonly float $tolerance = 1e-6
    ) {}

    public function compute(GraphInterface $graph): array
    {
        $ag = new AlgorithmGraph($graph);
        if ($ag->nodeCount === 0) return [];
        // TODO: implement HITS power-iteration; return nodeId => ['hub'=>, 'authority'=>]
        return [];
    }
}
