<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Responses;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpPawapay\Enums\DepositStatus;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;

final class InitiateDepositResponse extends Response
{
    public function getDepositId(): ?string
    {
        $data = $this->getBody()->format();

        return $data->depositId ?? null;
    }

    public function getStatus(): DepositStatus
    {
        $data = $this->getBody()->format();

        return DepositStatus::tryFrom($data->status ?? '') ?? DepositStatus::REJECTED;
    }

    public function getCreated(): ?string
    {
        $data = $this->getBody()->format();

        return $data->created ?? null;
    }

    public function getFailureReason(): ?FailureReasonStruct
    {
        $data = $this->getBody()->format();

        if (! isset($data->failureReason)) {
            return null;
        }

        return FailureReasonStruct::from((array) $data->failureReason);
    }

    public function isAccepted(): bool
    {
        return $this->getStatus()->isAccepted();
    }

    public function isRejected(): bool
    {
        return $this->getStatus()->isRejected();
    }

    public function isDuplicateIgnored(): bool
    {
        return $this->getStatus()->isDuplicateIgnored();
    }

    public function hasFailureReason(): bool
    {
        return $this->getFailureReason() !== null;
    }

    public static function getStructClass(): string
    {
        return Struct::class;
    }
}
