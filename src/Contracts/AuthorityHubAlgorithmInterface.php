<?php
namespace Mbsoft\Graph\Algorithms\Contracts;
use Mbsoft\Graph\Contracts\GraphInterface;
/** HITS-style algorithms that return two scores per node (hub & authority). */
interface AuthorityHubAlgorithmInterface
{
    /** @return array<string, array{hub: float, authority: float}> */
    public function compute(GraphInterface $graph): array;
}
