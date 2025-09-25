<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\LinkPrediction\ResourceAllocation;
use Mbsoft\Graph\Domain\Graph;

it('computes resource allocation score correctly', function () {
    $graph = new Graph();

    // A and B share common neighbors C (degree 2) and D (degree 2)
    $graph->addEdge('A', 'C');
    $graph->addEdge('A', 'D');
    $graph->addEdge('B', 'C');
    $graph->addEdge('B', 'D');
    // Don't add extra edges to keep degrees predictable

    $resourceAllocation = new ResourceAllocation();
    $score = $resourceAllocation->score($graph, 'A', 'B');

    // Score should be 1/2 + 1/2 = 1.0 (both C and D have degree 2)
    expect($score)->toBe(1.0);
});

it('returns zero for nodes with no common neighbors', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'D');

    $resourceAllocation = new ResourceAllocation();
    $score = $resourceAllocation->score($graph, 'A', 'B');

    expect($score)->toBe(0.0);
});

it('handles isolated common neighbors', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'C');
    // C has degree 2

    $resourceAllocation = new ResourceAllocation();
    $score = $resourceAllocation->score($graph, 'A', 'B');

    expect($score)->toBe(0.5); // 1/2
});

it('computes top-k scores correctly', function () {
    $graph = new Graph();

    // Setup network where B has higher RA score than F
    $graph->addEdge('A', 'C');
    $graph->addEdge('A', 'D');

    // B shares both C and D (high score)
    $graph->addEdge('B', 'C');
    $graph->addEdge('B', 'D');

    // F shares only C (lower score)
    $graph->addEdge('F', 'C');

    // Add more connections to vary degrees
    $graph->addEdge('C', 'X');
    $graph->addEdge('D', 'Y');

    $resourceAllocation = new ResourceAllocation();
    $scores = $resourceAllocation->scoresFrom($graph, 'A', 5);

    expect($scores)->toHaveKey('B')
        ->and($scores)->toHaveKey('F')
        ->and($scores['B'])->toBeGreaterThan($scores['F']);
});

it('excludes existing neighbors from predictions', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B'); // Already connected
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'C');

    $resourceAllocation = new ResourceAllocation();
    $scores = $resourceAllocation->scoresFrom($graph, 'A', 5);

    expect($scores)->not->toHaveKey('B')
        ->and($scores)->not->toHaveKey('C');
});

it('handles nodes with degree 1', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'C');
    // C has degree 2

    $resourceAllocation = new ResourceAllocation();
    $score = $resourceAllocation->score($graph, 'A', 'B');

    expect($score)->toBe(0.5);
});

it('returns zero for non-existent nodes', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B');

    $resourceAllocation = new ResourceAllocation();
    $score = $resourceAllocation->score($graph, 'A', 'NonExistent');

    expect($score)->toBe(0.0);
});

it('works with directed graphs', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'D');
    // C has total degree 3 (2 in, 1 out)

    $resourceAllocation = new ResourceAllocation();
    $score = $resourceAllocation->score($graph, 'A', 'B');

    expect($score)->toBeGreaterThan(0.0);
});