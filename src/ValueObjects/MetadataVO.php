<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use InvalidArgumentException;

final class MetadataVO extends AbstractValueObject
{
    public function __construct(public readonly ?StrictDataObject $value = null)
    {
        if ($value !== null && ! is_array($value)) {
            throw new InvalidArgumentException('Metadata must be an array');
        }
    }

    public function getValue(): ?StrictDataObject
    {
        return $this->value;
    }
}
