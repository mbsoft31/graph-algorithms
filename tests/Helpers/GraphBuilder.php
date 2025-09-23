<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Tests\Helpers;

use Mbsoft\Graph\Contracts\GraphInterface;

/**
 * GraphBuilder provides a fluent API for building test graphs.
 * It constructs a lightweight TestGraph that implements GraphInterface for unit tests.
 */
final class GraphBuilder
{
    /** @var array<string, true> */
    private array $nodes = [];
    /** @var list<object> */
    private array $edges = [];
    private bool $directed = true;

    public static function create(): self
    {
        return new self();
    }

    public function directed(bool $directed = true): self
    {
        $this->directed = $directed;
        return $this;
    }

    public function undirected(): self
    {
        return $this->directed(false);
    }

    public function addNode(string $id): self
    {
        $this->nodes[$id] = true;
        return $this;
    }

    /**
     * @param list<string> $ids
     */
    public function addNodes(string ...$ids): self
    {
        foreach ($ids as $id) {
            $this->addNode($id);
        }
        return $this;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function addEdge(string $from, string $to, array $attributes = []): self
    {
        $this->nodes[$from] = true;
        $this->nodes[$to] = true;
        $this->edges[] = (object) [
            'from' => $from,
            'to' => $to,
            'attributes' => $attributes,
        ];
        return $this;
    }

    public function addWeightedEdge(string $from, string $to, float $weight): self
    {
        return $this->addEdge($from, $to, ['weight' => $weight]);
    }

    /**
     * @param list<string> $nodes
     * @param list<float> $weights
     */
    public function path(array $nodes, array $weights = []): self
    {
        for ($i = 0; $i < count($nodes) - 1; $i++) {
            $w = $weights[$i] ?? 1.0;
            $this->addWeightedEdge($nodes[$i], $nodes[$i + 1], $w);
        }
        return $this;
    }

    /**
     * @param list<string> $nodes
     * @param list<float> $weights
     */
    public function cycle(array $nodes, array $weights = []): self
    {
        $this->path($nodes, $weights);
        $lastW = $weights[count($nodes) - 1] ?? 1.0;
        $this->addWeightedEdge($nodes[count($nodes) - 1], $nodes[0], $lastW);
        return $this;
    }

    /**
     * @param list<string> $nodes
     */
    public function complete(array $nodes, float $weight = 1.0): self
    {
        foreach ($nodes as $from) {
            foreach ($nodes as $to) {
                if ($from !== $to) {
                    $this->addWeightedEdge($from, $to, $weight);
                }
            }
        }
        return $this;
    }

    /**
     * @param list<string> $leaves
     */
    public function star(string $center, array $leaves, float $weight = 1.0): self
    {
        foreach ($leaves as $leaf) {
            $this->addWeightedEdge($center, $leaf, $weight);
        }
        return $this;
    }

    public function build(): GraphInterface
    {
        return new TestGraph(array_keys($this->nodes), $this->edges, $this->directed);
    }
}
