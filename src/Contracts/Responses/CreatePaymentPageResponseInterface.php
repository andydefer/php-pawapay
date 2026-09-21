<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts\Responses;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;

interface CreatePaymentPageResponseInterface extends ResponseInterface
{
    public function getRedirectUrl(): ?UrlVO;

    public function getRedirectUrlAsString(): ?string;

    public function hasFailureReason(): bool;

    public function getFailureReason(): ?FailureReasonStruct;
}
