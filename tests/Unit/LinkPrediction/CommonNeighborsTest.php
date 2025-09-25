<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\LinkPrediction\CommonNeighbors;
use Mbsoft\Graph\Domain\Graph;

it('computes common neighbors score correctly', function () {
    $graph = new Graph();

    // A and B share 2 common neighbors (C, D)
    $graph->addEdge('A', 'C');
    $graph->addEdge('A', 'D');
    $graph->addEdge('B', 'C');
    $graph->addEdge('B', 'D');
    $graph->addEdge('A', 'E'); // A has unique neighbor

    $commonNeighbors = new CommonNeighbors();
    $score = $commonNeighbors->score($graph, 'A', 'B');

    expect($score)->toBe(2.0);
});

it('returns zero for nodes with no common neighbors', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'D');

    $commonNeighbors = new CommonNeighbors();
    $score = $commonNeighbors->score($graph, 'A', 'B');

    expect($score)->toBe(0.0);
});

it('returns zero for non-existent nodes', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B');

    $commonNeighbors = new CommonNeighbors();
    $score = $commonNeighbors->score($graph, 'A', 'NonExistent');

    expect($score)->toBe(0.0);
});

it('computes top-k scores correctly', function () {
    $graph = new Graph();

    // A connects to C, D, E
    $graph->addEdge('A', 'C');
    $graph->addEdge('A', 'D');
    $graph->addEdge('A', 'E');

    // B shares 2 common neighbors with A (C, D)
    $graph->addEdge('B', 'C');
    $graph->addEdge('B', 'D');

    // F shares 1 common neighbor with A (E)
    $graph->addEdge('F', 'E');

    // G has no common neighbors
    $graph->addNode('G');

    $commonNeighbors = new CommonNeighbors();
    $scores = $commonNeighbors->scoresFrom($graph, 'A', 5);

    expect($scores)->toHaveKey('B')
        ->and($scores)->toHaveKey('F')
        ->and($scores)->not->toHaveKey('G') // No common neighbors
        ->and($scores['B'])->toBe(2.0)
        ->and($scores['F'])->toBe(1.0);
});

it('excludes existing neighbors from predictions', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B'); // Already connected
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'C');

    $commonNeighbors = new CommonNeighbors();
    $scores = $commonNeighbors->scoresFrom($graph, 'A', 5);

    expect($scores)->not->toHaveKey('B') // Existing neighbor
    ->and($scores)->not->toHaveKey('C'); // Direct neighbor
});

it('handles single node graph', function () {
    $graph = new Graph();
    $graph->addNode('A');

    $commonNeighbors = new CommonNeighbors();
    $score = $commonNeighbors->score($graph, 'A', 'A');

    expect($score)->toBe(0.0);
});

it('works with directed graphs treating them as undirected', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'C');
    $graph->addEdge('C', 'B');
    $graph->addEdge('A', 'D');
    $graph->addEdge('D', 'B');

    $commonNeighbors = new CommonNeighbors();
    $score = $commonNeighbors->score($graph, 'A', 'B');

    // C and D are common neighbors when treating directed as undirected
    expect($score)->toBe(2.0);
});

it('counts both in and out neighbors for directed graphs', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'C'); // C is reachable from both A and B
    $graph->addEdge('C', 'A'); // C also reaches A
    $graph->addEdge('C', 'B'); // C also reaches B

    $commonNeighbors = new CommonNeighbors();
    $score = $commonNeighbors->score($graph, 'A', 'B');

    expect($score)->toBe(1.0); // C is common neighbor
});