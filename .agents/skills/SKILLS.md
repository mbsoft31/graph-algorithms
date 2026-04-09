---
name: graph-algorithms
description: Procedural guidance for implementing, optimizing, and extending graph algorithms (Centrality, Decomposition, Pathfinding) in the mbsoft31/graph-algorithms package.
---
# Graph Algorithms Agent Skills (Super-Skill)

This package (`mbsoft31/graph-algorithms`) provides high-performance graph algorithms.

## 📚 Advanced References
- **[Centrality Algorithms](references/centrality.md)**: Details on PageRank, HITS, and Brandes' Betweenness algorithm.
- **[Community Detection](references/decomposition.md)**: Modularity optimization (Louvain) and K-Core pruning.
- **[Optimization Guide](references/optimization.md)**: High-performance PHP graph processing using `AlgorithmGraph` and `IndexMap`.

## Skill: Add a Centrality Algorithm
**Trigger:** User wants to implement a new centrality metric (e.g., Eigenvector, Closeness).

**Steps:**
1. Create `src/Centrality/{Name}.php` implementing `Mbsoft\Graph\Algorithms\Contracts\CentralityAlgorithmInterface`.
2. Use `Mbsoft\Graph\Algorithms\Support\AlgorithmGraph` to handle internal integer mapping.
3. Implement `compute(GraphInterface $graph): array`.
4. Ensure the result is an associative array `[nodeId => score]`.
5. **Advanced Details**: See [references/centrality.md](references/centrality.md).

## Skill: Add a Decomposition/Community Detection Algorithm
**Trigger:** User wants a new way to cluster nodes (e.g., Girvan-Newman, Infomap).

**Steps:**
1. Create `src/Decomposition/{Name}.php`.
2. Use `AlgorithmGraph` with `needEdgeWeights: true` if required.
3. Implement `compute(GraphInterface $graph): array`.
4. Return a mapping of `[nodeId => communityId]`.
5. **Advanced Details**: See [references/decomposition.md](references/decomposition.md).

## Skill: Optimize Algorithm with AlgorithmGraph
**Trigger:** Existing algorithm is slow on large graphs.

**Guidelines:**
- Use `AlgorithmGraph` to get `successors()` and `predecessors()` as integer-indexed arrays.
- Avoid string lookups in tight loops.
- Use the `IndexMap` provided by `AlgorithmGraph` to map back to IDs at the very end.
- **Advanced Details**: See [references/optimization.md](references/optimization.md).
