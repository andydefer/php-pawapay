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

    private const MIN_FIELDS = 1;

    /**
     * Keys automatically stripped from the metadata before sending to PawaPay.
     */
    private const EXCLUDED_KEYS = ['isPII'];

    public function __construct(public readonly StrictDataObject $value)
    {
        $data = $value->toArray();

        // Strip the excluded keys before validation so that a metadata
        // containing only "isPII" is rejected as empty.
        $data = array_diff_key($data, array_flip(self::EXCLUDED_KEYS));

        $count = count($data);

        if ($count < self::MIN_FIELDS) {
            throw new InvalidArgumentException(
                sprintf(
                    'Metadata must contain at least %d field, %d given.',
                    self::MIN_FIELDS,
                    $count,
                ),
            );
        }

        if ($count > self::MAX_FIELDS) {
            throw new InvalidArgumentException(
                sprintf(
                    'Metadata cannot exceed %d fields, %d given.',
                    self::MAX_FIELDS,
                    $count,
                ),
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

            if ($key === '') {
                throw new InvalidArgumentException('Metadata field name must not be empty.');
            }
        }
    }

    public function getValue(): Sequential
    {
        $data = $this->toArray();

        $result = [];
        foreach ($data as $key => $value) {
            $result[] = new StrictDataObject([$key => $value]);
        }

        return new Sequential($result);
    }

    /**
     * @return array<string, scalar>
     */
    public function toArray(): array
    {
        $data = $this->value->toArray();

        return array_diff_key($data, array_flip(self::EXCLUDED_KEYS));
    }

    public function count(): int
    {
        return count($this->toArray());
    }

    public function get(string $key): mixed
    {
        $data = $this->toArray();

        return $data[$key] ?? null;
    }

    public function has(string $key): bool
    {
        $data = $this->toArray();

        return array_key_exists($key, $data);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
