<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Components;

use InvalidArgumentException;
use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use SplStack;

/**
 * Tarjan's Strongly Connected Components algorithm.
 *
 * Time complexity: O(V + E)
 * Space complexity: O(V)
 *
 * Finds all strongly connected components in a directed graph using a single DFS pass.
 */
final class StronglyConnected
{
    /** @var array<int, int> */
    private array $indices = [];
    private array $lowlinks = [];
    private array $onStack = [];
    private SplStack $stack;
    private int $index = 0;
    private array $components = [];

    /**
     * @param GraphInterface $graph Must be a directed graph
     * @return list<list<string>> List of strongly connected components, each containing node IDs
     */
    public function findComponents(GraphInterface $graph): array
    {
        if (!$graph->isDirected()) {
            throw new InvalidArgumentException('Strongly connected components require a directed graph');
        }

        $algoGraph = new AlgorithmGraph($graph);

        if ($algoGraph->nodeCount() === 0) {
            return [];
        }

        $this->initialize($algoGraph->nodeCount());
        $successors = $algoGraph->successors();

        // Run Tarjan's algorithm on each unvisited node
        foreach ($algoGraph->nodes() as $nodeIdx) {
            if (!isset($this->indices[$nodeIdx])) {
                $this->strongConnect($nodeIdx, $successors);
            }
        }

        // Convert results back to node IDs
        $ids = $algoGraph->ids();
        $result = [];

        foreach ($this->components as $component) {
            $componentIds = array_map(fn(int $i): string => $ids->id($i), $component);
            $result[] = $componentIds;
        }

        return $result;
    }

    private function initialize(int $nodeCount): void
    {
        $this->indices = [];
        $this->lowlinks = [];
        $this->onStack = array_fill(0, $nodeCount, false);
        $this->stack = new SplStack();
        $this->index = 0;
        $this->components = [];
    }

    private function strongConnect(int $v, array $successors): void
    {
        $this->indices[$v] = $this->index;
        $this->lowlinks[$v] = $this->index;
        $this->index++;

        $this->stack->push($v);
        $this->onStack[$v] = true;

        foreach ($successors[$v] ?? [] as $w) {
            if (!isset($this->indices[$w])) {
                $this->strongConnect($w, $successors);
                $this->lowlinks[$v] = min($this->lowlinks[$v], $this->lowlinks[$w]);
            } elseif ($this->onStack[$w]) {
                $this->lowlinks[$v] = min($this->lowlinks[$v], $this->indices[$w]);
            }
        }

        if ($this->lowlinks[$v] === $this->indices[$v]) {
            $component = [];
            do {
                $w = $this->stack->pop();
                $this->onStack[$w] = false;
                $component[] = $w;
            } while ($w !== $v);

            $this->components[] = $component;
        }
    }
}
