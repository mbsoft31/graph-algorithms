<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Centrality\PersonalizedPageRank;
use Mbsoft\Graph\Domain\Graph;

it('handles empty graph', function () {
    $graph = new Graph();
    $result = (new PersonalizedPageRank())->compute($graph);
    expect($result)->toBe([]);
});

it('behaves like standard PageRank when teleport is empty', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'A');

    $result = (new PersonalizedPageRank())->compute($graph);
    
    // In a symmetric cycle, all nodes should have equal rank 1/3
    foreach ($result as $rank) {
        expect($rank)->toEqualWithDelta(1/3, 0.001);
    }
});

it('biases towards seed nodes', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'A');
    $graph->addNode('D'); // Isolated node

    // Standard PageRank would distribute rank equally (1/4 each)
    // Personalized to 'A'
    $ppr = new PersonalizedPageRank(teleportVector: ['A' => 1.0]);
    $result = $ppr->compute($graph);

    expect($result['A'])->toBeGreaterThan($result['B']);
    expect($result['A'])->toBeGreaterThan($result['C']);
    expect($result['A'])->toBeGreaterThan($result['D']);
    
    // Node D can only be reached via teleport, so its rank should be 
    // exactly (1 - dampingFactor) * teleport_weight_for_D
    // Since teleport is 100% to A, and D is isolated:
    // newRanks[D] = (1 - 0.85) * 0 + 0.85 * (0 + danglingSum * 0) = 0
    // Wait, if D is isolated, it's a dangling node.
    // danglingSum = rank[D]
    // newRanks[D] = (1 - 0.85) * 0 + 0.85 * (0 + rank[D] * 0) = 0
    // So isolated nodes not in teleport set will have 0 rank.
    expect($result['D'])->toEqualWithDelta(0, 0.0001);
});

it('distributes rank to nodes reachable from seeds', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');
    $graph->addEdge('D', 'E'); // Separate component

    // Personalized to 'A'
    $ppr = new PersonalizedPageRank(teleportVector: ['A' => 1.0]);
    $result = $ppr->compute($graph);

    expect($result['A'])->toBeGreaterThan(0);
    expect($result['B'])->toBeGreaterThan(0);
    expect($result['C'])->toBeGreaterThan(0);
    
    // Component D-E should have 0 rank
    expect($result['D'])->toEqualWithDelta(0, 0.0001);
    expect($result['E'])->toEqualWithDelta(0, 0.0001);
});

it('handles teleport nodes not in graph gracefully', function () {
    $graph = new Graph(directed: true);
    $graph->addEdge('A', 'B');

    $ppr = new PersonalizedPageRank(teleportVector: ['A' => 1.0, 'Z' => 1.0]);
    $result = $ppr->compute($graph);

    expect($result)->toHaveKeys(['A', 'B']);
    expect($result['A'])->toBeGreaterThan($result['B']);
});

it('validates damping factor', function () {
    expect(fn() => new PersonalizedPageRank(dampingFactor: 1.1))->toThrow(InvalidArgumentException::class);
});
