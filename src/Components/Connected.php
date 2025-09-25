<?php
namespace Mbsoft\Graph\Algorithms\Components;
use Mbsoft\Graph\Algorithms\Contracts\ComponentsFinderInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;
/** Connected components on an undirected view (treat edges as bidirectional). */
final class Connected implements ComponentsFinderInterface
{
    public function findComponents(GraphInterface $graph): array
    {
        $ag = new AlgorithmGraph($graph);
        if ($ag->nodeCount === 0) return [];
        // TODO: merge succ/pred neighbor sets; collect components with iterative DFS/BFS.
        return [];
    }
}
