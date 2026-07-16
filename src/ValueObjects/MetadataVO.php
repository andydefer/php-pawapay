<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Utils\Sequential;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use InvalidArgumentException;

final class MetadataVO extends AbstractValueObject
{
    private const MAX_FIELDS = 10;

    // ⚠️ Clés à exclure automatiquement des métadonnées Pawapay
    private const EXCLUDED_KEYS = ['isPII'];

    public function __construct(public readonly ?StrictDataObject $value = null)
    {
        if ($value === null) {
            return;
        }

        $data = $value->toArray();

        if (count($data) > self::MAX_FIELDS) {
            throw new InvalidArgumentException(
                sprintf('Metadata cannot exceed %d fields. Got %d fields.', self::MAX_FIELDS, count($data))
            );
        }

        foreach ($data as $key => $val) {
            if (! is_string($val) && ! is_int($val) && ! is_float($val) && ! is_bool($val)) {
                throw new InvalidArgumentException(
                    sprintf('Metadata value for key "%s" must be a scalar. Got %s.', $key, gettype($val))
                );
            }
        }

        foreach (array_keys($data) as $key) {
            if (! is_string($key)) {
                throw new InvalidArgumentException(
                    sprintf('Metadata key must be a string. Got %s.', gettype($key))
                );
            }
            if ($key === null || $key === '') {
                throw new InvalidArgumentException('Metadata field name must not be null or empty');
            }
        }
    }

    public function getValue(): ?Sequential
    {
        if ($this->value === null) {
            return null;
        }

        $data = $this->value->toArray();

        // ✅ Supprimer automatiquement les clés exclues (isPII, etc.)
        $filtered = array_diff_key($data, array_flip(self::EXCLUDED_KEYS));

        // ✅ Supprimer les valeurs null
        $filtered = array_filter($filtered, function ($value) {
            return $value !== null;
        });

        if (empty($filtered)) {
            return null;
        }

        $result = [];
        foreach ($filtered as $key => $value) {
            $result[] = new StrictDataObject([$key => $value]);
        }

        return new Sequential($result);
    }

    public function toArray(): ?array
    {
        if ($this->value === null) {
            return null;
        }

        $data = $this->value->toArray();

        // ✅ Supprimer automatiquement les clés exclues
        $filtered = array_diff_key($data, array_flip(self::EXCLUDED_KEYS));

        return array_filter($filtered, function ($value) {
            return $value !== null;
        });
    }

    public function count(): int
    {
        if ($this->value === null) {
            return 0;
        }

        return count($this->value->toArray());
    }

    public function get(string $key): mixed
    {
        if ($this->value === null) {
            return null;
        }

        $data = $this->value->toArray();

        return $data[$key] ?? null;
    }

    public function has(string $key): bool
    {
        if ($this->value === null) {
            return false;
        }

        $data = $this->value->toArray();

        return isset($data[$key]);
    }

    public function isEmpty(): bool
    {
        return $this->value === null || $this->count() === 0;
    }
}
