<?php
namespace Mbsoft\Graph\Algorithms\Contracts;
use Mbsoft\Graph\Contracts\GraphInterface;
/** Community detection algorithms (label propagation, Louvain, etc.). */
interface CommunityDetectorInterface
{
    /** @return array<string, int|string> Map of node ID -> community label */
    public function detect(GraphInterface $graph): array;
}
