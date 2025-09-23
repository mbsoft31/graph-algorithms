<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Traversal\Dfs;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;

it('traverses depth-first from start node', function () {
    $fixture = GraphFixtures::simplePath();

    $order = (new Dfs())->traverse($fixture['graph'], 'A');

    expect($order)->toBe($fixture['expected']['dfs_from_A']);
});
