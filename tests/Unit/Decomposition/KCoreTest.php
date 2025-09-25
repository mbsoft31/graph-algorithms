<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Decomposition\KCore;
use Mbsoft\Graph\Domain\Graph;

it('computes k-core decomposition for simple graph', function () {
    $graph = new Graph();

    // Create a graph with known core structure
    // Triangle: A-B-C-A (each has core 2)
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'A');

    // Add pendant edge: C-D (D has core 1)
    $graph->addEdge('C', 'D');

    $kcore = new KCore();
    $coreNumbers = $kcore->compute($graph);

    expect($coreNumbers['A'])->toBe(2)
        ->and($coreNumbers['B'])->toBe(2)
        ->and($coreNumbers['C'])->toBe(2)
        ->and($coreNumbers['D'])->toBe(1);
});

it('handles isolated nodes', function () {
    $graph = new Graph();
    $graph->addNode('A');

    $kcore = new KCore();
    $coreNumbers = $kcore->compute($graph);

    expect($coreNumbers['A'])->toBe(0);
});

it('computes core numbers for path graph', function () {
    $graph = new Graph();

    // Linear path: A-B-C-D
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'D');

    $kcore = new KCore();
    $coreNumbers = $kcore->compute($graph);

    // All nodes in a path have core number 1
    expect($coreNumbers['A'])->toBe(1)
        ->and($coreNumbers['B'])->toBe(1)
        ->and($coreNumbers['C'])->toBe(1)
        ->and($coreNumbers['D'])->toBe(1);
});

it('computes core numbers for complete graph', function () {
    $graph = new Graph();

    // Complete graph K4: every node connected to every other
    $nodes = ['A', 'B', 'C', 'D'];
    foreach ($nodes as $i => $node1) {
        foreach ($nodes as $j => $node2) {
            if ($i < $j) {
                $graph->addEdge($node1, $node2);
            }
        }
    }

    $kcore = new KCore();
    $coreNumbers = $kcore->compute($graph);

    // In K4, each node has degree 3, so core number is 3
    expect($coreNumbers['A'])->toBe(3)
        ->and($coreNumbers['B'])->toBe(3)
        ->and($coreNumbers['C'])->toBe(3)
        ->and($coreNumbers['D'])->toBe(3);
});

it('handles complex graph with multiple core levels', function () {
    $graph = new Graph();

    // Create a graph with multiple core levels
    // Core 3: triangle + extra connections
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'A');
    $graph->addEdge('A', 'D');
    $graph->addEdge('B', 'D');
    $graph->addEdge('C', 'D');

    // Core 1: pendant nodes
    $graph->addEdge('A', 'E');
    $graph->addEdge('B', 'F');

    $kcore = new KCore();
    $coreNumbers = $kcore->compute($graph);

    expect($coreNumbers['A'])->toBeGreaterThanOrEqual(3)
        ->and($coreNumbers['B'])->toBeGreaterThanOrEqual(3)
        ->and($coreNumbers['C'])->toBeGreaterThanOrEqual(3)
        ->and($coreNumbers['D'])->toBeGreaterThanOrEqual(3)
        ->and($coreNumbers['E'])->toBe(1)
        ->and($coreNumbers['F'])->toBe(1);
});

it('returns empty array for empty graph', function () {
    $graph = new Graph();

    $kcore = new KCore();
    $coreNumbers = $kcore->compute($graph);

    expect($coreNumbers)->toBeEmpty();
});

it('works with directed graphs by treating them as undirected', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'A');

    $kcore = new KCore();
    $coreNumbers = $kcore->compute($graph);

    // Should treat as undirected triangle
    expect($coreNumbers['A'])->toBe(2)
        ->and($coreNumbers['B'])->toBe(2)
        ->and($coreNumbers['C'])->toBe(2);
});

it('handles star graph correctly', function () {
    $graph = new Graph();

    // Star: center connected to 4 leaves
    $graph->addEdge('CENTER', 'A');
    $graph->addEdge('CENTER', 'B');
    $graph->addEdge('CENTER', 'C');
    $graph->addEdge('CENTER', 'D');

    $kcore = new KCore();
    $coreNumbers = $kcore->compute($graph);

    // All nodes in star have core number 1
    expect($coreNumbers['CENTER'])->toBe(1)
        ->and($coreNumbers['A'])->toBe(1)
        ->and($coreNumbers['B'])->toBe(1)
        ->and($coreNumbers['C'])->toBe(1)
        ->and($coreNumbers['D'])->toBe(1);
});