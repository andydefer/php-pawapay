<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use InvalidArgumentException;

final class AmountVO extends AbstractValueObject
{
    public function __construct(public readonly float $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Amount must be positive: {$value}");
        }

        if (! is_numeric($value) || $value <= 0) {
            throw new InvalidArgumentException("Invalid amount: {$value}");
        }
    }

    public function getValue(): string
    {
        return (string) $this->value;
    }
}
