<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures;

use AndyDefer\PhpClient\Abstracts\Graph;

final class FailureReasonStruct extends Graph
{
    public function __construct(
        public readonly string $failureCode,
        public readonly string $failureMessage,
    ) {}
}
