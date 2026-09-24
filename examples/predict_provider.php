<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpPawapay\Builders\PredictProviderBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

// ============================================================
// Créer le client
// ============================================================

$apiToken = 'eyJraWQiOiIxIiwiYWxnIjoiRVMyNTYifQ.eyJ0dCI6IkFBVCIsInN1YiI6Ijk4ODgiLCJtYXYiOiIxIiwiZXhwIjoyMDkwNTk2NDg3LCJpYXQiOjE3NzQ5NzcyODcsInBtIjoiREFGLFBBRiIsImp0aSI6ImUxOGNiNzE5LTAyMWUtNDVkYy05MzQwLTRhODg4YWRmY2QwMSJ9.LhD46ze3F_8lxpb9caaLcRTyH5zEpD5xvbCoxneTIq2FALWDu8nWmZPr3YuRNqDz3q4irjHM60ooR3-JTzI8Cg';

$client = new PawapayClient(
    apiToken: $apiToken,
    baseUrl: PawaPayBaseUrl::SANDBOX,
);

echo "=== Predict Provider - Exemples ===\n\n";

// ============================================================
// EXEMPLE 1 : Avec le Builder (recommandé) - Zambie MTN
// ============================================================

echo "1. Avec le Builder (recommandé) - Zambie MTN:\n";

$response = PredictProviderBuilder::create($apiToken)
    ->withBaseUrl(PawaPayBaseUrl::SANDBOX)
    ->withPhoneNumber('260763456789')
    ->execute();

if ($response->isSuccess()) {
    if ($response->isFound()) {
        echo "✅ Provider identifié !\n";
        echo 'Pays: '.$response->getCountry()->value."\n";
        echo 'Provider: '.$response->getProvider()->value."\n";
        echo 'Numéro normalisé: '.$response->getPhoneNumber()->getValue()."\n\n";
    } else {
        echo "⚠️ Aucun provider trouvé pour ce numéro\n\n";
    }
} else {
    echo "❌ Échec de la prédiction\n";
    echo 'Statut HTTP: '.$response->getStatusCode()->value."\n";

    if ($response->hasFailureReason()) {
        $failure = $response->getFailureReason();
        echo 'Code: '.$failure->failureCode->value."\n";
        echo 'Message: '.$failure->failureMessage."\n\n";
    }
}

// ============================================================
// EXEMPLE 2 : Sans builder (manuel) - RDC Vodacom
// ============================================================

echo "2. Sans builder (manuel) - RDC Vodacom:\n";

$phoneNumber = PhoneNumberVO::from('243812345678');

$response = $client->predictProvider($phoneNumber);

if ($response->isSuccess()) {
    if ($response->isFound()) {
        echo "✅ Provider identifié !\n";
        echo 'Pays: '.$response->getCountry()->value."\n";
        echo 'Provider: '.$response->getProvider()->value."\n";
        echo 'Numéro normalisé: '.$response->getPhoneNumber()->getValue()."\n\n";
    } else {
        echo "⚠️ Aucun provider trouvé pour ce numéro\n\n";
    }
} else {
    echo "❌ Échec de la prédiction\n";
    echo 'Statut HTTP: '.$response->getStatusCode()->value."\n";
}

// ============================================================
// EXEMPLE 3 : Test sur plusieurs pays
// ============================================================

echo "3. Test sur plusieurs pays:\n";

$numbers = [
    '260763456789' => 'Zambie (MTN)',
    '243812345678' => 'RDC (Vodacom)',
    '254712345678' => 'Kenya (M-Pesa)',
    '2348034567890' => 'Nigeria (MTN)',
];

foreach ($numbers as $number => $label) {

    try {
        $response = PredictProviderBuilder::create($apiToken)
            ->withPhoneNumber((string) $number)
            ->execute();

        if ($response->isSuccess() && $response->isFound()) {
            echo sprintf(
                "%-20s → %s (%s)\n",
                $label,
                $response->getProvider()->value,
                $response->getCountry()->value,
            );
        } else {
            echo sprintf("%-20s → Aucun provider trouvé\n", $label);
        }
    } catch (Throwable $e) {
        echo sprintf("%-20s → Erreur: %s\n", $label, $e->getMessage());
    }
}

echo "\n";
