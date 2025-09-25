<?php
namespace Mbsoft\Graph\Algorithms\Centrality;
use Mbsoft\Graph\Algorithms\Contracts\CentralityAlgorithmInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;
/** Random Walk with Restart / Personalized PageRank. */
final class PersonalizedPageRank implements CentralityAlgorithmInterface
{
    /** @var array<string,float> */
    private array $teleport; // unnormalized preferences

    public function __construct(
        array $teleportVector = [],     // nodeId => weight; empty means uniform
        private readonly float $dampingFactor = 0.85,
        private readonly int $maxIterations = 100,
        private readonly float $tolerance = 1e-6
    ) {
        $this->teleport = $teleportVector;
    }

    public function compute(GraphInterface $graph): array
    {
        $ag = new AlgorithmGraph($graph);
        if ($ag->nodeCount() === 0) return [];
        // TODO: implement real RWR; keep signature stable for incremental implementation.
        return [];
    }
}
