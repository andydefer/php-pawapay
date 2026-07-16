<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\PhpServices\Configs\TextNormalizerConfig;
use AndyDefer\PhpServices\Services\TextNormalizerService;
use InvalidArgumentException;

final class MessageVO extends AbstractValueObject
{
    private const MAX_LENGTH = 22;

    public readonly ?string $value;

    public function __construct(?string $value = null)
    {
        if ($value === null) {
            $this->value = null;

            return;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException('Message must be a string');
        }

        $normalizer = new TextNormalizerService(new TextNormalizerConfig);

        $cleaned = $normalizer->normalize($value);

        // ✅ Supprimer les espaces en début et fin
        $cleaned = trim($cleaned);

        // ✅ Troncature à 22 caractères
        if (strlen($cleaned) > self::MAX_LENGTH) {
            $cleaned = substr($cleaned, 0, self::MAX_LENGTH);
            // ✅ Supprimer les espaces après troncature
            $cleaned = trim($cleaned);
        }

        $this->value = $cleaned;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getLength(): int
    {
        return $this->value !== null ? strlen($this->value) : 0;
    }

    public function isEmpty(): bool
    {
        return $this->value === null || $this->value === '';
    }
}
