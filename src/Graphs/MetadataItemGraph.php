<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;

final class MetadataItemGraph extends Graph
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
        public readonly ?bool $isPII = null,
    ) {}
}
