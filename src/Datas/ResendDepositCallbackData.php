<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\PhpPawapay\Enums\ResendCallbackStatus;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

final class ResendDepositCallbackData extends AbstractData
{
    public function __construct(
        public readonly ?UuidVO $depositId,
        public readonly ?ResendCallbackStatus $status,
        public readonly ?FailureReasonData $failureReason,
        public readonly bool $isAccepted,
        public readonly bool $isRejected,
        public readonly bool $hasFailureReason,
    ) {}
}
