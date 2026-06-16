<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use InvalidArgumentException;

final class PreAuthorisationCodeVO extends AbstractValueObject
{
    public function __construct(public readonly ?string $value = null)
    {
        if ($value !== null && ! is_string($value)) {
            throw new InvalidArgumentException('Pre-authorisation code must be a string');
        }
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
