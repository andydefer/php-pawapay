<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;

final class AmountDetailsGraph extends Graph
{
    public function __construct(
        public readonly AmountVO $amount,
        public readonly Currency $currency,
    ) {}
}
