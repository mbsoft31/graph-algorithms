<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Contracts;

use Mbsoft\Graph\Algorithms\Pathfinding\PathResult;
use Mbsoft\Graph\Contracts\GraphInterface;

interface PathfindingAlgorithmInterface
{
    /**
     * Finds the shortest path between two nodes.
     * 
     * @param GraphInterface $graph The graph to search
     * @param string $startNodeId The starting node ID
     * @param string $endNodeId The target node ID
     * @return PathResult|null The result object if a path is found, otherwise null
     */
    public function find(GraphInterface $graph, string $startNodeId, string $endNodeId): ?PathResult;
}
