<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Pathfinding\Dijkstra;
use Mbsoft\Graph\Domain\Graph;

it('finds shortest path in weighted graph', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B', ['weight' => 4]);
    $graph->addEdge('A', 'C', ['weight' => 2]);
    $graph->addEdge('B', 'C', ['weight' => 1]);
    $graph->addEdge('B', 'D', ['weight' => 5]);
    $graph->addEdge('C', 'D', ['weight' => 8]);

    $dijkstra = new Dijkstra();
    $result = $dijkstra->find($graph, 'A', 'D');

    expect($result)->not->toBeNull()
        ->and($result->nodes)->toMatchArray(['A', 'B', 'D'])
        ->and($result->cost)->toBe(9.0);
});

it('returns null for unreachable nodes', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B');
    $graph->addNode('C');

    $dijkstra = new Dijkstra();
    $result = $dijkstra->find($graph, 'A', 'C');

    expect($result)->toBeNull();
});

it('throws on negative weights', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B', ['weight' => -1]);

    $dijkstra = new Dijkstra();

    expect(fn() => $dijkstra->find($graph, 'A', 'B'))
        ->toThrow(\InvalidArgumentException::class, 'non-negative');
});