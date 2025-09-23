<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Tests\Fixtures;

use Mbsoft\Graph\Algorithms\Tests\Helpers\GraphBuilder;

final class GraphFixtures
{
    /**
     * Simple 3-node path: A -> B -> C
     * @return array{graph: object, description: string, expected: array}
     */
    public static function simplePath(): array
    {
        $graph = GraphBuilder::create()
            ->directed()
            ->path(['A', 'B', 'C'], [1.0, 2.0])
            ->build();

        return [
            'graph' => $graph,
            'description' => 'Simple path A->B->C with weights 1,2',
            'expected' => [
                'dijkstra_A_to_C' => ['nodes' => ['A', 'B', 'C'], 'cost' => 3.0],
                'bfs_from_A' => ['A', 'B', 'C'],
                'dfs_from_A' => ['A', 'B', 'C'],
                'topological_sort' => ['A', 'B', 'C'],
                'degree_centrality' => [
                    'in' => ['A' => 0.0, 'B' => 1.0, 'C' => 1.0],
                    'out' => ['A' => 1.0, 'B' => 1.0, 'C' => 0.0],
                    'total' => ['A' => 1.0, 'B' => 2.0, 'C' => 1.0]
                ]
            ]
        ];
    }

    /**
     * Simple 3-node cycle: A -> B -> C -> A
     */
    public static function simpleCycle(): array
    {
        $graph = GraphBuilder::create()
            ->directed()
            ->cycle(['A', 'B', 'C'], [1.0, 1.0, 1.0])
            ->build();

        return [
            'graph' => $graph,
            'description' => 'Simple cycle A->B->C->A',
            'expected' => [
                'dijkstra_A_to_C' => ['nodes' => ['A', 'B', 'C'], 'cost' => 2.0],
                'bfs_from_A' => ['A', 'B', 'C'],
                'strongly_connected' => [['A', 'B', 'C']],
                'pagerank' => ['A' => 1/3, 'B' => 1/3, 'C' => 1/3],
                'degree_centrality' => [
                    'in' => ['A' => 1.0, 'B' => 1.0, 'C' => 1.0],
                    'out' => ['A' => 1.0, 'B' => 1.0, 'C' => 1.0],
                    'total' => ['A' => 2.0, 'B' => 2.0, 'C' => 2.0]
                ],
                'topological_sort' => 'throws_cycle_exception'
            ]
        ];
    }

    /** Complete 3-node graph (triangle). */
    public static function completeTriangle(): array
    {
        $graph = GraphBuilder::create()->directed()->complete(['A', 'B', 'C'], 1.0)->build();
        return [
            'graph' => $graph,
            'description' => 'Complete directed triangle',
            'expected' => [
                'dijkstra_A_to_C' => ['nodes' => ['A', 'C'], 'cost' => 1.0],
                'strongly_connected' => [['A', 'B', 'C']],
                'pagerank' => ['A' => 1/3, 'B' => 1/3, 'C' => 1/3],
                'degree_centrality' => [
                    'in' => ['A' => 2.0, 'B' => 2.0, 'C' => 2.0],
                    'out' => ['A' => 2.0, 'B' => 2.0, 'C' => 2.0],
                    'total' => ['A' => 4.0, 'B' => 4.0, 'C' => 4.0]
                ]
            ]
        ];
    }

    /** Star graph with center A and leaves B,C,D. */
    public static function starGraph(): array
    {
        $graph = GraphBuilder::create()->directed()->star('A', ['B', 'C', 'D'], 1.0)->build();
        return [
            'graph' => $graph,
            'description' => 'Star graph with A at center',
            'expected' => [
                'dijkstra_A_to_C' => ['nodes' => ['A', 'C'], 'cost' => 1.0],
                'dijkstra_B_to_C' => null,
                'bfs_from_A' => ['A', 'B', 'C', 'D'],
                'topological_sort' => ['A', 'B', 'C', 'D'],
                'strongly_connected' => [['A'], ['B'], ['C'], ['D']],
                'degree_centrality' => [
                    'in' => ['A' => 0.0, 'B' => 1.0, 'C' => 1.0, 'D' => 1.0],
                    'out' => ['A' => 3.0, 'B' => 0.0, 'C' => 0.0, 'D' => 0.0],
                    'total' => ['A' => 3.0, 'B' => 1.0, 'C' => 1.0, 'D' => 1.0]
                ]
            ]
        ];
    }

    /** Disconnected graph: A-B and C-D (undirected). */
    public static function disconnectedGraph(): array
    {
        $graph = GraphBuilder::create()
            ->undirected()
            ->addWeightedEdge('A', 'B', 1.0)
            ->addWeightedEdge('C', 'D', 1.0)
            ->build();
        return [
            'graph' => $graph,
            'description' => 'Disconnected: A-B and C-D',
            'expected' => [
                'dijkstra_A_to_C' => null,
                'bfs_from_A' => ['A', 'B'],
                'mst_prim' => null,
                'degree_centrality' => [
                    'total' => ['A' => 1.0, 'B' => 1.0, 'C' => 1.0, 'D' => 1.0]
                ]
            ]
        ];
    }

    /** Weighted square for MST testing (undirected). */
    public static function weightedSquare(): array
    {
        $graph = GraphBuilder::create()
            ->undirected()
            ->addWeightedEdge('A', 'B', 1.0)
            ->addWeightedEdge('B', 'C', 3.0)
            ->addWeightedEdge('C', 'D', 2.0)
            ->addWeightedEdge('D', 'A', 4.0)
            ->build();
        return [
            'graph' => $graph,
            'description' => 'Weighted square A-B-C-D-A with weights 1,3,2,4',
            'expected' => [
                'mst_prim' => [
                    'edges' => [
                        ['from' => 'A', 'to' => 'B', 'weight' => 1.0],
                        ['from' => 'C', 'to' => 'D', 'weight' => 2.0],
                        ['from' => 'B', 'to' => 'C', 'weight' => 3.0],
                    ],
                    'total_weight' => 6.0
                ],
                'dijkstra_A_to_C' => ['nodes' => ['A', 'B', 'C'], 'cost' => 4.0]
            ]
        ];
    }

    /** Empty graph. */
    public static function emptyGraph(): array
    {
        $graph = GraphBuilder::create()->directed()->build();
        return [
            'graph' => $graph,
            'description' => 'Empty graph',
            'expected' => [
                'pagerank' => [],
                'degree_centrality' => ['total' => []],
                'topological_sort' => [],
                'strongly_connected' => []
            ]
        ];
    }

    /** Single node graph. */
    public static function singleNodeGraph(): array
    {
        $graph = GraphBuilder::create()->directed()->addNode('A')->build();
        return [
            'graph' => $graph,
            'description' => 'Single node A',
            'expected' => [
                'pagerank' => ['A' => 1.0],
                'bfs_from_A' => ['A'],
                'dfs_from_A' => ['A'],
                'topological_sort' => ['A'],
                'strongly_connected' => [['A']],
                'degree_centrality' => [
                    'in' => ['A' => 0.0],
                    'out' => ['A' => 0.0],
                    'total' => ['A' => 0.0]
                ]
            ]
        ];
    }

    /** @return array<string, array> */
    public static function all(): array
    {
        return [
            'simple_path' => self::simplePath(),
            'simple_cycle' => self::simpleCycle(),
            'complete_triangle' => self::completeTriangle(),
            'star_graph' => self::starGraph(),
            'disconnected_graph' => self::disconnectedGraph(),
            'weighted_square' => self::weightedSquare(),
            'empty_graph' => self::emptyGraph(),
            'single_node' => self::singleNodeGraph(),
        ];
    }
}
