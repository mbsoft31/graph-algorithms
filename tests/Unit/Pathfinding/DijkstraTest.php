<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Pathfinding\Dijkstra;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;
use Mbsoft\Graph\Algorithms\Tests\Helpers\GraphBuilder;

it('finds shortest path in simple graph', function () {
    $fixture = GraphFixtures::simplePath();
    $algo = new Dijkstra();

    $result = $algo->find($fixture['graph'], 'A', 'C');

    expect($result)->not->toBeNull();
    expect($result->nodes)->toBe(['A', 'B', 'C']);
    expect($result->cost)->toEqualWithDelta(3.0, 0.0001);
});

it('returns null when unreachable', function () {
    $fixture = GraphFixtures::disconnectedGraph();
    $algo = new Dijkstra();

    $result = $algo->find($fixture['graph'], 'A', 'C');

    expect($result)->toBeNull();
});

it('throws on negative weights', function () {
    $graph = GraphBuilder::create()
        ->directed()
        ->addWeightedEdge('A', 'B', -1.0)
        ->build();

    $algo = new Dijkstra();
    expect(fn() => $algo->find($graph, 'A', 'B'))->toThrow(InvalidArgumentException::class);
});

it('supports custom weight callbacks', function () {
    $graph = GraphBuilder::create()
        ->directed()
        ->addEdge('A', 'B', ['time' => 2, 'distance' => 10])
        ->addEdge('B', 'C', ['time' => 1, 'distance' => 1])
        ->build();

    $weightByTime = new Dijkstra(fn(array $attrs) => $attrs['time']);
    $result = $weightByTime->find($graph, 'A', 'C');

    expect($result)->not->toBeNull();
    expect($result->cost)->toEqualWithDelta(3.0, 0.0001);
});
