<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Datas\ErrorResponseData;
use AndyDefer\PhpPawapay\Datas\InitiateDepositData;
use AndyDefer\PhpPawapay\Datas\PredictProviderData;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\Language;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Enums\PayerType;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\Graphs\AmountDetailsGraph;
use AndyDefer\PhpPawapay\PawapayClient;
use AndyDefer\PhpPawapay\Records\CheckDepositStatusRecord;
use AndyDefer\PhpPawapay\Records\CreatePaymentPageRecord;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Records\PredictProviderRecord;
use AndyDefer\PhpPawapay\Records\ResendDepositCallbackRecord;
use AndyDefer\PhpPawapay\Services\PawapayService;
use AndyDefer\PhpPawapay\ValueObjects\AccountDetailsVO;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

$apiToken = 'eyJraWQiOiIxIiwiYWxnIjoiRVMyNTYifQ.eyJ0dCI6IkFBVCIsInN1YiI6Ijk4ODgiLCJtYXYiOiIxIiwiZXhwIjoyMDkwNTk2NDg3LCJpYXQiOjE3NzQ5NzcyODcsInBtIjoiREFGLFBBRiIsImp0aSI6ImUxOGNiNzE5LTAyMWUtNDVkYy05MzQwLTRhODg4YWRmY2QwMSJ9.LhD46ze3F_8lxpb9caaLcRTyH5zEpD5xvbCoxneTIq2FALWDu8nWmZPr3YuRNqDz3q4irjHM60ooR3-JTzI8Cg';

$service = PawapayService::create(
    apiToken: $apiToken,
    baseUrl: PawaPayBaseUrl::SANDBOX,
);
$uuid = UuidVO::generate();

echo "=== PawapayService - Exemples ===\n\n";

// ============================================================
// 1. INITIATE DEPOSIT
// ============================================================

echo "1. Initiate deposit - RDC (USD):\n";

$data = $service->initiateDeposit(
    InitiateDepositRecord::from([
        'depositId' => $uuid,
        'payer' => PayerVO::from([
            'type' => PayerType::MMO,
            'accountDetails' => AccountDetailsVO::from([
                'phoneNumber' => PhoneNumberVO::from('243812345678'),
                'provider' => Provider::VODACOM_MPESA_COD,
            ]),
        ]),
        'amount' => AmountVO::from(25.50),
        'currency' => Currency::USD,
        'clientReferenceId' => 'INV-RDC-123456',
        'customerMessage' => CustomerMessageVO::from('Payment order RDC'),
        'metadata' => MetadataVO::from(new StrictDataObject([
            'orderId' => 'ORD-RDC-123456789',
            'customerId' => 'customer@email.com',
        ])),
    ]),
);

if ($data instanceof ErrorResponseData) {
    echo "❌ Erreur bloquée par un hook :\n";
    echo 'Code: '.$data->errorCode."\n";
    echo 'Message: '.$data->message."\n\n";
} elseif ($data->isAccepted) {
    echo "✅ Dépôt accepté !\n";
    echo 'ID: '.$data->depositId?->getValue()."\n";
    echo 'Statut: '.$data->status->value."\n";
    echo 'Créé: '.$data->created?->getValue()."\n\n";
} elseif ($data->isRejected) {
    echo "❌ Dépôt rejeté\n";
    if ($data->failureReason !== null) {
        echo 'Code: '.$data->failureReason->failureCode->value."\n";
        echo 'Message: '.$data->failureReason->failureMessage."\n\n";
    }
} elseif ($data->isDuplicateIgnored) {
    echo "⚠️ Dépôt dupliqué ignoré\n\n";
}

// ============================================================
// 2. CHECK DEPOSIT STATUS
// ============================================================

echo "2. Check deposit status:\n";

$data = $service->checkDepositStatus(
    CheckDepositStatusRecord::from([
        'depositId' => $uuid,
    ]),
);

if ($data instanceof ErrorResponseData) {
    echo "❌ Erreur bloquée par un hook :\n";
    echo 'Code: '.$data->errorCode."\n";
    echo 'Message: '.$data->message."\n\n";
} elseif ($data->isFound) {
    echo "✅ Dépôt trouvé\n";
    echo 'Statut recherche: '.$data->searchStatus->value."\n";

    if ($data->depositData !== null) {
        echo 'Statut dépôt: '.$data->depositData->status->value."\n";
        echo 'Montant: '.$data->depositData->amount->getValue()."\n";

        if ($data->depositData->status->isCompleted()) {
            echo "→ Le paiement est confirmé\n";
        } elseif ($data->depositData->status->isPending()) {
            echo "→ Le paiement est en cours\n";
        }
    }
    echo "\n";
} elseif ($data->isNotFound) {
    echo "❌ Dépôt introuvable\n\n";
}

// ============================================================
// 3. RESEND DEPOSIT CALLBACK
// ============================================================

echo "3. Resend deposit callback:\n";

$data = $service->resendDepositCallback(
    ResendDepositCallbackRecord::from([
        'depositId' => $uuid,
    ]),
);

