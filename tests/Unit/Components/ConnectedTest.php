<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Components\Connected;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;
use Mbsoft\Graph\Algorithms\Tests\Helpers\GraphBuilder;

it('finds connected components in an undirected graph', function () {
    $fixture = GraphFixtures::disconnectedGraph();
    $components = (new Connected())->findComponents($fixture['graph']);

    expect(normalizeConnectedComponents($components))->toBe([
        ['A', 'B'],
        ['C', 'D'],
    ]);
});

it('finds weak components in a directed graph', function () {
    $fixture = GraphFixtures::starGraph();
    $components = (new Connected())->findComponents($fixture['graph']);

    expect(normalizeConnectedComponents($components))->toBe([
        ['A', 'B', 'C', 'D'],
    ]);
});

it('keeps disconnected directed weak components separate', function () {
    $graph = GraphBuilder::create()
        ->directed()
        ->addEdge('A', 'B')
        ->addEdge('C', 'D')
        ->addNode('E')
        ->build();

    $components = (new Connected())->findComponents($graph);

    expect(normalizeConnectedComponents($components))->toBe([
        ['A', 'B'],
        ['C', 'D'],
        ['E'],
    ]);
});

it('returns an empty list for an empty graph', function () {
    $fixture = GraphFixtures::emptyGraph();

    expect((new Connected())->findComponents($fixture['graph']))->toBe([]);
});

/**
 * @param list<list<string>> $components
 * @return list<list<string>>
 */
function normalizeConnectedComponents(array $components): array
{
    $normalized = [];

    foreach ($components as $component) {
        sort($component);
        $normalized[] = $component;
    }

    usort($normalized, fn (array $a, array $b): int => $a[0] <=> $b[0]);

    return $normalized;
}
