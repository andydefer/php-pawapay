<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpPawapay\Enums\Currency;

/**
 * Collection of Currency enums.
 *
 * @extends AbstractTypedCollection<Currency>
 */
final class CurrencyCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(Currency::class);
    }

    /**
     * Get only African currencies.
     */
    public function getAfricanCurrencies(): self
    {
        $africanCurrencies = [
            Currency::XOF,
            Currency::XAF,
            Currency::CDF,
            Currency::ETB,
            Currency::GHS,
            Currency::KES,
            Currency::LSL,
            Currency::MWK,
            Currency::MZN,
            Currency::NGN,
            Currency::RWF,
            Currency::SLE,
            Currency::TZS,
            Currency::UGX,
            Currency::ZMW,
        ];

        return $this->filter(fn (Currency $currency) => in_array($currency, $africanCurrencies, true));
    }

    /**
     * Get only CFA currencies (XOF, XAF).
     */
    public function getCFACurrencies(): self
    {
        return $this->filter(fn (Currency $currency) => in_array($currency, [Currency::XOF, Currency::XAF], true));
    }

    /**
     * Get currencies by country code.
     *
     * @param  string  $countryCode  The country code (e.g., 'COD', 'KEN')
     */
    public function getByCountry(string $countryCode): self
    {
        return $this->filter(function (Currency $currency) use ($countryCode) {
            $countries = $currency->getCountries();
            $countryCodes = array_map(fn ($country) => $country->value, $countries);

            return in_array($countryCode, $countryCodes, true);
        });
    }

    /**
     * Get currencies that support a specific MMO.
     *
     * @param  string  $mmo  The MMO name
     */
    public function getByMmo(string $mmo): self
    {
        return $this->filter(fn (Currency $currency) => in_array($mmo, $currency->getMmos(), true));
    }

    /**
     * Get currencies that support a specific provider.
     *
     * @param  string  $provider  The provider name
     */
    public function getByProvider(string $provider): self
    {
        return $this->filter(fn (Currency $currency) => in_array($provider, $currency->getProviders(), true));
    }

    /**
     * Get currencies with a specific symbol.
     *
     * @param  string  $symbol  The currency symbol
     */
    public function getBySymbol(string $symbol): self
    {
        return $this->filter(fn (Currency $currency) => $currency->getSymbol() === $symbol);
    }

    /**
     * Get currencies with a specific number of decimals.
     *
     * @param  int  $decimals  The number of decimals
     */
    public function getByDecimals(int $decimals): self
    {
        return $this->filter(fn (Currency $currency) => $currency->getDecimals() === $decimals);
    }

    /**
     * Get the default currency for a country.
     *
     * @param  string  $countryCode  The country code (e.g., 'COD', 'KEN')
     */
    public function getDefaultForCountry(string $countryCode): ?Currency
    {
        $currencies = $this->getByCountry($countryCode);

        return $currencies->first();
    }

    /**
     * Create a collection with default currencies (USD, CDF).
     */
    public static function default(): self
    {
        $collection = new self;
        $collection->add(Currency::USD);
        $collection->add(Currency::CDF);

        return $collection;
    }

    /**
     * Get all currency values as array.
     *
     * @return array<string>
     */
    public function toValues(): array
    {
        return $this->map(fn (Currency $currency) => $currency->value)->toArray();
    }

    /**
     * Get all currency symbols as array.
     *
     * @return array<string>
     */
    public function toSymbols(): array
    {
        return $this->map(fn (Currency $currency) => $currency->getSymbol())->toArray();
    }
}
