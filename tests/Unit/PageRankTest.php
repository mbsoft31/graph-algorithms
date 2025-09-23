<?php
declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Centrality\PageRank;
use Mbsoft\Graph\Domain\Graph;

it('computes PageRank for a simple graph', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B');
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'A');

    $pagerank = new PageRank();
    $scores = $pagerank->compute($graph);

    expect($scores)->toBeArray()
        ->and($scores)->toHaveKeys(['A', 'B', 'C'])
        ->and(array_sum($scores))->toBeGreaterThan(0.99)->toBeLessThan(1.01);
});

it('handles empty graphs', function () {
    $graph = new Graph();
    $pagerank = new PageRank();

    expect($pagerank->compute($graph))->toBe([]);
});

it('handles graphs with dangling nodes', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B');
    $graph->addNode('C'); // Dangling node

    $pagerank = new PageRank();
    $scores = $pagerank->compute($graph);

    expect($scores)->toHaveKeys(['A', 'B', 'C']);
});
