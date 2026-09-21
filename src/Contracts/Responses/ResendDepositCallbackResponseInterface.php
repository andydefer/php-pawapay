<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts\Responses;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpPawapay\Enums\ResendCallbackStatus;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;

interface ResendDepositCallbackResponseInterface extends ResponseInterface
{
    public function getDepositId(): ?string;

    public function getStatus(): ?ResendCallbackStatus;

    public function getFailureReason(): ?FailureReasonStruct;

    public function isAccepted(): bool;

    public function isRejected(): bool;

    public function hasFailureReason(): bool;
}
