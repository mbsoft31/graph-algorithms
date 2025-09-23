<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Pathfinding\AStar;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;
use Mbsoft\Graph\Algorithms\Tests\Helpers\GraphBuilder;

it('finds shortest path with zero heuristic (Dijkstra equivalent)', function () {
    $fixture = GraphFixtures::simplePath();
    $algo = new AStar();

    $result = $algo->find($fixture['graph'], 'A', 'C');

    expect($result)->not->toBeNull();
    expect($result->nodes)->toBe(['A', 'B', 'C']);
    expect($result->cost)->toEqualWithDelta(3.0, 0.0001);
});

it('returns null for unreachable nodes', function () {
    $fixture = GraphFixtures::disconnectedGraph();
    $algo = new AStar();

    $result = $algo->find($fixture['graph'], 'A', 'C');
    expect($result)->toBeNull();
});

it('throws on negative weights', function () {
    $graph = GraphBuilder::create()
        ->directed()
        ->addWeightedEdge('A', 'B', -1.0)
        ->build();

    $algo = new AStar();
    expect(fn() => $algo->find($graph, 'A', 'B'))->toThrow(InvalidArgumentException::class);
});
