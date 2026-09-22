<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Enums;

enum Provider: string
{
    // Benin
    case MTN_MOMO_BEN = 'MTN_MOMO_BEN';
    case MOOV_BEN = 'MOOV_BEN';

    // Burkina Faso
    case MOOV_BFA = 'MOOV_BFA';
    case ORANGE_BFA = 'ORANGE_BFA';

    // Cameroon
    case MTN_MOMO_CMR = 'MTN_MOMO_CMR';
    case ORANGE_CMR = 'ORANGE_CMR';

    // Côte d'Ivoire
    case MTN_MOMO_CIV = 'MTN_MOMO_CIV';
    case ORANGE_CIV = 'ORANGE_CIV';
    case WAVE_CIV = 'WAVE_CIV';

    // DRC
    case VODACOM_MPESA_COD = 'VODACOM_MPESA_COD';
    case AIRTEL_COD = 'AIRTEL_COD';
    case ORANGE_COD = 'ORANGE_COD';

    // Ethiopia
    case MPESA_ETH = 'MPESA_ETH';

    // Gabon
    case AIRTEL_GAB = 'AIRTEL_GAB';

    // Ghana
    case MTN_MOMO_GHA = 'MTN_MOMO_GHA';
    case AIRTELTIGO_GHA = 'AIRTELTIGO_GHA';
    case VODAFONE_GHA = 'VODAFONE_GHA';

    // Kenya
    case MPESA_KEN = 'MPESA_KEN';

    // Lesotho
    case MPESA_LSO = 'MPESA_LSO';

    // Malawi
    case AIRTEL_MWI = 'AIRTEL_MWI';
    case TNM_MWI = 'TNM_MWI';

    // Mozambique
    case MOVITEL_MOZ = 'MOVITEL_MOZ';
    case VODACOM_MOZ = 'VODACOM_MOZ';

    // Nigeria
    case AIRTEL_NGA = 'AIRTEL_NGA';
    case MTN_MOMO_NGA = 'MTN_MOMO_NGA';

    // Republic of Congo
    case AIRTEL_COG = 'AIRTEL_COG';
    case MTN_MOMO_COG = 'MTN_MOMO_COG';

    // Rwanda
    case AIRTEL_RWA = 'AIRTEL_RWA';
    case MTN_MOMO_RWA = 'MTN_MOMO_RWA';

    // Senegal
    case FREE_SEN = 'FREE_SEN';
    case ORANGE_SEN = 'ORANGE_SEN';
    case WAVE_SEN = 'WAVE_SEN';

    // Sierra Leone
    case ORANGE_SLE = 'ORANGE_SLE';

    // Tanzania
    case AIRTEL_TZA = 'AIRTEL_TZA';
    case VODACOM_TZA = 'VODACOM_TZA';
    case TIGO_TZA = 'TIGO_TZA';
    case HALOTEL_TZA = 'HALOTEL_TZA';

    // Uganda
    case AIRTEL_OAPI_UGA = 'AIRTEL_OAPI_UGA';
    case MTN_MOMO_UGA = 'MTN_MOMO_UGA';

    // Zambia
    case AIRTEL_OAPI_ZMB = 'AIRTEL_OAPI_ZMB';
    case MTN_MOMO_ZMB = 'MTN_MOMO_ZMB';
    case ZAMTEL_ZMB = 'ZAMTEL_ZMB';

    // =========================================================================
    // MMO
    // =========================================================================

    public function getMMO(): MMO
    {
        return match ($this) {
            self::MTN_MOMO_BEN, self::MTN_MOMO_CMR, self::MTN_MOMO_CIV,
            self::MTN_MOMO_GHA, self::MTN_MOMO_NGA, self::MTN_MOMO_COG,
            self::MTN_MOMO_RWA, self::MTN_MOMO_UGA, self::MTN_MOMO_ZMB => MMO::MTN,

            self::MOOV_BEN, self::MOOV_BFA => MMO::MOOV,

            self::ORANGE_BFA, self::ORANGE_CMR, self::ORANGE_CIV,
            self::ORANGE_COD, self::ORANGE_SEN, self::ORANGE_SLE => MMO::ORANGE,

            self::WAVE_CIV, self::WAVE_SEN => MMO::WAVE,

            self::VODACOM_MPESA_COD, self::VODACOM_MOZ, self::VODACOM_TZA => MMO::VODACOM,

            self::AIRTEL_COD, self::AIRTEL_GAB, self::AIRTEL_MWI,
            self::AIRTEL_NGA, self::AIRTEL_COG, self::AIRTEL_RWA,
            self::AIRTEL_TZA, self::AIRTEL_OAPI_UGA, self::AIRTEL_OAPI_ZMB => MMO::AIRTEL,

            self::MPESA_ETH, self::MPESA_KEN, self::MPESA_LSO => MMO::MPESA,

            self::AIRTELTIGO_GHA => MMO::AT,

            self::VODAFONE_GHA => MMO::VODAFONE,

            self::TNM_MWI => MMO::TNM,

            self::MOVITEL_MOZ => MMO::MOVITEL,

            self::FREE_SEN => MMO::FREE,

            self::TIGO_TZA => MMO::TIGO,

            self::HALOTEL_TZA => MMO::HALOTEL,

            self::ZAMTEL_ZMB => MMO::ZAMTEL,
        };
    }

