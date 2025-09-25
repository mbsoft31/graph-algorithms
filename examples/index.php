<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Mbsoft\Graph\Algorithms\Centrality\PageRank;
use Mbsoft\Graph\Algorithms\Centrality\DegreeCentrality;
use Mbsoft\Graph\Algorithms\Pathfinding\Dijkstra;
use Mbsoft\Graph\Algorithms\Pathfinding\AStar;
use Mbsoft\Graph\Algorithms\Traversal\Bfs;
use Mbsoft\Graph\Algorithms\Traversal\Dfs;
use Mbsoft\Graph\Algorithms\Components\StronglyConnected;
use Mbsoft\Graph\Algorithms\Topological\TopologicalSort;
use Mbsoft\Graph\Algorithms\Mst\Prim;
use Mbsoft\Graph\Domain\Graph as DemoGraph;


echo "🚀 mbsoft/graph-algorithms - Comprehensive Demo\n";
echo "==============================================\n\n";

// Create a complex directed graph for demonstration
// Structure: A -> B -> C -> D
//           |    |    ^    ^
//           v    v    |    |
//           E -> F ---+    |
//           |           ^  |
//           +-> G ------+--+
$nodes = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
$edges = [
    (object)['from' => 'A', 'to' => 'B', 'attributes' => ['weight' => 2.0]],
    (object)['from' => 'A', 'to' => 'E', 'attributes' => ['weight' => 4.0]],
    (object)['from' => 'B', 'to' => 'C', 'attributes' => ['weight' => 3.0]],
    (object)['from' => 'B', 'to' => 'F', 'attributes' => ['weight' => 1.0]],
    (object)['from' => 'C', 'to' => 'D', 'attributes' => ['weight' => 1.0]],
    (object)['from' => 'E', 'to' => 'F', 'attributes' => ['weight' => 2.0]],
    (object)['from' => 'E', 'to' => 'G', 'attributes' => ['weight' => 3.0]],
    (object)['from' => 'F', 'to' => 'C', 'attributes' => ['weight' => 1.0]],
    (object)['from' => 'G', 'to' => 'F', 'attributes' => ['weight' => 1.0]],
    (object)['from' => 'G', 'to' => 'D', 'attributes' => ['weight' => 2.0]],
];

$graph = new DemoGraph(directed: true);

foreach ($nodes as $node) {
    $graph->addNode($node);
}

foreach ($edges as $edge) {
    $graph->addEdge($edge->from, $edge->to, $edge->attributes);
}

// 1. PageRank Centrality Analysis
echo "📊 PageRank Centrality Analysis\n";
echo "------------------------------\n";
$pagerank = new PageRank(
    dampingFactor: 0.85,
    maxIterations: 100,
    tolerance: 1e-6
);
$prScores = $pagerank->compute($graph);
arsort($prScores); // Sort by score descending

echo "Node rankings by PageRank importance:\n";
$rank = 1;
foreach ($prScores as $node => $score) {
    echo sprintf("%d. Node %s: %.4f\n", $rank++, $node, $score);
}
echo "\n";

// 2. Degree Centrality Comparison
echo "📊 Degree Centrality Analysis\n";
echo "----------------------------\n";
$inDegree = new DegreeCentrality(DegreeCentrality::IN_DEGREE);
$outDegree = new DegreeCentrality(DegreeCentrality::OUT_DEGREE);
$totalDegree = new DegreeCentrality(DegreeCentrality::TOTAL_DEGREE);

$inScores = $inDegree->compute($graph);
$outScores = $outDegree->compute($graph);
$totalScores = $totalDegree->compute($graph);

echo sprintf("%-4s | %-8s | %-8s | %-8s\n", "Node", "In", "Out", "Total");
echo "----------------------------------------\n";
foreach ($nodes as $node) {
    echo sprintf("%-4s | %-8.0f | %-8.0f | %-8.0f\n",
        $node, $inScores[$node], $outScores[$node], $totalScores[$node]);
}
echo "\n";

