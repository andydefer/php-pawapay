<?php

namespace AndyDefer\PhpPawapay\Enums;

enum MMO: string
{
    case MTN = 'MTN';
    case MOOV = 'Moov';
    case ORANGE = 'Orange';
    case WAVE = 'Wave';
    case VODACOM = 'Vodacom';
    case AIRTEL = 'Airtel';
    case SAFARICOM_MPESA = 'Safaricom MPesa';
    case AT = 'AT';
    case VODAFONE = 'Vodafone';
    case MPESA = 'MPesa';
    case TNM = 'TNM';
    case MOVITEL = 'Movitel';
    case FREE = 'Free';
    case TIGO = 'Tigo';
    case HALOTEL = 'Halotel';
    case ZAMTEL = 'Zamtel';

    public function getProviders(): array
    {
        return array_filter(
            Provider::cases(),
            fn ($provider) => $provider->getMMO() === $this
        );
    }

    public function getCountries(): array
    {
        $countries = [];
        foreach ($this->getProviders() as $provider) {
            $country = $provider->getCountry();
            if (! in_array($country, $countries, true)) {
                $countries[] = $country;
            }
        }

        return $countries;
    }

    public function getCurrencies(): array
    {
        $currencies = [];
        foreach ($this->getCountries() as $country) {
            foreach ($country->getCurrencies() as $currency) {
                if (! in_array($currency, $currencies, true)) {
                    $currencies[] = $currency;
                }
            }
        }

        return $currencies;
    }

    public function getCallingCodes(): array
    {
        return array_map(
            fn ($country) => $country->getCallingCode(),
            $this->getCountries()
        );
    }
}
