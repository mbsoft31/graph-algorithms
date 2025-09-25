<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Pathfinding\BellmanFord;
use Mbsoft\Graph\Domain\Graph;

it('finds shortest path with positive weights', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B', ['weight' => 4]);
    $graph->addEdge('A', 'C', ['weight' => 2]);
    $graph->addEdge('B', 'C', ['weight' => 3]);
    $graph->addEdge('B', 'D', ['weight' => 2]);
    $graph->addEdge('C', 'D', ['weight' => 4]);
    $graph->addEdge('C', 'E', ['weight' => 3]);
    $graph->addEdge('D', 'E', ['weight' => 1]);

    $bellmanFord = new BellmanFord();
    $result = $bellmanFord->find($graph, 'A', 'E');

    expect($result)->not->toBeNull()
        ->and($result->cost)->toBe(5.0); // A->C->E = 2+3 = 5
});

it('handles negative weights correctly', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B', ['weight' => 1]);
    $graph->addEdge('A', 'C', ['weight' => 4]);
    $graph->addEdge('B', 'C', ['weight' => -3]);
    $graph->addEdge('B', 'D', ['weight' => 2]);
    $graph->addEdge('C', 'D', ['weight' => 3]);

    $bellmanFord = new BellmanFord();
    $result = $bellmanFord->find($graph, 'A', 'D');

    expect($result)->not->toBeNull()
        ->and($result->cost)->toBe(1.0); // A->B->D = 1+2 = 3, but A->B->C->D = 1+(-3)+3 = 1 is shorter
});

it('detects negative cycles', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B', ['weight' => 1]);
    $graph->addEdge('B', 'C', ['weight' => -3]);
    $graph->addEdge('C', 'B', ['weight' => 1]); // Negative cycle: B->C->B = -2

    $bellmanFord = new BellmanFord();

    expect(fn() => $bellmanFord->find($graph, 'A', 'C'))
        ->toThrow(\InvalidArgumentException::class, 'negative cycle');
});

it('returns null for unreachable nodes', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B', ['weight' => 1]);
    $graph->addNode('C'); // Isolated node

    $bellmanFord = new BellmanFord();
    $result = $bellmanFord->find($graph, 'A', 'C');

    expect($result)->toBeNull();
});

it('returns null for non-existent nodes', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B', ['weight' => 1]);

    $bellmanFord = new BellmanFord();
    $result = $bellmanFord->find($graph, 'A', 'NonExistent');

    expect($result)->toBeNull();
});

it('handles single node path', function () {
    $graph = new Graph();
    $graph->addNode('A');

    $bellmanFord = new BellmanFord();
    $result = $bellmanFord->find($graph, 'A', 'A');

    expect($result)->not->toBeNull()
        ->and($result->cost)->toBe(0.0);
});

it('works with custom weight extractor', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B', ['cost' => 5, 'weight' => 1]);
    $graph->addEdge('A', 'C', ['cost' => 2, 'weight' => 3]);

    $bellmanFord = new BellmanFord(fn($attrs) => $attrs['cost'] ?? 1.0);
    $result = $bellmanFord->find($graph, 'A', 'C');

    expect($result)->not->toBeNull()
        ->and($result->cost)->toBe(2.0); // Uses 'cost' instead of 'weight'
});

it('handles zero weights', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B', ['weight' => 0]);
    $graph->addEdge('B', 'C', ['weight' => 1]);

    $bellmanFord = new BellmanFord();
    $result = $bellmanFord->find($graph, 'A', 'C');

    expect($result)->not->toBeNull()
        ->and($result->cost)->toBe(1.0);
});

it('early terminates when no updates occur', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B', ['weight' => 1]);
    $graph->addEdge('B', 'C', ['weight' => 1]);

    $bellmanFord = new BellmanFord();
    $result = $bellmanFord->find($graph, 'A', 'C');

    expect($result)->not->toBeNull()
        ->and($result->cost)->toBe(2.0);
});