<?php
namespace Mbsoft\Graph\Algorithms\Decomposition;
use Mbsoft\Graph\Algorithms\Contracts\CoreDecompositionInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;
/** k-core decomposition on the undirected view (standard definition). */
final class KCore implements CoreDecompositionInterface
{
    public function compute(GraphInterface $graph): array
    {
        $ag = new AlgorithmGraph($graph);
        if ($ag->nodeCount() === 0) return [];
        // TODO: implement peeling; return nodeId => core number
        return [];
    }
}
