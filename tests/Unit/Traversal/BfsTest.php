<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Traversal\Bfs;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;

it('traverses breadth-first from start node', function () {
    $fixture = GraphFixtures::starGraph();

    $order = (new Bfs())->traverse($fixture['graph'], 'A');

    expect($order)->toBe($fixture['expected']['bfs_from_A']);
});

it('returns empty when start node missing', function () {
    $fixture = GraphFixtures::simplePath();
    $order = (new Bfs())->traverse($fixture['graph'], 'Z');
    expect($order)->toBe([]);
});
