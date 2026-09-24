<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts\Responses;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

interface PredictProviderResponseInterface extends ResponseInterface
{
    public function getCountry(): ?Country;

    public function getProvider(): ?Provider;

    public function getPhoneNumber(): ?PhoneNumberVO;

    public function getFailureReason(): ?FailureReasonStruct;

    public function hasFailureReason(): bool;

    public function isFound(): bool;
}
