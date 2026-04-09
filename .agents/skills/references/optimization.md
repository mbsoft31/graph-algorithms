# Graph Performance & Optimization

Guidance on using `AlgorithmGraph` to optimize graph algorithms.

## Integer-Indexed Graphs
PHP array performance is significantly better with integer indices than string keys in tight loops.

### The Problem:
`GraphInterface` uses string IDs for flexibility. String lookups in a loop of millions of nodes are slow.

### The Solution: `AlgorithmGraph`
`AlgorithmGraph` maps all string IDs to a contiguous range of integers (`0` to `nodeCount - 1`).

```php
// 1. Initialize mapping
$algoGraph = new AlgorithmGraph($graph, needPredecessors: true);

// 2. Get optimized structures
$successors = $algoGraph->successors();     // array of int-indexed arrays
$predecessors = $algoGraph->predecessors(); // array of int-indexed arrays

// 3. Process with integers
for ($u = 0; $u < $algoGraph->nodeCount(); $u++) {
    foreach ($successors[$u] as $v) {
        // v is an integer ID
    }
}
```

## `IndexMap`
After processing, you must map the integer IDs back to the original string IDs to return the results.

```php
$result = [];
$ids = $algoGraph->ids(); // Returns the IndexMap
foreach ($scores as $nodeIdx => $score) {
    $result[$ids->id($nodeIdx)] = $score;
}
return $result;
```

## Space Complexity vs. Time Complexity
- **Caching**: Always cache the results of `count($successors[$u])` if used multiple times in a loop.
- **Typed Arrays**: While PHP doesn't have native typed arrays like C++, using `SplFixedArray` for rank storage can save memory on extremely large graphs (1M+ nodes).
- **Garbage Collection**: In long-running algorithm processes, use `gc_collect_cycles()` manually if memory pressure is high.
