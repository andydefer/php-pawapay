<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpPawapay\Builders\ResendDepositCallbackBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;

// ============================================================
// CAS 1 : Avec le Builder (recommandé)
// ============================================================
echo "=== CAS 1 : Avec le Builder ===\n";

$response = ResendDepositCallbackBuilder::create(
    apiToken: 'eyJraWQiOiIxIiwiYWxnIjoiRVMyNTYifQ.eyJ0dCI6IkFBVCIsInN1YiI6Ijk4ODgiLCJtYXYiOiIxIiwiZXhwIjoyMDkwNTk2NDg3LCJpYXQiOjE3NzQ5NzcyODcsInBtIjoiREFGLFBBRiIsImp0aSI6ImUxOGNiNzE5LTAyMWUtNDVkYy05MzQwLTRhODg4YWRmY2QwMSJ9.LhD46ze3F_8lxpb9caaLcRTyH5zEpD5xvbCoxneTIq2FALWDu8nWmZPr3YuRNqDz3q4irjHM60ooR3-JTzI8Cg'
)
    ->withBaseUrl(PawaPayBaseUrl::SANDBOX)
    ->withDepositId('9b724dbf-32a7-4e63-96bb-59a4747e43ca')
    ->execute();

if ($response->isAccepted()) {
    echo "✅ Callback renvoyé avec succès !\n";
    echo 'Deposit ID: '.$response->getDepositId()."\n";
    echo 'Statut: '.$response->getStatus()->value."\n";
} elseif ($response->isRejected()) {
    echo "❌ Échec du renvoi du callback\n";
    echo 'Deposit ID: '.$response->getDepositId()."\n";
    echo 'Statut: '.$response->getStatus()->value."\n";

    if ($response->hasFailureReason()) {
        $failure = $response->getFailureReason();
        echo "Code d'erreur: ".$failure->failureCode."\n";
        echo 'Message: '.$failure->failureMessage."\n";
    }
}

if ($response->hasFailureReason()) {
    $failure = $response->getFailureReason();
    echo '❌ Erreur API: '.$failure->failureCode."\n";
    echo 'Message: '.$failure->failureMessage."\n";
}

echo "\n";

// ============================================================
// CAS 2 : Avec le client manuel (sans builder)
// ============================================================
echo "=== CAS 2 : Avec le client manuel ===\n";

$client = new PawapayClient(
    apiToken: 'eyJraWQiOiIxIiwiYWxnIjoiRVMyNTYifQ.eyJ0dCI6IkFBVCIsInN1YiI6Ijk4ODgiLCJtYXYiOiIxIiwiZXhwIjoyMDkwNTk2NDg3LCJpYXQiOjE3NzQ5NzcyODcsInBtIjoiREFGLFBBRiIsImp0aSI6ImUxOGNiNzE5LTAyMWUtNDVkYy05MzQwLTRhODg4YWRmY2QwMSJ9.LhD46ze3F_8lxpb9caaLcRTyH5zEpD5xvbCoxneTIq2FALWDu8nWmZPr3YuRNqDz3q4irjHM60ooR3-JTzI8Cg',
    baseUrl: PawaPayBaseUrl::SANDBOX
);

$depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';
$response = $client->resendDepositCallback($depositId);

if ($response->isAccepted()) {
    echo "✅ Callback renvoyé avec succès !\n";
    echo 'Deposit ID: '.$response->getDepositId()."\n";
} else {
    echo "❌ Échec\n";
}
