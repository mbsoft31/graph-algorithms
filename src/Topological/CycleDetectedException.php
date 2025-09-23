<?php

declare(strict_types=1);

namespace Mbsoft\Graph\Algorithms\Topological;


use RuntimeException;

/**
 * Exception thrown when attempting topological sort on a graph with cycles.
 */
class CycleDetectedException extends RuntimeException
{
    public function __construct(string $message = 'Graph contains a cycle - topological sort impossible')
    {
        parent::__construct($message);
    }
}