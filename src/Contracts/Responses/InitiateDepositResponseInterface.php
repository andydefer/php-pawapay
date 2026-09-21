<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts\Responses;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpPawapay\Enums\DepositStatus;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;

interface InitiateDepositResponseInterface extends ResponseInterface
{
    public function getDepositId(): ?string;

    public function getStatus(): DepositStatus;

    public function getCreated(): ?string;

    public function getFailureReason(): ?FailureReasonStruct;

    public function isAccepted(): bool;

    public function isRejected(): bool;

    public function isDuplicateIgnored(): bool;

    public function hasFailureReason(): bool;
}
