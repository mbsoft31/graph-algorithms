<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Topological\TopologicalSort;
use Mbsoft\Graph\Algorithms\Topological\CycleDetectedException;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;

it('sorts DAG correctly', function () {
    $fixture = GraphFixtures::simplePath();
    $order = (new TopologicalSort())->sort($fixture['graph']);
    expect($order)->toBe($fixture['expected']['topological_sort']);
});

it('throws exception on cycle', function () {
    $fixture = GraphFixtures::simpleCycle();
    expect(fn() => (new TopologicalSort())->sort($fixture['graph']))
        ->toThrow(CycleDetectedException::class);
});

it('rejects undirected graphs', function () {
    $fixture = GraphFixtures::disconnectedGraph();
    expect(fn() => (new TopologicalSort())->sort($fixture['graph']))
        ->toThrow(InvalidArgumentException::class);
});
