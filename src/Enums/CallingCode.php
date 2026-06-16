<?php

namespace AndyDefer\PhpPawapay\Enums;

enum CallingCode: string
{
    case BEN = '+229';
    case BFA = '+226';
    case CMR = '+237';
    case CIV = '+225';
    case COD = '+243';
    case ETH = '+251';
    case GAB = '+241';
    case GHA = '+233';
    case KEN = '+254';
    case LSO = '+266';
    case MWI = '+265';
    case MOZ = '+258';
    case NGA = '+234';
    case COG = '+242';
    case RWA = '+250';
    case SEN = '+221';
    case SLE = '+232';
    case TZA = '+255';
    case UGA = '+256';
    case ZMB = '+260';

    public function getCountry(): Country
    {
        return match ($this) {
            self::BEN => Country::BEN,
            self::BFA => Country::BFA,
            self::CMR => Country::CMR,
            self::CIV => Country::CIV,
            self::COD => Country::COD,
            self::ETH => Country::ETH,
            self::GAB => Country::GAB,
            self::GHA => Country::GHA,
            self::KEN => Country::KEN,
            self::LSO => Country::LSO,
            self::MWI => Country::MWI,
            self::MOZ => Country::MOZ,
            self::NGA => Country::NGA,
            self::COG => Country::COG,
            self::RWA => Country::RWA,
            self::SEN => Country::SEN,
            self::SLE => Country::SLE,
            self::TZA => Country::TZA,
            self::UGA => Country::UGA,
            self::ZMB => Country::ZMB,
        };
    }

    public function getProviders(): array
    {
        $country = $this->getCountry();

        return array_filter(
            Provider::cases(),
            fn ($provider) => $provider->getCountry() === $country
        );
    }

    public function getMMOs(): array
    {
        $mmos = [];
        foreach (MMO::cases() as $mmo) {
            $providers = $mmo->getProviders();
            if (! empty($providers)) {
                $mmos[] = $mmo;
            }
        }

        return $mmos;
    }

    public function getCurrencies(): array
    {
        return $this->getCountry()->getCurrencies();
    }
}
