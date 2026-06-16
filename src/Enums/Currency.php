<?php

namespace AndyDefer\PhpPawapay\Enums;

enum Currency: string
{
    case XOF = 'XOF'; // West African CFA franc
    case XAF = 'XAF'; // Central African CFA franc
    case CDF = 'CDF'; // Congolese franc
    case USD = 'USD'; // US dollar
    case ETB = 'ETB'; // Ethiopian birr
    case GHS = 'GHS'; // Ghanaian cedi
    case KES = 'KES'; // Kenyan shilling
    case LSL = 'LSL'; // Lesotho loti
    case MWK = 'MWK'; // Malawian kwacha
    case MZN = 'MZN'; // Mozambican metical
    case NGN = 'NGN'; // Nigerian naira
    case RWF = 'RWF'; // Rwandan franc
    case SLE = 'SLE'; // Sierra Leonean leone
    case TZS = 'TZS'; // Tanzanian shilling
    case UGX = 'UGX'; // Ugandan shilling
    case ZMW = 'ZMW'; // Zambian kwacha

    public function getCountries(): array
    {
        return match ($this) {
            self::XOF => [
                Country::BEN,
                Country::BFA,
                Country::CIV,
                Country::SEN,
            ],
            self::XAF => [
                Country::CMR,
                Country::GAB,
                Country::COG,
            ],
            self::CDF => [Country::COD],
            self::USD => [Country::COD],
            self::ETB => [Country::ETH],
            self::GHS => [Country::GHA],
            self::KES => [Country::KEN],
            self::LSL => [Country::LSO],
            self::MWK => [Country::MWI],
            self::MZN => [Country::MOZ],
            self::NGN => [Country::NGA],
            self::RWF => [Country::RWA],
            self::SLE => [Country::SLE],
            self::TZS => [Country::TZA],
            self::UGX => [Country::UGA],
            self::ZMW => [Country::ZMB],
        };
    }

    public function getCallingCodes(): array
    {
        return array_map(
            fn ($country) => $country->getCallingCode(),
            $this->getCountries()
        );
    }

    public function getMmos(): array
    {
        $mmos = [];
        foreach ($this->getCountries() as $country) {
            foreach ($country->getMmos() as $mmo) {
                if (! in_array($mmo, $mmos, true)) {
                    $mmos[] = $mmo;
                }
            }
        }

        return $mmos;
    }

    public function getProviders(): array
    {
        $providers = [];
        foreach ($this->getCountries() as $country) {
            foreach ($country->getProviders() as $provider) {
                if (! in_array($provider, $providers, true)) {
                    $providers[] = $provider;
                }
            }
        }

        return $providers;
    }

    public function getSymbol(): string
    {
        return match ($this) {
            self::XOF, self::XAF => 'CFA',
            self::CDF => 'FC',
            self::USD => '$',
            self::ETB => 'Br',
            self::GHS => '₵',
            self::KES => 'KSh',
            self::LSL => 'L',
            self::MWK => 'MK',
            self::MZN => 'MT',
            self::NGN => '₦',
            self::RWF => 'FRw',
            self::SLE => 'Le',
            self::TZS => 'TSh',
            self::UGX => 'USh',
            self::ZMW => 'ZK',
        };
    }

    public function getDecimals(): int
    {
        return match ($this) {
            self::USD, self::NGN, self::KES, self::TZS, self::UGX, self::GHS => 2,
            self::XOF, self::XAF, self::CDF, self::RWF, self::MWK, self::ZMW => 2,
            self::ETB => 2,
            self::LSL => 2,
            self::MZN => 2,
            self::SLE => 2,
            default => 2,
        };
    }
}
