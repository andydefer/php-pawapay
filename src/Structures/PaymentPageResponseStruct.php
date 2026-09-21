<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\ValueObjects\UrlVO;

final class PaymentPageResponseStruct extends Struct
{
    public function __construct(
        public readonly UrlVO $redirectUrl,
        public readonly ?FailureReasonStruct $failureReason = null,
    ) {}
}