if ($data instanceof ErrorResponseData) {
    echo "❌ Erreur bloquée par un hook :\n";
    echo 'Code: '.$data->errorCode."\n";
    echo 'Message: '.$data->message."\n\n";
} elseif ($data->isAccepted) {
    echo "✅ Callback renvoyé\n";
    echo 'ID: '.$data->depositId?->getValue()."\n";
    echo 'Statut: '.$data->status?->value."\n\n";
} elseif ($data->isRejected) {
    echo "❌ Callback rejeté\n";
    if ($data->failureReason !== null) {
        echo 'Code: '.$data->failureReason->failureCode->value."\n";
        echo 'Message: '.$data->failureReason->failureMessage."\n\n";
    }
}

// ============================================================
// 4. CREATE PAYMENT PAGE
// ============================================================

echo "4. Create payment page:\n";

$data = $service->createPaymentPage(
    CreatePaymentPageRecord::from([
        'depositId' => $uuid,
        'returnUrl' => UrlVO::from('http://127.0.0.1:8000/payments/pawapay/success'),
        'amountDetails' => AmountDetailsGraph::from([
            'amount' => AmountVO::from(25.50),
            'currency' => Currency::USD,
        ]),
        'metadata' => $metadata = new MetadataVO(
            new StrictDataObject([
                'orderId' => 'ORD-123456789',
                'customerId' => 'customer@email.com',
            ])
        ),
        'phoneNumber' => PhoneNumberVO::from('243827833329'),
        'language' => Language::FR,
        'country' => Country::COD,
        'customerMessage' => CustomerMessageVO::from('Payment order'),
        'reason' => CustomerMessageVO::from('paiement'),
    ]),
);

if ($data instanceof ErrorResponseData) {
    echo "❌ Erreur bloquée par un hook :\n";
    echo 'Code: '.$data->errorCode."\n";
    echo 'Message: '.$data->message."\n\n";
} elseif ($data->redirectUrl !== null) {
    echo "✅ Page de paiement créée\n";
    echo 'URL: '.$data->redirectUrl->getValue()."\n";
    echo "→ Rediriger l'utilisateur vers cette URL\n\n";
} elseif ($data->hasFailureReason && $data->failureReason !== null) {
    echo "❌ Échec création page\n";
    echo 'Code: '.$data->failureReason->failureCode->value."\n";
    echo 'Message: '.$data->failureReason->failureMessage."\n\n";
}

// ============================================================
// 5. PREDICT PROVIDER
// ============================================================

echo "5. Predict provider - Zambie MTN:\n";

$data = $service->predictProvider(
    PredictProviderRecord::from([
        'phoneNumber' => PhoneNumberVO::from('260763456789'),
    ]),
);

if ($data instanceof ErrorResponseData) {
    echo "❌ Erreur bloquée par un hook :\n";
    echo 'Code: '.$data->errorCode."\n";
    echo 'Message: '.$data->message."\n\n";
} elseif ($data->isFound) {
    echo "✅ Provider identifié !\n";
    echo 'Pays: '.$data->country?->value."\n";
    echo 'Provider: '.$data->provider?->value."\n";
    echo 'Numéro normalisé: '.$data->phoneNumber?->getValue()."\n\n";
} else {
    echo "⚠️ Aucun provider trouvé pour ce numéro\n";
    if ($data->failureReason !== null) {
        echo 'Code: '.$data->failureReason->failureCode->value."\n";
        echo 'Message: '.$data->failureReason->failureMessage."\n";
    }
    echo "\n";
}

// ============================================================
// 6. PREDICT PROVIDER - Multi-pays
// ============================================================

echo "6. Predict provider - Multi-pays:\n";

$numbers = [
    '260763456789' => 'Zambie (MTN)',
    '243812345678' => 'RDC (Vodacom)',
    '254712345678' => 'Kenya (M-Pesa)',
    '2348034567890' => 'Nigeria (MTN)',
];

foreach ($numbers as $number => $label) {
    $data = $service->predictProvider(
        PredictProviderRecord::from([
            'phoneNumber' => PhoneNumberVO::from($number),
        ]),
    );

    if ($data instanceof PredictProviderData && $data->isFound) {
        echo sprintf(
            "%-20s → %s (%s)\n",
            $label,
            $data->provider?->value,
            $data->country?->value,
        );
    } else {
        echo sprintf("%-20s → Aucun provider trouvé\n", $label);
    }
}

echo "\n";

// ============================================================
// 7. SERVICE ÉTENDU — avec hooks
// ============================================================

echo "7. Service étendu — avec hooks:\n";

final class LoggingPawapayService extends PawapayService
{
    protected function beforeInitiateDeposit(InitiateDepositRecord $record): ?ErrorResponseData
    {
        echo '  [hook] Avant initiateDeposit : '.$record->depositId->getValue()."\n";

        return null;
    }

    protected function afterInitiateDeposit(InitiateDepositRecord $record, InitiateDepositData $data): ?ErrorResponseData
    {
        echo '  [hook] Après initiateDeposit : statut = '.$data->status->value."\n";

        return null;
    }
}

$loggingService = new LoggingPawapayService(
    new PawapayClient($apiToken, PawaPayBaseUrl::SANDBOX),
);

$loggingService->initiateDeposit(
    InitiateDepositRecord::from([
        'depositId' => $uuid,
        'payer' => PayerVO::from([
            'type' => PayerType::MMO,
            'accountDetails' => AccountDetailsVO::from([
                'phoneNumber' => PhoneNumberVO::from('260763456789'),
                'provider' => Provider::MTN_MOMO_ZMB,
            ]),
        ]),
        'amount' => AmountVO::from(15.00),
        'currency' => Currency::ZMW,
    ]),
);

echo "\n";
