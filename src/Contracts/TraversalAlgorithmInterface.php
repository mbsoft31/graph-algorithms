<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Contracts;

use Mbsoft\Graph\Contracts\GraphInterface;

interface TraversalAlgorithmInterface
{
    /**
     * Traverses the graph starting from the given node.
     * 
     * @param GraphInterface $graph The graph to traverse
     * @param string $startNodeId The starting node ID
     * @return list<string> The traversal order as node IDs
     */
    public function traverse(GraphInterface $graph, string $startNodeId): array;
}
