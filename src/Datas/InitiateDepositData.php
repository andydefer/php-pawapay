<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\PhpPawapay\Enums\DepositStatus;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

final class InitiateDepositData extends AbstractData
{
    public function __construct(
        public readonly ?UuidVO $depositId,
        public readonly DepositStatus $status,
        public readonly ?DateTimeZuluVO $created,
        public readonly ?FailureReasonData $failureReason,
        public readonly bool $isAccepted,
        public readonly bool $isRejected,
        public readonly bool $isDuplicateIgnored,
        public readonly bool $hasFailureReason,
    ) {}
}
