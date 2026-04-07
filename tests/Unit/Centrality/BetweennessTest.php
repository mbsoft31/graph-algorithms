<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Centrality\Betweenness;
use Mbsoft\Graph\Domain\Graph;

it('handles empty graph', function () {
    $graph = new Graph();
    $result = (new Betweenness())->compute($graph);
    expect($result)->toBe([]);
});

it('handles single node graph', function () {
    $graph = new Graph();
    $graph->addNode('A');
    $result = (new Betweenness())->compute($graph);
    expect($result['A'])->toBe(0.0);
});

it('identifies bridge in path graph', function () {
    // A -> B -> C
    // B is the only node on a path between two other nodes
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');

    $result = (new Betweenness(normalized: false))->compute($graph);
    
    expect($result['B'])->toBe(1.0);
    expect($result['A'])->toBe(0.0);
    expect($result['C'])->toBe(0.0);
});

it('calculates star graph centrality', function () {
    // A -> C, B -> C, D -> C, C -> E
    // C is the hub
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'C');
    $graph->addEdge('D', 'C');
    $graph->addEdge('C', 'E');

    $result = (new Betweenness(normalized: false))->compute($graph);

    // Paths: A-C-E, B-C-E, D-C-E
    // C is on 3 paths.
    expect($result['C'])->toBe(3.0);
    expect($result['A'])->toBe(0.0);
    expect($result['E'])->toBe(0.0);
});

it('normalizes correctly', function () {
    // A -> B -> C
    // N=3. Normalization factor = 1 / (2 * 1) = 0.5
    // Unnormalized B = 1.0. Normalized B = 0.5
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');

    $result = (new Betweenness(normalized: true))->compute($graph);
    
    expect($result['B'])->toBe(0.5);
});
