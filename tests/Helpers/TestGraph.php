<?php

namespace Mbsoft\Graph\Algorithms\Tests\Helpers;

use Mbsoft\Graph\Contracts\GraphInterface;

/**
 * Minimal GraphInterface implementation for tests.
 */
final class TestGraph implements GraphInterface
{
    /** @param list<string> $nodes @param list<object> $edges */
    public function __construct(
        private array $nodes,
        private array $edges,
        private bool $directed,
    ) {}

    public function isDirected(): bool { return $this->directed; }
    /** @return list<string> */
    public function nodes(): array { return $this->nodes; }
    /** @return list<object> */
    public function edges(): array { return $this->edges; }

    public function hasNode(string $id): bool
    {
        return in_array($id, $this->nodes, true);
    }

    public function hasEdge(string $u, string $v): bool
    {
        foreach ($this->edges as $e) {
            if ($e->from === $u && $e->to === $v) return true;
            if (!$this->directed && $e->from === $v && $e->to === $u) return true;
        }
        return false;
    }

    /** @return list<string> */
    public function successors(string $id): array
    {
        $succ = [];
        foreach ($this->edges as $e) {
            if ($e->from === $id) $succ[] = $e->to;
            if (!$this->directed && $e->to === $id) $succ[] = $e->from;
        }
        return array_values(array_unique($succ));
    }

    /** @return list<string> */
    public function predecessors(string $id): array
    {
        $pred = [];
        foreach ($this->edges as $e) {
            if ($e->to === $id) $pred[] = $e->from;
            if (!$this->directed && $e->from === $id) $pred[] = $e->to;
        }
        return array_values(array_unique($pred));
    }

    public function nodeCount(): int { return count($this->nodes); }
    public function edgeCount(): int { return count($this->edges); }

    public function nodeAttrs(string $id): array
    {
        return [];
    }

    public function edgeAttrs(string $u, string $v): array
    {
        return [];
    }
}