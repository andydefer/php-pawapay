<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Responses;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;
use AndyDefer\PhpPawapay\Structures\PaymentPageResponseStruct;

final class CreatePaymentPageResponse extends Response
{
    public function getRedirectUrl(): ?UrlVO
    {
        $data = $this->getBody()->format();

        if (! isset($data['redirectUrl'])) {
            return null;
        }

        return new UrlVO($data['redirectUrl']);
    }

    public function getRedirectUrlAsString(): ?string
    {
        $data = $this->getBody()->format();

        return $data['redirectUrl'] ?? null;
    }

    public function hasFailureReason(): bool
    {
        $data = $this->getBody()->format();

        return isset($data['failureReason']);
    }

    public function getFailureReason(): ?FailureReasonStruct
    {
        $data = $this->getBody()->format();

        if (! isset($data['failureReason'])) {
            return null;
        }

        return FailureReasonStruct::from((array) $data['failureReason']);
    }

    public static function getStructClass(): string
    {
        return PaymentPageResponseStruct::class;
    }
}
