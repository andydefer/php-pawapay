<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\PhpPawapay\Enums\DepositSearchStatus;

final class CheckDepositStatusData extends AbstractData
{
    public function __construct(
        public readonly DepositSearchStatus $searchStatus,
        public readonly ?DepositDataData $depositData,
        public readonly bool $isFound,
        public readonly bool $isNotFound,
        public readonly ?FailureReasonData $failureReason,
        public readonly bool $hasFailureReason,
    ) {}
}
