<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final class UuidVO extends AbstractValueObject
{
    public readonly ?string $value;

    public function __construct(?string $value = null)
    {
        if ($value === null) {
            $this->value = Uuid::uuid4()->toString();

            return;
        }

        if (! Uuid::isValid($value)) {
            throw new InvalidArgumentException("Invalid UUID: {$value}");
        }

        $this->value = $value;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
