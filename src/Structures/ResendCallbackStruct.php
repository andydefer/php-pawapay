<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpPawapay\Enums\ResendCallbackStatus;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

final class ResendCallbackStruct extends Graph
{
    public function __construct(
        public readonly UuidVO $depositId,
        public readonly ResendCallbackStatus $status,
        public readonly ?FailureReasonStruct $failureReason = null,
    ) {}
}
