# Centrality Algorithms

This guide provides implementation details for centrality metrics in `graph-algorithms`.

## PageRank
`Mbsoft\Graph\Algorithms\Centrality\PageRank` implements the classic algorithm with damping and convergence detection.

### Key Implementation Details:
- **Damping Factor ($d$)**: Usually 0.85. The probability that a surfer continues clicking.
- **Dangling Nodes**: Nodes with no outgoing edges. Their rank is distributed equally among all nodes.
- **Convergence**: Measured using the L1 norm (sum of absolute differences) between iterations.

### Mathematical Loop:
```php
$newRanks[$u] = (1.0 - $d) / $N + $d * ($rankSum + $danglingSum / $N);
```

## HITS (Hyperlink-Induced Topic Search)
`Mbsoft\Graph\Algorithms\Centrality\Hits` computes Authority and Hub scores.

- **Authorities**: Nodes pointed to by many good hubs.
- **Hubs**: Nodes that point to many good authorities.
- **Mutual Reinforcement**: Iteratively update Authority scores based on Hubs, then Hub scores based on Authorities.

## Betweenness Centrality
Implementation follows **Brandes' Algorithm**, which is $O(VE)$ for unweighted graphs.

### Process:
1. Perform BFS from each node to find shortest paths.
2. Calculate "dependency" of source nodes on intermediate nodes.
3. Accumulate scores.
- **Note**: Use `AlgorithmGraph` with `needPredecessors: true` and a `SplQueue` for the BFS phase.
