<?php

namespace AndyDefer\PhpPawapay\Enums;

enum Country: string
{
    // Benin
    case BEN = 'BEN';
    // Burkina Faso
    case BFA = 'BFA';
    // Cameroon
    case CMR = 'CMR';
    // Côte d'Ivoire
    case CIV = 'CIV';
    // Democratic Republic of the Congo
    case COD = 'COD';
    // Ethiopia
    case ETH = 'ETH';
    // Gabon
    case GAB = 'GAB';
    // Ghana
    case GHA = 'GHA';
    // Kenya
    case KEN = 'KEN';
    // Lesotho
    case LSO = 'LSO';
    // Malawi
    case MWI = 'MWI';
    // Mozambique
    case MOZ = 'MOZ';
    // Nigeria
    case NGA = 'NGA';
    // Republic of the Congo
    case COG = 'COG';
    // Rwanda
    case RWA = 'RWA';
    // Senegal
    case SEN = 'SEN';
    // Sierra Leone
    case SLE = 'SLE';
    // Tanzania
    case TZA = 'TZA';
    // Uganda
    case UGA = 'UGA';
    // Zambia
    case ZMB = 'ZMB';

    public function getIso3(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return match ($this) {
            self::BEN => 'Benin',
            self::BFA => 'Burkina Faso',
            self::CMR => 'Cameroon',
            self::CIV => 'Côte d\'Ivoire',
            self::COD => 'Democratic Republic of the Congo',
            self::ETH => 'Ethiopia',
            self::GAB => 'Gabon',
            self::GHA => 'Ghana',
            self::KEN => 'Kenya',
            self::LSO => 'Lesotho',
            self::MWI => 'Malawi',
            self::MOZ => 'Mozambique',
            self::NGA => 'Nigeria',
            self::COG => 'Republic of the Congo',
            self::RWA => 'Rwanda',
            self::SEN => 'Senegal',
            self::SLE => 'Sierra Leone',
            self::TZA => 'Tanzania',
            self::UGA => 'Uganda',
            self::ZMB => 'Zambia',
        };
    }

    public function getCallingCode(): CallingCode
    {
        return match ($this) {
            self::BEN => CallingCode::BEN,
            self::BFA => CallingCode::BFA,
            self::CMR => CallingCode::CMR,
            self::CIV => CallingCode::CIV,
            self::COD => CallingCode::COD,
            self::ETH => CallingCode::ETH,
            self::GAB => CallingCode::GAB,
            self::GHA => CallingCode::GHA,
            self::KEN => CallingCode::KEN,
            self::LSO => CallingCode::LSO,
            self::MWI => CallingCode::MWI,
            self::MOZ => CallingCode::MOZ,
            self::NGA => CallingCode::NGA,
            self::COG => CallingCode::COG,
            self::RWA => CallingCode::RWA,
            self::SEN => CallingCode::SEN,
            self::SLE => CallingCode::SLE,
            self::TZA => CallingCode::TZA,
            self::UGA => CallingCode::UGA,
            self::ZMB => CallingCode::ZMB,
        };
    }

    public function getCurrencies(): array
    {
        return array_filter(
            Currency::cases(),
            fn ($currency) => in_array($this, $currency->getCountries())
        );
    }

    public function getMmos(): array
    {
        $mmos = [];
        foreach (MMO::cases() as $mmo) {
            $providers = $mmo->getProviders();
            foreach ($providers as $provider) {
                if ($provider->getCountry() === $this) {
                    $mmos[] = $mmo;
                    break;
                }
            }
        }

        return $mmos;
    }

    public function getProviders(): array
    {
        return array_filter(
            Provider::cases(),
            fn ($provider) => $provider->getCountry() === $this
        );
    }

    public function getRegion(): string
    {
        return match ($this) {
            self::BEN, self::BFA, self::CIV, self::SEN, self::GHA, self::NGA, self::SLE => 'West Africa',
            self::CMR, self::GAB, self::COG, self::COD => 'Central Africa',
            self::ETH, self::KEN, self::MWI, self::MOZ, self::RWA, self::TZA, self::UGA => 'East Africa',
            self::LSO, self::ZMB => 'Southern Africa',
        };
    }
}
