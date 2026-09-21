<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpPawapay\Enums\Provider;

/**
 * Collection of Provider enums.
 *
 * @extends AbstractTypedCollection<Provider>
 */
final class ProviderCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(Provider::class);
    }

    public static function default(): self
    {
        $collection = new self;

        foreach (Provider::cases() as $provider) {
            $collection->add($provider);
        }

        return $collection;
    }

    public function getByCountry(string $countryCode): self
    {
        return $this->filter(
            fn (Provider $provider): bool => $provider->getCountry()->value === $countryCode,
        );
    }

    /**
     * @return array<int, string>
     */
    public function toValues(): array
    {
        return $this->map(fn (Provider $provider): string => $provider->value)->toArray();
    }
}
