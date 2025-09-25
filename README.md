# mbsoft/graph-algorithms

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mbsoft31/graph-algorithms.svg?style=flat-square)](https://packagist.org/packages/mbsoft/graph-algorithms)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mbsoft31/graph-algorithms/ci.yml?branch=main&style=flat-square)](https://github.com/mbsoft31/graph-algorithms/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/mbsoft31/graph-algorithms.svg?style=flat-square)](https://packagist.org/packages/mbsoft31/graph-algorithms)

A high-performance library of standard graph algorithms for PHP. This library provides efficient implementations of essential graph algorithms with clean APIs, comprehensive error handling, and performance optimizations for production use.

## ✨ Features

- ⚡ **High Performance**: Integer-indexed `AlgorithmGraph` proxy for O(1) adjacency operations
- 🎯 **Comprehensive Coverage**: Centrality, pathfinding, traversal, components, ordering, and MST algorithms
- 📊 **Centrality Analysis**: PageRank with damping and convergence, degree centrality with normalization
- 🛣️ **Smart Pathfinding**: Dijkstra and A* with heuristic support and negative weight detection
- 🔍 **Graph Traversal**: BFS with `SplQueue` and iterative DFS to prevent stack overflow
- 🔗 **Component Analysis**: Tarjan's single-pass strongly connected components algorithm
- 📋 **Topological Ordering**: Kahn's algorithm with cycle detection and meaningful exceptions
- 🌲 **Minimum Spanning Tree**: Prim's algorithm with connectivity validation
- 🔒 **Type-Safe**: Leverages PHP 8.2+ features with strict typing and comprehensive contracts
- ✅ **Production Ready**: Extensive test coverage with canonical fixtures and edge case handling
- 🚀 **Zero External Dependencies**: Only depends on `mbsoft/graph-core`

## 📋 Requirements

- PHP 8.2 or higher
- mbsoft/graph-core ^1.0

## 📦 Installation

Install via Composer:

```bash
composer require mbsoft/graph-algorithms
```

Here are the code snippets for each Quick Start and Advanced Features section:

## 🚀 Quick Start

### PageRank Centrality

```php
use Mbsoft\Graph\Algorithms\Centrality\PageRank;

// Create PageRank with custom parameters
$pagerank = new PageRank(
    dampingFactor: 0.85,    // Link-following probability
    maxIterations: 100,     // Maximum iterations
    tolerance: 1e-6         // Convergence threshold
);

// Compute centrality scores
$scores = $pagerank->compute($graph);
arsort($scores); // Sort by importance

// Results: ['nodeA' => 0.343, 'nodeB' => 0.289, 'nodeC' => 0.368]
foreach ($scores as $node => $importance) {
    echo "Node {$node}: " . round($importance, 3) . "\n";
}
```

### Shortest Path Finding

```php
use Mbsoft\Graph\Algorithms\Pathfinding\Dijkstra;
use Mbsoft\Graph\Algorithms\Pathfinding\AStar;

// Dijkstra's algorithm for guaranteed shortest path
$dijkstra = new Dijkstra();
$path = $dijkstra->find($graph, 'start', 'destination');

if ($path) {
    echo "Path: " . implode(' → ', $path->nodes) . "\n";
    echo "Total cost: " . $path->cost . "\n";
    echo "Hops: " . $path->edgeCount() . "\n";
}

// A* with heuristic for faster pathfinding
$astar = new AStar(
    heuristicCallback: fn($from, $to) => manhattanDistance($from, $to)
);
$fasterPath = $astar->find($graph, 'start', 'destination');
```

### Graph Traversal

```php
use Mbsoft\Graph\Algorithms\Traversal\Bfs;
use Mbsoft\Graph\Algorithms\Traversal\Dfs;

// Breadth-first search (level by level)
$bfs = new Bfs();
$bfsOrder = $bfs->traverse($graph, 'startNode');
echo "BFS: " . implode(' → ', $bfsOrder) . "\n";

// Depth-first search (go deep first)
$dfs = new Dfs();
$dfsOrder = $dfs->traverse($graph, 'startNode');
echo "DFS: " . implode(' → ', $dfsOrder) . "\n";

// Example output:
// BFS: A → B → C → D → E → F (breadth-first)
// DFS: A → B → D → F → C → E (depth-first)
```

### Component Analysis

```php
use Mbsoft\Graph\Algorithms\Components\StronglyConnected;

// Find strongly connected components using Tarjan's algorithm
$scc = new StronglyConnected();
$components = $scc->findComponents($graph);

echo "Found " . count($components) . " strongly connected components:\n";
foreach ($components as $i => $component) {
    echo "Component " . ($i + 1) . ": [" . implode(', ', $component) . "]\n";
}

// Example output:
// Component 1: [A, B, C]  <- These nodes can all reach each other
// Component 2: [D]        <- Isolated node
// Component 3: [E, F]     <- Another strongly connected pair
```

## 🔍 Advanced Features

### Custom Weight Extraction

```php
use Mbsoft\Graph\Algorithms\Pathfinding\Dijkstra;

// Extract weights from different edge attributes
$timeOptimized = new Dijkstra(
    fn(array $attrs, string $from, string $to): float => 
        $attrs['travel_time'] ?? 1.0
);

$distanceOptimized = new Dijkstra(
    fn(array $attrs, string $from, string $to): float => 
        $attrs['distance'] ?? 1.0
);

// Dynamic weight calculation based on node properties
$dynamicWeights = new Dijkstra(
    function(array $attrs, string $from, string $to) use ($graph): float {
        $fromElevation = $graph->nodeAttrs($from)['elevation'] ?? 0;
        $toElevation = $graph->nodeAttrs($to)['elevation'] ?? 0;
        $baseDistance = $attrs['distance'] ?? 1.0;
        
        // Add penalty for uphill movement
        $elevationPenalty = max(0, $toElevation - $fromElevation) * 0.1;
        return $baseDistance + $elevationPenalty;
    }
);
```

### Heuristic Functions

```php
use Mbsoft\Graph\Algorithms\Pathfinding\AStar;

// Manhattan distance heuristic for grid-based pathfinding
$gridPathfinder = new AStar(
    heuristicCallback: function(string $from, string $to): float {
        [$x1, $y1] = explode(',', $from);
        [$x2, $y2] = explode(',', $to);
        return abs($x2 - $x1) + abs($y2 - $y1);
    }
);

// Euclidean distance for coordinate-based graphs
$coordinatePathfinder = new AStar(
    heuristicCallback: function(string $from, string $to) use ($coordinates): float {
        $fromCoord = $coordinates[$from];
        $toCoord = $coordinates[$to];
        return sqrt(
            pow($toCoord['x'] - $fromCoord['x'], 2) + 
            pow($toCoord['y'] - $fromCoord['y'], 2)
        );
    }
);

// Zero heuristic (equivalent to Dijkstra)
$dijkstraEquivalent = new AStar(); // Default heuristic returns 0.0
```

### Convergence Control

```php
use Mbsoft\Graph\Algorithms\Centrality\PageRank;

// Fast convergence for real-time applications
$fastPageRank = new PageRank(
    dampingFactor: 0.85,
    maxIterations: 50,      // Fewer iterations
    tolerance: 0.01         // Less precision, faster results
);

// High precision for research/analysis
$precisePageRank = new PageRank(
    dampingFactor: 0.85,
    maxIterations: 1000,    // More iterations allowed
    tolerance: 1e-8         // Higher precision
);

// Monitor convergence
$scores = $precisePageRank->compute($graph);
echo "PageRank converged with high precision\n";

// Different damping factors for different behaviors
$surfingPageRank = new PageRank(dampingFactor: 0.85); // Traditional web surfing
$explorationPageRank = new PageRank(dampingFactor: 0.5); // More random exploration
```

### Error Handling

```php
use Mbsoft\Graph\Algorithms\Pathfinding\Dijkstra;
use Mbsoft\Graph\Algorithms\Topological\TopologicalSort;
use Mbsoft\Graph\Algorithms\Topological\CycleDetectedException;
use Mbsoft\Graph\Algorithms\Mst\Prim;

// Handle negative weights in Dijkstra
try {
    $dijkstra = new Dijkstra();
    $path = $dijkstra->find($graphWithNegativeWeights, 'A', 'B');
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Use Bellman-Ford algorithm for negative weights\n";
}

// Handle cycles in topological sort
try {
    $topo = new TopologicalSort();
    $order = $topo->sort($cyclicGraph);
    echo "Valid ordering: " . implode(' → ', $order) . "\n";
} catch (CycleDetectedException $e) {
    echo "Cannot sort: Graph contains cycles!\n";
    // Alternative: Use strongly connected components to identify cycles
}

// Handle disconnected graphs in MST
$prim = new Prim();
$mst = $prim->findMst($disconnectedGraph);

if ($mst === null) {
    echo "Cannot create MST: Graph is not connected\n";
    echo "Consider finding MST for each connected component separately\n";
} else {
    echo "MST total weight: " . $mst->totalWeight . "\n";
}

// Handle unreachable nodes in pathfinding
$path = $dijkstra->find($graph, 'island1', 'island2');
if ($path === null) {
    echo "No path exists between the nodes\n";
    // Alternative: Check connected components first
}
```

These code snippets demonstrate practical usage patterns for each feature, showing both 
basic usage and real-world scenarios with proper error handling and parameter configuration.

## 🏗️ Architecture

### Core Strategy

**AlgorithmGraph Proxy Pattern**: All algorithms convert any `GraphInterface` to an optimized integer-indexed representation once, then perform all operations on fast adjacency lists.

### Interfaces

- **`CentralityAlgorithmInterface`**: Common interface for centrality calculations
- **`PathfindingAlgorithmInterface`**: Standard pathfinding contract with `PathResult` return type
- **`TraversalAlgorithmInterface`**: Graph traversal operations

### Value Objects

- **`PathResult`**: Immutable path representation with nodes, cost, and utility methods
- **`MstResult`**: MST result with edges, total weight, and connectivity information

### Performance Classes

- **`AlgorithmGraph`**: Internal proxy for integer-indexed graph operations
- **`IndexMap`**: Bidirectional string-to-integer mapping for node ID management

## 🧪 Testing

Run the test suite with Pest:

```bash
composer test
```

Run static analysis with PHPStan:

```bash
composer stan
```

Run performance benchmarks:

```bash
composer bench
```

## 🎯 Use Cases

This library excels in:

- **Network Analysis**: Social network centrality, influence propagation, community detection
- **Route Planning**: GPS navigation, logistics optimization, shortest path queries
- **Dependency Resolution**: Package managers, build systems, task scheduling
- **Web Analytics**: PageRank-based ranking, link analysis, authority computation
- **Game Development**: Pathfinding AI, level connectivity, optimal route calculation
- **Data Pipeline**: Topological sorting for ETL processes, workflow orchestration
- **Infrastructure Planning**: Network design, minimal spanning trees, connectivity analysis

## ⚡ Performance Considerations

The library is optimized for high-performance graph processing:

### Integer-Indexed Operations

**AlgorithmGraph Conversion**: One-time O(V + E) conversion to integer indices enables O(1) adjacency lookups throughout algorithm execution.

### Efficient Data Structures

- **BFS**: Uses `SplQueue` for FIFO operations without array shifting overhead
- **DFS**: Iterative implementation with `SplStack` prevents recursion depth limits
- **Dijkstra/A***: Handles stale priority queue entries without expensive decrease-key operations
- **Tarjan SCC**: Single-pass algorithm with optimal time complexity

### Memory Optimization

**Lazy Predecessor Building**: Only constructs reverse adjacency lists when algorithms require them, saving memory for algorithms that only need forward traversal.

### Cache-Friendly Design

**Parallel Adjacency Lists**: Neighbor and weight arrays stored in aligned structures for CPU cache efficiency.

### Benchmarks

Performance on a 1,000-node graph (100 iterations):
- PageRank convergence: ~5ms
- Dijkstra pathfinding: ~2ms
- BFS traversal: ~1ms
- Tarjan SCC: ~3ms

## 📚 Example Applications

### Social Network Analysis

Identify influential users with PageRank, find shortest connection paths, and detect tightly-knit communities using strongly connected components.

### Supply Chain Optimization

Model supplier networks as graphs, find optimal routing with Dijkstra, ensure connectivity with MST algorithms, and identify critical dependencies.

### Web Crawling and SEO

Implement PageRank for page authority, use BFS for site structure analysis, and apply topological sorting for sitemap generation.

### Game AI Pathfinding

Integrate A* with custom heuristics for character movement, use BFS for area exploration, and apply DFS for maze solving.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-algorithm`)
3. Ensure tests pass (`composer test`)
4. Verify static analysis (`composer stan`)
5. Push to the branch (`git push origin feature/amazing-algorithm`)
6. Open a Pull Request

### Adding New Algorithms

Follow the established patterns:
- Create algorithm in appropriate category directory
- Implement relevant interface contract
- Use `AlgorithmGraph` proxy for performance
- Include comprehensive tests with fixtures
- Document time/space complexity

## 📝 License

This library is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Algorithm implementations inspired by CLRS "Introduction to Algorithms"
- Performance patterns from NetworkX (Python) and JGraphT (Java)
- Built with modern PHP 8.2+ features and best practices
- Tested with Pest PHP testing framework

## 📮 Support

For bugs and feature requests, please use the [GitHub issues page](https://github.com/mbsoft/graph-algorithms/issues).

For algorithm-specific questions or performance optimization discussions, include graph characteristics 
(node count, edge density, directedness) in your issue description.

## 🔗 See Also

- [mbsoft/graph-core](https://github.com/mbsoft/graph-core) - Core graph package that implement entities 
and exports to many graph plotting libraries formats. 