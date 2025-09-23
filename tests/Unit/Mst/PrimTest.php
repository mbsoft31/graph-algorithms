<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Mst\Prim;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;

it('finds MST with correct total weight', function () {
    $fixture = GraphFixtures::weightedSquare();

    $result = (new Prim())->findMst($fixture['graph']);

    expect($result)->not->toBeNull();
    expect($result->edgeCount())->toBe(3);
    expect($result->totalWeight)->toEqualWithDelta(6.0, 0.0001);
});

it('returns null for disconnected graphs', function () {
    $fixture = GraphFixtures::disconnectedGraph();

    $result = (new Prim())->findMst($fixture['graph']);
    expect($result)->toBeNull();
});
