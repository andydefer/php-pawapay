<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts\Responses;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpPawapay\Enums\DepositSearchStatus;
use AndyDefer\PhpPawapay\Structures\DepositDataStruct;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;

interface CheckDepositStatusResponseInterface extends ResponseInterface
{
    public function getSearchStatus(): DepositSearchStatus;

    public function getDepositData(): ?DepositDataStruct;

    public function isFound(): bool;

    public function isNotFound(): bool;

    public function hasFailureReason(): bool;

    public function getFailureReason(): ?FailureReasonStruct;
}