// 3. Shortest Path Analysis (Multiple algorithms)
echo "🛣️  Shortest Path Analysis\n";
echo "-------------------------\n";

// Dijkstra's algorithm
$dijkstra = new Dijkstra();
echo "Using Dijkstra's Algorithm:\n";
$testPaths = [['A', 'D'], ['A', 'G'], ['B', 'D'], ['E', 'C']];

foreach ($testPaths as [$start, $end]) {
    $path = $dijkstra->find($graph, $start, $end);
    if ($path) {
        echo sprintf("  %s -> %s: %s (cost: %.1f)\n",
            $start, $end, implode(' -> ', $path->nodes), $path->cost);
    } else {
        echo sprintf("  %s -> %s: No path found\n", $start, $end);
    }
}

// A* with Manhattan distance heuristic
echo "\nUsing A* Algorithm with heuristic:\n";
$astar = new AStar(
    heuristicCallback: function (string $from, string $to): float {
        // Simple coordinate-based heuristic (assuming nodes have positions)
        $coords = ['A' => [0, 0], 'B' => [1, 0], 'C' => [2, 0], 'D' => [3, 0],
            'E' => [0, 1], 'F' => [1, 1], 'G' => [0, 2]];
        if (!isset($coords[$from]) || !isset($coords[$to])) return 0.0;
        return abs($coords[$to][0] - $coords[$from][0]) +
            abs($coords[$to][1] - $coords[$from][1]);
    }
);

foreach ($testPaths as [$start, $end]) {
    $path = $astar->find($graph, $start, $end);
    if ($path) {
        echo sprintf("  %s -> %s: %s (cost: %.1f)\n",
            $start, $end, implode(' -> ', $path->nodes), $path->cost);
    } else {
        echo sprintf("  %s -> %s: No path found\n", $start, $end);
    }
}
echo "\n";

// 4. Graph Traversal Comparison
echo "🔍 Graph Traversal Analysis\n";
echo "--------------------------\n";
$bfs = new Bfs();
$dfs = new Dfs();

echo "Starting from node A:\n";
$bfsResult = $bfs->traverse($graph, 'A');
$dfsResult = $dfs->traverse($graph, 'A');

echo "BFS order: " . implode(' -> ', $bfsResult) . "\n";
echo "DFS order: " . implode(' -> ', $dfsResult) . "\n";
echo "\nTraversal from different starting points:\n";

foreach (['B', 'E', 'G'] as $startNode) {
    $bfsOrder = $bfs->traverse($graph, $startNode);
    echo sprintf("BFS from %s: %s\n", $startNode, implode(' -> ', $bfsOrder));
}
echo "\n";

// 5. Strongly Connected Components Analysis
echo "🔗 Strongly Connected Components\n";
echo "-------------------------------\n";
$scc = new StronglyConnected();
$components = $scc->findComponents($graph);

if (count($components) === 1) {
    echo "Graph is strongly connected - all nodes in one component:\n";
    echo "  [" . implode(', ', $components[0]) . "]\n";
} else {
    echo "Graph has " . count($components) . " strongly connected components:\n";
    foreach ($components as $i => $component) {
        echo sprintf("  Component %d: [%s]\n", $i + 1, implode(', ', $component));
    }
}
echo "\n";

// 6. Topological Sort Analysis
echo "📋 Topological Sort Analysis\n";
echo "----------------------------\n";
try {
    $topo = new TopologicalSort();
    $order = $topo->sort($graph);
    echo "✅ Topological order found: " . implode(' -> ', $order) . "\n";
    echo "This ordering respects all directed dependencies.\n";
} catch (\Mbsoft\Graph\Algorithms\Topological\CycleDetectedException $e) {
    echo "❌ Cannot create topological sort: Graph contains cycles!\n";
    echo "Cycles prevent a valid topological ordering.\n";
}
echo "\n";

// 7. Minimum Spanning Tree Analysis
echo "🌲 Minimum Spanning Tree Analysis\n";
echo "--------------------------------\n";