    // =========================================================================
    // COUNTRY
    // =========================================================================

    public function getCountry(): Country
    {
        return match ($this) {
            self::MTN_MOMO_BEN, self::MOOV_BEN => Country::BEN,
            self::MOOV_BFA, self::ORANGE_BFA => Country::BFA,
            self::MTN_MOMO_CMR, self::ORANGE_CMR => Country::CMR,
            self::MTN_MOMO_CIV, self::ORANGE_CIV, self::WAVE_CIV => Country::CIV,
            self::VODACOM_MPESA_COD, self::AIRTEL_COD, self::ORANGE_COD => Country::COD,
            self::MPESA_ETH => Country::ETH,
            self::AIRTEL_GAB => Country::GAB,
            self::MTN_MOMO_GHA, self::AIRTELTIGO_GHA, self::VODAFONE_GHA => Country::GHA,
            self::MPESA_KEN => Country::KEN,
            self::MPESA_LSO => Country::LSO,
            self::AIRTEL_MWI, self::TNM_MWI => Country::MWI,
            self::MOVITEL_MOZ, self::VODACOM_MOZ => Country::MOZ,
            self::AIRTEL_NGA, self::MTN_MOMO_NGA => Country::NGA,
            self::AIRTEL_COG, self::MTN_MOMO_COG => Country::COG,
            self::AIRTEL_RWA, self::MTN_MOMO_RWA => Country::RWA,
            self::FREE_SEN, self::ORANGE_SEN, self::WAVE_SEN => Country::SEN,
            self::ORANGE_SLE => Country::SLE,
            self::AIRTEL_TZA, self::VODACOM_TZA, self::TIGO_TZA, self::HALOTEL_TZA => Country::TZA,
            self::AIRTEL_OAPI_UGA, self::MTN_MOMO_UGA => Country::UGA,
            self::AIRTEL_OAPI_ZMB, self::MTN_MOMO_ZMB, self::ZAMTEL_ZMB => Country::ZMB,
        };
    }

    // =========================================================================
    // CALLING CODE & CURRENCIES
    // =========================================================================

    public function getCallingCode(): CallingCode
    {
        return $this->getCountry()->getCallingCode();
    }

    public function getCurrencies(): array
    {
        return $this->getCountry()->getCurrencies();
    }

    // =========================================================================
    // PHONE PREFIXES
    // =========================================================================

    /**
     * Récupère les préfixes téléphoniques locaux associés à ce provider.
     *
     * Les préfixes dépendent du MMO (opérateur), pas du pays :
     * Vodacom utilise les mêmes préfixes partout, idem Airtel, Orange.
     *
     * @return array<string>
     */
    public function getPhonePrefixes(): array
    {
        return match ($this->getMMO()) {
            MMO::VODACOM => ['81', '82', '83', '80', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99'],
            MMO::AIRTEL => ['99', '97', '81', '82', '83', '84', '85'],
            MMO::ORANGE => ['85', '89', '84', '86', '87', '88'],
            default => [],
        };
    }

    // =========================================================================
    // STATIC HELPERS
    // =========================================================================

    /**
     * Détecte le Provider à partir d'un numéro de téléphone.
     *
     * Essaie les formats : international (`243XXXXXXXXX`), local
     * (`0XXXXXXXXX`), ou brut (`XXXXXXXXX`). Le pays est déduit du
     * calling code présent dans le numéro, puis le MMO est identifié
     * par le préfixe local.
     */
    public static function fromPhoneNumber(string $phoneNumber): ?self
    {
        $cleaned = preg_replace('/\D+/', '', $phoneNumber);

        if ($cleaned === null || $cleaned === '') {
            return null;
        }

        foreach (self::cases() as $provider) {
            // Ex: '+243' → '243'
            $callingCode = ltrim($provider->getCallingCode()->value, '+');

            if ($callingCode === '' || ! str_starts_with($cleaned, $callingCode)) {
                continue;
            }

            $national = substr($cleaned, strlen($callingCode));

            if (str_starts_with($national, '0')) {
                $national = substr($national, 1);
            }

            $prefix = substr($national, 0, 2);

            if ($prefix === '' || ! in_array($prefix, $provider->getPhonePrefixes(), true)) {
                continue;
            }

            return $provider;
        }

        return null;
    }

    /**
     * Récupère tous les providers pour un pays donné.
     *
     * @return array<self>
     */
    public static function forCountry(Country $country): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $provider): bool => $provider->getCountry() === $country,
        ));
    }

    /**
     * Récupère tous les providers associés à un MMO donné.
     *
     * @return array<self>
     */
    public static function forMMO(MMO $mmo): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $provider): bool => $provider->getMMO() === $mmo,
        ));
    }
}
