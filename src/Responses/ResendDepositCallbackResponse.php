<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Responses;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Utils\EmptyStruct;
use AndyDefer\PhpPawapay\Enums\ResendCallbackStatus;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;

final class ResendDepositCallbackResponse extends Response
{
    public function getDepositId(): ?string
    {
        $data = $this->getBody()->format();

        return $data['depositId'] ?? null;
    }

    public function getStatus(): ?ResendCallbackStatus
    {
        $data = $this->getBody()->format();

        return isset($data['status']) ? ResendCallbackStatus::tryFrom($data['status']) : null;
    }

    public function getFailureReason(): ?FailureReasonStruct
    {
        $data = $this->getBody()->format();

        if (! isset($data['failureReason'])) {
            return null;
        }

        return FailureReasonStruct::from($data['failureReason']);
    }

    public function isAccepted(): bool
    {
        return $this->getStatus()?->isAccepted() ?? false;
    }

    public function isRejected(): bool
    {
        return $this->getStatus()?->isRejected() ?? false;
    }

    public function hasFailureReason(): bool
    {
        $data = $this->getBody()->format();

        return isset($data['failureReason']);
    }

    public static function getStructClass(): string
    {
        return EmptyStruct::class;
    }
}
