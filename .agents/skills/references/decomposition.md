# Graph Decomposition & Community Detection

Details on algorithms for clustering and simplifying graphs.

## Louvain Method
The `Louvain` algorithm optimizes **Modularity ($Q$)** to find clusters.

### Algorithm Phases:
1.  **Local Optimization**: Move each node to a community of its neighbors to maximize $Q$.
2.  **Aggregation**: Shrink the graph by merging nodes in the same community into "super-nodes."
3.  **Iteration**: Repeat Phase 1 on the super-nodes until $Q$ can no longer be improved.

### Modularity Equation:
$$Q = \frac{1}{2m} \sum_{i,j} \left[ A_{ij} - \frac{k_i k_j}{2m} \right] \delta(c_i, c_j)$$
- $A_{ij}$: Edge weight between $i$ and $j$.
- $k_i, k_j$: Degrees of nodes.
- $m$: Total weight of all edges.
- $\delta$: Kronecker delta (1 if $c_i = c_j$, 0 otherwise).

## K-Core Decomposition
`KCore` finds nested subsets of nodes where each node has at least degree $k$ within the subset.

### Algorithm (Pruning):
1.  Initialize $k = 1$.
2.  Identify all nodes with degree $< k$.
3.  Remove these nodes and their incident edges.
4.  Re-calculate degrees of affected nodes.
5.  If nodes still have degree $< k$, repeat Step 2.
6.  Increment $k$ and repeat.

### Usage in Scholarly Graphs:
K-Core is excellent for identifying the **"central core"** of a citation network by pruning peripheral, low-citation papers.
