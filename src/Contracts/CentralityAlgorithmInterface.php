<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Contracts;

use Mbsoft\Graph\Contracts\GraphInterface;

interface CentralityAlgorithmInterface
{
    /**
     * Computes the centrality score for each node in the graph.
     * 
     * @param GraphInterface $graph The graph to analyze
     * @return array<string, float> Map of node ID to its centrality score
     */
    public function compute(GraphInterface $graph): array;
}
