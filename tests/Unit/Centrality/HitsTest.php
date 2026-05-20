<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Centrality\Hits;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;
use Mbsoft\Graph\Algorithms\Tests\Helpers\GraphBuilder;

it('returns empty scores for an empty graph', function () {
    $fixture = GraphFixtures::emptyGraph();

    expect((new Hits())->compute($fixture['graph']))->toBe([]);
});

it('identifies hubs and authorities in a directed graph', function () {
    $graph = GraphBuilder::create()
        ->directed()
        ->addEdge('A', 'C')
        ->addEdge('B', 'C')
        ->addEdge('B', 'D')
        ->build();

    $scores = (new Hits())->compute($graph);

    expect($scores['B']['hub'])->toBeGreaterThan($scores['A']['hub']);
    expect($scores['C']['authority'])->toBeGreaterThan($scores['D']['authority']);
});

it('validates constructor parameters', function () {
    expect(fn () => new Hits(maxIterations: 0))->toThrow(InvalidArgumentException::class);
    expect(fn () => new Hits(tolerance: 0.0))->toThrow(InvalidArgumentException::class);
});
