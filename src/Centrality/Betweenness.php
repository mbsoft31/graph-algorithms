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
        $ag = new AlgorithmGraph($graph);
        if ($ag->nodeCount() === 0) return [];
        // TODO: implement Brandes algorithm; return nodeId => score
        return [];
    }
}
