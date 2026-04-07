<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Decomposition;

use Mbsoft\Graph\Algorithms\Support\AlgorithmGraph;
use Mbsoft\Graph\Contracts\GraphInterface;

/**
 * Louvain community detection algorithm.
 * Groups nodes into thematic clusters by maximizing modularity.
 */
final readonly class Louvain
{
    public function __construct(
        private float $resolution = 1.0,
        private int   $maxIterations = 10
    ) {}

    /**
     * @return array<string, int> Mapping of nodeId to communityId
     */
    public function compute(GraphInterface $graph): array
    {
        $ag = new AlgorithmGraph($graph, needEdgeWeights: true);
        $N = $ag->nodeCount();
        if ($N === 0) return [];

        $ids = $ag->ids();
        $adj = $this->buildSymmetricAdjacency($ag);
        
        // Initial state: each node in its own community
        $nodeToComm = range(0, $N - 1);
        $communities = [];
        for ($i = 0; $i < $N; $i++) {
            $communities[$i] = [
                'nodes' => [$i],
                'in' => $adj[$i][$i] ?? 0.0, // weight of self-loops
                'tot' => array_sum($adj[$i])
            ];
        }

        $m2 = 0.0;
        foreach ($communities as $comm) {
            $m2 += $comm['tot'];
        }

        if ($m2 === 0.0) {
            $result = [];
            for ($i = 0; $i < $N; $i++) $result[$ids->id($i)] = $i;
            return $result;
        }

        $improved = true;
        $iter = 0;

        while ($improved && $iter < $this->maxIterations) {
            $improved = false;
            $iter++;
            
            // Phase 1: Local moving
            $nodes = range(0, $N - 1);
            shuffle($nodes); // Stochastic improvement

            foreach ($nodes as $u) {
                $uCommId = $nodeToComm[$u];
                $uWeight = array_sum($adj[$u]);
                
                // Weights to neighbor communities
                $neighComms = [];
                foreach ($adj[$u] as $v => $w) {
                    if ($u === $v) continue;
                    $vCommId = $nodeToComm[$v];
                    $neighComms[$vCommId] = ($neighComms[$vCommId] ?? 0.0) + $w;
                }

                // Remove u from its current community
                $communities[$uCommId]['tot'] -= $uWeight;
                $communities[$uCommId]['in'] -= 2 * ($neighComms[$uCommId] ?? 0.0) + ($adj[$u][$u] ?? 0.0);
                
                $bestCommId = $uCommId;
                $bestGain = 0.0;

                foreach ($neighComms as $commId => $w_u_c) {
                    $gain = $this->calculateModularityGain($w_u_c, $uWeight, $communities[$commId]['tot'], $m2);
                    if ($gain > $bestGain) {
                        $bestGain = $gain;
                        $bestCommId = $commId;
                    }
                }

                // Move u to best community
                if ($bestCommId !== $uCommId) {
                    $improved = true;
                }
                
                $nodeToComm[$u] = $bestCommId;
                $communities[$bestCommId]['tot'] += $uWeight;
                $communities[$bestCommId]['in'] += 2 * ($neighComms[$bestCommId] ?? 0.0) + ($adj[$u][$u] ?? 0.0);
            }
        }

        // Map back to string IDs
        $result = [];
        foreach ($nodeToComm as $uIdx => $commId) {
            $result[$ids->id($uIdx)] = $commId;
        }

        return $result;
    }

    private function calculateModularityGain(float $k_i_in, float $k_i, float $Sigma_tot, float $m2): float
    {
        return $k_i_in - $this->resolution * $k_i * $Sigma_tot / $m2;
    }

    private function buildSymmetricAdjacency(AlgorithmGraph $ag): array
    {
        $N = $ag->nodeCount();
        $adj = array_fill(0, $N, []);
        $successors = $ag->successors();
        $weights = $ag->edgeWeights();

        for ($u = 0; $u < $N; $u++) {
            foreach ($successors[$u] as $v) {
                $w = $weights[$u][$v] ?? 1.0;
                $adj[$u][$v] = ($adj[$u][$v] ?? 0.0) + $w;
                if ($u !== $v) {
                    $adj[$v][$u] = ($adj[$v][$u] ?? 0.0) + $w;
                }
            }
        }
        return $adj;
    }
}
