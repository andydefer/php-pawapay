<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpPawapay\Enums\MMO;

final class PayerGraph extends Graph
{
    public function __construct(
        public readonly MMO $type,
        public readonly AccountDetailsGraph $accountDetails,
    ) {}
}
