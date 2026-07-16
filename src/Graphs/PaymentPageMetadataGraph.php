<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;

final class PaymentPageMetadataGraph extends Graph
{
    public function __construct(
        public readonly ?string $orderId = null,
        public readonly ?string $customerId = null,
        public readonly ?bool $isPII = null,
    ) {}
}
