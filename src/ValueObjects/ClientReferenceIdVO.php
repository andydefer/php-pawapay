<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;

final class ClientReferenceIdVO extends AbstractValueObject
{
    public const MIN_LENGTH = 4;

    public const MAX_LENGTH = 64;

    public function __construct(
        private readonly string $value,
    ) {
        $length = mb_strlen($value);

        if ($length < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Client reference id must be at least %d characters, %d given.',
                    self::MIN_LENGTH,
                    $length,
                ),
            );
        }

        if ($length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Client reference id must not exceed %d characters, %d given.',
                    self::MAX_LENGTH,
                    $length,
                ),
            );
        }

        if (preg_match('/^[a-zA-Z0-9]+(-[a-zA-Z0-9]+)*$/', $value) !== 1) {
            throw new \InvalidArgumentException(
                'Client reference id must contain only letters, digits, and hyphens between alphanumeric groups.',
            );
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
