<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Centrality\DegreeCentrality;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;

it('computes degree centrality for simple path', function () {
    $fixture = GraphFixtures::simplePath();

    $in = (new DegreeCentrality(DegreeCentrality::IN_DEGREE))->compute($fixture['graph']);
    $out = (new DegreeCentrality(DegreeCentrality::OUT_DEGREE))->compute($fixture['graph']);
    $total = (new DegreeCentrality(DegreeCentrality::TOTAL_DEGREE))->compute($fixture['graph']);

    expect($in)->toMatchArray($fixture['expected']['degree_centrality']['in']);
    expect($out)->toMatchArray($fixture['expected']['degree_centrality']['out']);
    expect($total)->toMatchArray($fixture['expected']['degree_centrality']['total']);
});

it('normalizes degree centrality when requested', function () {
    $fixture = GraphFixtures::completeTriangle();
    $normalized = (new DegreeCentrality(DegreeCentrality::OUT_DEGREE, normalized: true))->compute($fixture['graph']);

    foreach ($normalized as $value) {
        expect($value)->toBeFloat()->toBeGreaterThan(0)->toBeLessThanOrEqual(1);
    }
});
