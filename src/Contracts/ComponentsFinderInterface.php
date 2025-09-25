<?php
namespace Mbsoft\Graph\Algorithms\Contracts;
use Mbsoft\Graph\Contracts\GraphInterface;
/** Connected components (typically for undirected or weakly connected views). */
interface ComponentsFinderInterface
{
    /** @return list<list<string>> List of components; each is a list of node IDs. */
    public function findComponents(GraphInterface $graph): array;
}
