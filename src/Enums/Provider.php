<?php

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

    public function getCallingCode(): CallingCode
    {
        return $this->getCountry()->getCallingCode();
    }

    public function getCurrencies(): array
    {
        return $this->getCountry()->getCurrencies();
    }

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
}
