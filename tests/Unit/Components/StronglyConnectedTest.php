<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Components\StronglyConnected;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;

/**
 * Helper to sort components deterministically for comparison.
 */
function sortComponents(array $components): array {
    $sorted = [];
    foreach ($components as $comp) {
        sort($comp);
        $sorted[] = $comp;
    }
    usort($sorted, fn($a, $b) => $a[0] <=> $b[0]);
    return $sorted;
}

it('finds single SCC in strongly connected graph', function () {
    $fixture = GraphFixtures::simpleCycle();
    $result = (new StronglyConnected())->findComponents($fixture['graph']);

    $expected = $fixture['expected']['strongly_connected'];
    expect(sortComponents($result))->toBe(sortComponents($expected));
});

it('finds multiple SCCs in star graph', function () {
    $fixture = GraphFixtures::starGraph();
    $result = (new StronglyConnected())->findComponents($fixture['graph']);

    $expected = $fixture['expected']['strongly_connected'];
    expect(sortComponents($result))->toBe(sortComponents($expected));
});
