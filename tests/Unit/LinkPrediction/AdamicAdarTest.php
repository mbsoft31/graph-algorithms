<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\LinkPrediction\AdamicAdar;
use Mbsoft\Graph\Domain\Graph;

it('computes adamic adar score for connected nodes', function () {
    $graph = new Graph();

    // Create a simple network: A-C-B, A-D-B (C and D are common neighbors)
    $graph->addEdge('A', 'C');
    $graph->addEdge('C', 'B');
    $graph->addEdge('A', 'D');
    $graph->addEdge('D', 'B');
    $graph->addEdge('C', 'E'); // C has degree 3 now
    $graph->addEdge('D', 'F'); // D has degree 3 now

    $adamicAdar = new AdamicAdar();
    $score = $adamicAdar->score($graph, 'A', 'B');

    // Score should be 1/log(3) + 1/log(3) = 2/log(3) ≈ 1.82
    expect($score)->toBeGreaterThan(1.8)
        ->and($score)->toBeLessThan(1.9);
});

it('returns zero score for nodes with no common neighbors', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'D');

    $adamicAdar = new AdamicAdar();
    $score = $adamicAdar->score($graph, 'A', 'B');

    expect($score)->toBe(0.0);
});

it('returns zero score for non-existent nodes', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B');

    $adamicAdar = new AdamicAdar();
    $score = $adamicAdar->score($graph, 'A', 'NonExistent');

    expect($score)->toBe(0.0);
});

it('handles nodes with degree 1 correctly', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'C');
    $graph->addEdge('C', 'B');
    // C has degree 2, so score should be 1/log(2)

    $adamicAdar = new AdamicAdar();
    $score = $adamicAdar->score($graph, 'A', 'B');

    expect($score)->toBeGreaterThan(1.4)
        ->and($score)->toBeLessThan(1.5);
});

it('computes top-k scores from a node', function () {
    $graph = new Graph();

    // A connects to common neighbors C, D
    $graph->addEdge('A', 'C');
    $graph->addEdge('A', 'D');

    // B connects to C, D (potential link prediction target)
    $graph->addEdge('B', 'C');
    $graph->addEdge('B', 'D');

    // E connects to only C (lower score)
    $graph->addEdge('E', 'C');

    // F is isolated (no common neighbors)
    $graph->addNode('F');

    $adamicAdar = new AdamicAdar();
    $scores = $adamicAdar->scoresFrom($graph, 'A', 5);

    expect($scores)->toHaveKey('B')
        ->and($scores)->toHaveKey('E')
        ->and($scores)->not->toHaveKey('F') // No common neighbors
        ->and($scores['B'])->toBeGreaterThan($scores['E']); // B has higher score
});

it('excludes existing neighbors from top-k predictions', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B'); // A and B are already connected
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'C'); // C is common neighbor

    $adamicAdar = new AdamicAdar();
    $scores = $adamicAdar->scoresFrom($graph, 'A', 5);

    expect($scores)->not->toHaveKey('B') // Existing neighbor excluded
    ->and($scores)->not->toHaveKey('C'); // Direct neighbor excluded
});

it('works with directed graphs', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'C');
    $graph->addEdge('C', 'B');
    $graph->addEdge('B', 'A'); // Creates a cycle

    $adamicAdar = new AdamicAdar();
    $score = $adamicAdar->score($graph, 'A', 'B');

    expect($score)->toBeGreaterThan(0.0);
});

it('handles empty graph', function () {
    $graph = new Graph();

    $adamicAdar = new AdamicAdar();
    $scores = $adamicAdar->scoresFrom($graph, 'A', 5);

    expect($scores)->toBeEmpty();
});