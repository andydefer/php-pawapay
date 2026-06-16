<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use InvalidArgumentException;

final class PhoneNumberVO extends AbstractValueObject
{
    public function __construct(public readonly string $value)
    {
        $pattern = '/^\+?[1-9]\d{1,14}$/';

        if (! preg_match($pattern, $value)) {
            throw new InvalidArgumentException("Invalid phone number: {$value}");
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
