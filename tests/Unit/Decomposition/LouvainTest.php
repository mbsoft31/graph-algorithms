<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Decomposition\Louvain;
use Mbsoft\Graph\Domain\Graph;

it('handles empty graph', function () {
    $graph = new Graph();
    $result = (new Louvain())->compute($graph);
    expect($result)->toBe([]);
});

it('identifies single community for clique', function () {
    $graph = new Graph(directed: false);
    $nodes = ['A', 'B', 'C', 'D'];
    foreach ($nodes as $u) {
        foreach ($nodes as $v) {
            if ($u !== $v) $graph->addEdge($u, $v);
        }
    }

    $result = (new Louvain())->compute($graph);
    
    // All nodes should have the same community ID
    $communityId = $result['A'];
    foreach ($result as $id) {
        expect($id)->toBe($communityId);
    }
});

it('identifies separate communities for disconnected cliques', function () {
    $graph = new Graph(directed: false);
    
    // Clique 1: A, B, C
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'A');

    // Clique 2: X, Y, Z
    $graph->addEdge('X', 'Y');
    $graph->addEdge('Y', 'Z');
    $graph->addEdge('Z', 'X');

    // Bridge: A-X (very weak compared to intra-clique)
    $graph->addEdge('A', 'X');

    $result = (new Louvain())->compute($graph);
    
    expect($result['A'])->toBe($result['B'])->toBe($result['C']);
    expect($result['X'])->toBe($result['Y'])->toBe($result['Z']);
    expect($result['A'])->not->toBe($result['X']);
});
