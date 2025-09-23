<?php

declare(strict_types=1);

use Mbsoft\Graph\Algorithms\Centrality\PageRank;
use Mbsoft\Graph\Algorithms\Tests\Fixtures\GraphFixtures;
use Mbsoft\Graph\Algorithms\Tests\Helpers\GraphBuilder;

it('handles empty graph', function () {
    $fixture = GraphFixtures::emptyGraph();
    $result = (new PageRank())->compute($fixture['graph']);
    expect($result)->toBe($fixture['expected']['pagerank']);
});

it('handles single node graph', function () {
    $fixture = GraphFixtures::singleNodeGraph();
    $result = (new PageRank())->compute($fixture['graph']);
    expect($result['A'])->toBeFloat()->toEqualWithDelta(1.0, 0.001);
});

it('computes symmetric cycle with equal ranks', function () {
    $fixture = GraphFixtures::simpleCycle();
    $result = (new PageRank())->compute($fixture['graph']);
    foreach ($fixture['expected']['pagerank'] as $node => $expected) {
        expect($result[$node])->toEqualWithDelta($expected, 0.001);
    }
});

it('respects damping factor', function () {
    $graph = GraphFixtures::simplePath()['graph'];

    $low = new PageRank(dampingFactor: 0.1);
    $high = new PageRank(dampingFactor: 0.9);

    $lowResult = $low->compute($graph);
    $highResult = $high->compute($graph);

    $variance = fn(array $v) => (function(array $vals) {
        $mean = array_sum($vals) / count($vals);
        $sum = 0.0; foreach ($vals as $x) { $sum += ($x - $mean) ** 2; }
        return $sum / count($vals);
    })($v);

    expect($variance($lowResult))->toBeLessThan($variance($highResult));
});

it('converges given tolerance', function () {
    $graph = GraphFixtures::completeTriangle()['graph'];

    $fast = new PageRank(tolerance: 0.1, maxIterations: 1000);
    $slow = new PageRank(tolerance: 1e-6, maxIterations: 1000);

    $fastResult = $fast->compute($graph);
    $slowResult = $slow->compute($graph);

    foreach ($fastResult as $node => $val) {
        expect($val)->toEqualWithDelta($slowResult[$node], 0.01);
    }
});

it('handles dangling nodes and sums to 1', function () {
    $graph = GraphBuilder::create()
        ->directed()
        ->addWeightedEdge('A', 'B', 1.0)
        ->addWeightedEdge('B', 'C', 1.0)
        ->build();

    $result = (new PageRank())->compute($graph);

    expect($result['A'])->toBeGreaterThan(0);
    expect($result['B'])->toBeGreaterThan(0);
    expect($result['C'])->toBeGreaterThan(0);

    expect(array_sum($result))->toEqualWithDelta(1.0, 0.001);
});

it('validates damping factor bounds', function () {
    expect(fn() => new PageRank(dampingFactor: 1.5))->toThrow(InvalidArgumentException::class);
    expect(fn() => new PageRank(dampingFactor: -0.1))->toThrow(InvalidArgumentException::class);
});
