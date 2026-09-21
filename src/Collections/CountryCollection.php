<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpPawapay\Enums\Country;

/**
 * Collection of Country enums.
 *
 * @extends AbstractTypedCollection<Country>
 */
final class CountryCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(Country::class);
    }

    public static function default(): self
    {
        $collection = new self;
        $collection->add(Country::COD);

        return $collection;
    }

    /**
     * @return array<string>
     */
    public function toValues(): array
    {
        return $this->map(fn (Country $country) => $country->value)->toArray();
    }
}
