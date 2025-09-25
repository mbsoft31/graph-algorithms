<?php
namespace Mbsoft\Graph\Algorithms\Traversal;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;
/** BFS variant that returns order and distance map without touching existing Bfs API. */
final class BfsDistance
{
    /** @return array{order: list<string>, distance: array<string,int>} */
    public function traverseWithDistance(GraphInterface $graph, string $start): array
    {
        $ag = new AlgorithmGraph($graph);
        if (!$graph->hasNode($start)) {
            return ['order' => [], 'distance' => []];
        }
        // TODO: implement queue-based BFS using $ag->successors
        return ['order' => [], 'distance' => []];
    }
}
