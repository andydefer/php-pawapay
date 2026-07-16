<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures;

use AndyDefer\PhpClient\Abstracts\Struct;

final class PaymentPageResponseStruct extends Struct
{
    public function __construct(
        public readonly string $redirectUrl,
        public readonly ?FailureReasonStruct $failureReason = null,
    ) {}
}
