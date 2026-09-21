<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\PhpServices\Configs\TextNormalizerConfig;
use AndyDefer\PhpServices\Services\TextNormalizerService;

final class CustomerMessageVO extends AbstractValueObject
{
    public const MIN_LENGTH = 4;

    public const MAX_LENGTH = 22;

    private readonly string $value;

    public function __construct(string $value)
    {
        $normalizer = new TextNormalizerService(new TextNormalizerConfig);

        $normalized = $normalizer->normalize($value);

        $length = mb_strlen($normalized);

        if ($length < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Customer message must be at least %d characters, %d given.',
                    self::MIN_LENGTH,
                    $length,
                ),
            );
        }

        if ($length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Customer message must not exceed %d characters, %d given.',
                    self::MAX_LENGTH,
                    $length,
                ),
            );
        }

        $this->value = $normalized;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
