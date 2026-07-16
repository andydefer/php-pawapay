<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpPawapay\Builders\CheckDepositStatusBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;

// ============================================================
// CAS 1 : Avec le Builder (recommandé)
// ============================================================
echo "=== CAS 1 : Avec le Builder ===\n";

$response = CheckDepositStatusBuilder::create(
    apiToken: 'eyJraWQiOiIxIiwiYWxnIjoiRVMyNTYifQ.eyJ0dCI6IkFBVCIsInN1YiI6Ijk4ODgiLCJtYXYiOiIxIiwiZXhwIjoyMDkwNTk2NDg3LCJpYXQiOjE3NzQ5NzcyODcsInBtIjoiREFGLFBBRiIsImp0aSI6ImUxOGNiNzE5LTAyMWUtNDVkYy05MzQwLTRhODg4YWRmY2QwMSJ9.LhD46ze3F_8lxpb9caaLcRTyH5zEpD5xvbCoxneTIq2FALWDu8nWmZPr3YuRNqDz3q4irjHM60ooR3-JTzI8Cg'
)
    ->withBaseUrl(PawaPayBaseUrl::SANDBOX)
    ->withDepositId('60bd6a3d-177e-4ec2-a65c-d622ede29c99')
    ->execute();

if ($response->isFound()) {
    $data = $response->getDepositData();
    if ($data !== null) {
        echo "✅ Dépôt trouvé !\n";
        echo 'ID: '.$data->depositId."\n";
        echo 'Statut: '.$data->status->value."\n";
        echo 'Montant: '.$data->amount.' '.$data->currency->value."\n";
        echo 'Pays: '.$data->country->getName()."\n";
        echo 'Payeur: '.$data->payer->accountDetails->phoneNumber."\n";
        echo 'Provider: '.$data->payer->accountDetails->provider->value."\n";
        echo 'Créé: '.$data->created."\n";

        if ($data->providerTransactionId !== null) {
            echo 'ID Transaction Provider: '.$data->providerTransactionId."\n";
        }

        if ($data->clientReferenceId !== null) {
            echo 'Référence client: '.$data->clientReferenceId."\n";
        }

        if ($data->customerMessage !== null) {
            echo 'Message client: '.$data->customerMessage."\n";
        }

        if ($data->metadata !== null) {
            echo "Métadonnées:\n";
            foreach ($data->metadata as $key => $value) {
                echo '  - '.$key.': '.(is_array($value) ? json_encode($value) : $value)."\n";
            }
        }

        if ($data->failureReason !== null) {
            echo "❌ Échec:\n";
            echo 'Code: '.$data->failureReason->failureCode."\n";
            echo 'Message: '.$data->failureReason->failureMessage."\n";
        }
    }
} else {
    echo "❌ Dépôt non trouvé\n";
}

if ($response->hasFailureReason()) {
    $failure = $response->getFailureReason();
    echo '❌ Erreur: '.$failure->failureCode."\n";
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

$depositId = '60bd6a3d-177e-4ec2-a65c-d622ede29c99';
$response = $client->checkDepositStatus($depositId);

if ($response->isFound()) {
    $data = $response->getDepositData();
    if ($data !== null) {
        echo "✅ Dépôt trouvé !\n";
        echo 'ID: '.$data->depositId."\n";
        echo 'Statut: '.$data->status->value."\n";
        echo 'Montant: '.$data->amount.' '.$data->currency->value."\n";
    }
} else {
    echo "❌ Dépôt non trouvé\n";
}
