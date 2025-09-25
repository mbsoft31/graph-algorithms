<?php
namespace Mbsoft\Graph\Algorithms\Contracts;
use Mbsoft\Graph\Contracts\GraphInterface;
/** Link prediction heuristics. */
interface LinkPredictorInterface
{
    /** Score a potential link between two nodes (higher = more likely). */
    public function score(GraphInterface $graph, string $u, string $v): float;

    /**
     * Recommend likely new neighbors for a given node (excluding existing neighbors).
     * @return array<string,float> Node ID -> score, typically top-k sorted by score desc.
     */
    public function scoresFrom(GraphInterface $graph, string $u, int $k = 20): array;
}