// Create undirected version for MST
$undirectedEdges = [
    (object)['from' => 'A', 'to' => 'B', 'attributes' => ['weight' => 2.0]],
    (object)['from' => 'A', 'to' => 'E', 'attributes' => ['weight' => 4.0]],
    (object)['from' => 'B', 'to' => 'C', 'attributes' => ['weight' => 3.0]],
    (object)['from' => 'B', 'to' => 'F', 'attributes' => ['weight' => 1.0]],
    (object)['from' => 'C', 'to' => 'D', 'attributes' => ['weight' => 1.0]],
    (object)['from' => 'E', 'to' => 'F', 'attributes' => ['weight' => 2.0]],
    (object)['from' => 'E', 'to' => 'G', 'attributes' => ['weight' => 3.0]],
    (object)['from' => 'F', 'to' => 'G', 'attributes' => ['weight' => 1.0]],
    (object)['from' => 'G', 'to' => 'D', 'attributes' => ['weight' => 2.0]],
];

$undirectedGraph = new DemoGraph(directed: false);

foreach ($nodes as $node) {
    $undirectedGraph->addNode($node);
}

foreach ($undirectedEdges as $edge) {
    $undirectedGraph->addEdge($edge->from, $edge->to, $edge->attributes);
}

$prim = new Prim();
$mst = $prim->findMst($undirectedGraph);

if ($mst) {
    echo sprintf("✅ MST found with total weight: %.1f\n", $mst->totalWeight);
    echo "MST edges:\n";
    foreach ($mst->edges as $edge) {
        echo sprintf("  %s -- %s (weight: %.1f)\n",
            $edge['from'], $edge['to'], $edge['weight']);
    }
    echo sprintf("\nEfficiency: MST uses %d edges to connect %d nodes\n",
        $mst->edgeCount(), count($nodes));
} else {
    echo "❌ Cannot create MST: Graph is not connected\n";
}
echo "\n";

// 8. Performance Comparison Demo
echo "⚡ Performance Characteristics\n";
echo "-----------------------------\n";
$startTime = microtime(true);

// Run multiple algorithms to show relative performance
for ($i = 0; $i < 100; $i++) {
    $pagerank->compute($graph);
}
$prTime = microtime(true);

for ($i = 0; $i < 100; $i++) {
    $dijkstra->find($graph, 'A', 'D');
}
$dijkstraTime = microtime(true);

for ($i = 0; $i < 100; $i++) {
    $bfs->traverse($graph, 'A');
}
$bfsTime = microtime(true);

echo "Relative performance (100 iterations each):\n";
echo sprintf("PageRank:    %.4f seconds\n", $prTime - $startTime);
echo sprintf("Dijkstra:    %.4f seconds\n", $dijkstraTime - $prTime);
echo sprintf("BFS:         %.4f seconds\n", $bfsTime - $dijkstraTime);
echo "\n";

// 9. Algorithm Recommendations
echo "💡 Algorithm Selection Guide\n";
echo "---------------------------\n";
echo "📊 Centrality Analysis:\n";
echo "   • PageRank: Best for ranking nodes by global importance\n";
echo "   • Degree: Fast local connectivity measure\n\n";

echo "🛣️  Pathfinding:\n";
echo "   • Dijkstra: Optimal for single-source shortest paths\n";
echo "   • A*: Best when you have good heuristics to guide search\n\n";

echo "🔍 Traversal:\n";
echo "   • BFS: Level-by-level exploration, shortest hop paths\n";
echo "   • DFS: Deep exploration, good for cycle detection\n\n";

echo "🔗 Structure Analysis:\n";
echo "   • SCC: Identifies strongly connected regions\n";
echo "   • Topological Sort: Finds valid dependency ordering\n\n";

echo "🌲 Optimization:\n";
echo "   • MST: Minimal cost to connect all nodes\n\n";

echo "🎉 Demo completed!\n";
echo "\n📚 Next Steps:\n";
echo "• Explore test fixtures in tests/Fixtures/GraphFixtures.php\n";
echo "• Run benchmarks with: composer bench\n";
echo "• Check static analysis with: composer stan\n";
echo "• Run full test suite with: composer test\n";