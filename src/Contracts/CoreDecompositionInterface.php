<?php
namespace Mbsoft\Graph\Algorithms\Contracts;
use Mbsoft\Graph\Contracts\GraphInterface;
/** k-core decomposition: returns the core number per node. */
interface CoreDecompositionInterface
{
    /** @return array<string, int> Map of node ID -> core number (k) */
    public function compute(GraphInterface $graph): array;
}
