<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Pathfinding;

/**
 * A readonly result object representing a path through a graph.
 */
final readonly class PathResult
{
    /**
     * @param list<string> $nodes The sequence of node IDs in the path
     * @param float $cost The total cost/weight of the path
     */
    public function __construct(
        public array $nodes,
        public float $cost
    ) {}

    /**
     * Returns the number of nodes in the path.
     */
    public function length(): int
    {
        return count($this->nodes);
    }

    /**
     * Returns the number of edges in the path.
     */
    public function edgeCount(): int
    {
        return max(0, $this->length() - 1);
    }

    /**
     * Returns true if this represents a valid path.
     */
    public function isValid(): bool
    {
        return count($this->nodes) > 0;
    }

    /**
     * Returns the starting node ID.
     */
    public function start(): ?string
    {
        return $this->nodes[0] ?? null;
    }

    /**
     * Returns the ending node ID.
     */
    public function end(): ?string
    {
        return $this->nodes[array_key_last($this->nodes)] ?? null;
    }
}
