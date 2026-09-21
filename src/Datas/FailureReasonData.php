<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\PhpPawapay\Enums\FailureCode;

final class FailureReasonData extends AbstractData
{
    public function __construct(
        public readonly FailureCode $failureCode,
        public readonly string $failureMessage,
    ) {}
}
