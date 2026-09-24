<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Responses;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Utils\EmptyStruct;
use AndyDefer\PhpPawapay\Contracts\Responses\PredictProviderResponseInterface;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

final class PredictProviderResponse extends Response implements PredictProviderResponseInterface
{
    public function getCountry(): ?Country
    {
        $data = $this->getBody()->format();

        return isset($data['country'])
            ? Country::tryFrom($data['country'])
            : null;
    }

    public function getProvider(): ?Provider
    {
        $data = $this->getBody()->format();

        return isset($data['provider'])
            ? Provider::tryFrom($data['provider'])
            : null;
    }

    public function getPhoneNumber(): ?PhoneNumberVO
    {
        $data = $this->getBody()->format();

        if (! isset($data['phoneNumber'])) {
            return null;
        }

        return PhoneNumberVO::from($data['phoneNumber']);
    }

    public function getFailureReason(): ?FailureReasonStruct
    {
        $data = $this->getBody()->format();

        if (! isset($data['failureReason'])) {
            return null;
        }

        return FailureReasonStruct::from($data['failureReason']);
    }

    public function hasFailureReason(): bool
    {
        return $this->getFailureReason() !== null;
    }

    public function isFound(): bool
    {
        return $this->getProvider() !== null && $this->getCountry() !== null;
    }

    public static function getStructClass(): string
    {
        return EmptyStruct::class;
    }
}
