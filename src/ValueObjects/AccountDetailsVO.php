<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Utils\DataObject;
use AndyDefer\PhpPawapay\Enums\Provider;

final class AccountDetailsVO extends AbstractValueObject
{
    public function __construct(
        public readonly PhoneNumberVO $phoneNumber,
        public readonly Provider $provider
    ) {}

    public function getValue(): DataObject
    {
        return new DataObject([
            'phoneNumber' => $this->phoneNumber,
            'provider' => $this->provider->value,
        ]);
    }
}
