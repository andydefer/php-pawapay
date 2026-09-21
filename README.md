# PHP Pawapay SDK

**SDK PHP pour l'intégration des paiements Mobile Money Pawapay à travers les marchés africains.**

---

## 📖 Introduction

### Qu'est-ce que Pawapay ?

Pawapay est une plateforme de paiement qui permet d'accepter des paiements via Mobile Money (M-Pesa, MTN MoMo, Orange Money, etc.) dans plus de 15 pays africains.

### Ce que fait ce SDK

Ce SDK transforme les appels HTTP bruts vers l'API Pawapay en **objets PHP typés, validés et documentés**. Il expose deux niveaux d'utilisation :

- **`PawapayClient`** — client HTTP bas niveau, expose les `Response` typées du SDK.
- **`PawapayService`** — façade applicative haut niveau, prend des `Record` et retourne des `Data`. C'est ce que vous utilisez dans votre code métier.

### Pourquoi l'utiliser ?

| Sans SDK | Avec SDK |
|----------|----------|
| `json_decode()` manuel | Objets PHP typés (`DepositDataStruct`, `InitiateDepositData`) |
| Validation manuelle | Value Objects (`PhoneNumberVO`, `UuidVO`, `AmountVO`) |
| Strings magiques | Enums (`Provider::MTN_MOMO_ZMB`, `Currency::ZMW`) |
| Code répétitif | Builders fluides + façade `PawapayService` |
| Mélange HTTP/métier | Séparation `Record` (entrée) / `Data` (sortie) |

---

## 🚀 Installation

```bash
composer require andydefer/php-pawapay
```

---

## ⚙️ Configuration

### Via la façade `PawapayService` (recommandé)

```php
<?php

use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Services\PawapayService;

$service = PawapayService::create(
    apiToken: 'eyJraWQiOiIxIiwiYWxnIjoiRVMyNTYifQ...',
    baseUrl: PawaPayBaseUrl::SANDBOX, // ou PRODUCTION
);
```

### Via le client bas niveau

```php
<?php

use AndyDefer\PhpPawapay\PawapayClient;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

$client = new PawapayClient(
    apiToken: 'eyJraWQiOiIxIiwiYWxnIjoiRVMyNTYifQ...',
    baseUrl: PawaPayBaseUrl::SANDBOX,
);
```

---

## 🎯 `PawapayService` — façade haut niveau

`PawapayService` implémente `PawapayInterface`. C'est la couche que vous utilisez dans votre métier : vous lui passez un `Record`, il vous renvoie une `Data`.

### API

| Méthode | Entrée | Sortie |
|---------|--------|--------|
| `initiateDeposit` | `InitiateDepositRecord` | `InitiateDepositData` |
| `checkDepositStatus` | `CheckDepositStatusRecord` | `CheckDepositStatusData` |
| `resendDepositCallback` | `ResendDepositCallbackRecord` | `ResendDepositCallbackData` |
| `createPaymentPage` | `CreatePaymentPageRecord` | `CreatePaymentPageData` |

### Initier un dépôt via le service

```php
<?php

use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\PayerType;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\ValueObjects\AccountDetailsVO;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

$data = $service->initiateDeposit(
    InitiateDepositRecord::from([
        'depositId' => UuidVO::from('f4401bd2-1568-4140-bf2d-eb77d2b2b639'),
        'payer' => PayerVO::from([
            'type' => PayerType::MMO,
            'accountDetails' => AccountDetailsVO::from([
                'phoneNumber' => PhoneNumberVO::from('260763456789'),
                'provider' => Provider::MTN_MOMO_ZMB,
            ]),
        ]),
        'amount' => AmountVO::from(15.00),
        'currency' => Currency::ZMW,
        'clientReferenceId' => 'INV-123456',
        'customerMessage' => CustomerMessageVO::from('Payment order 123'),
    ]),
);

if ($data->isAccepted) {
    // Stocker $data->depositId en base
}
```

Le service expose les mêmes informations que le client bas niveau, mais sous forme de `Data` immuable :

```php
$data->depositId;             // ?string
$data->status;                // DepositStatus
$data->created;               // ?string
$data->failureReason;         // ?FailureReasonData
$data->isAccepted;            // bool
$data->isRejected;            // bool
$data->isDuplicateIgnored;    // bool
$data->hasFailureReason;      // bool
```

### Vérifier un dépôt

```php
use AndyDefer\PhpPawapay\Records\CheckDepositStatusRecord;

$data = $service->checkDepositStatus(
    CheckDepositStatusRecord::from([
        'depositId' => UuidVO::from('60bd6a3d-177e-4ec2-a65c-d622ede29c99'),
    ]),
);

if ($data->isFound) {
    $status = $data->depositData?->status;
    if ($status?->isCompleted()) {
        // Paiement confirmé
    }
}
```

### Renvoyer un callback

```php
use AndyDefer\PhpPawapay\Records\ResendDepositCallbackRecord;

$data = $service->resendDepositCallback(
    ResendDepositCallbackRecord::from([
        'depositId' => UuidVO::from('9b724dbf-32a7-4e63-96bb-59a4747e43ca'),
    ]),
);

if ($data->isAccepted) {
    // Callback renvoyé
}
```

### Créer une page de paiement

```php
use AndyDefer\PhpPawapay\Records\CreatePaymentPageRecord;

$data = $service->createPaymentPage(
    CreatePaymentPageRecord::from([
        // ...
    ]),
);

if ($data->redirectUrl !== null) {
    header('Location: ' . $data->redirectUrl);
    exit;
}
```

### Intégration Laravel

Bindez l'interface à l'implémentation dans un ServiceProvider :

```php
$this->app->singleton(PawapayInterface::class, function () {
    return PawapayService::create(
        apiToken: config('pawapay.api_token'),
        baseUrl: config('pawapay.environment') === 'production'
            ? PawaPayBaseUrl::PRODUCTION
            : PawaPayBaseUrl::SANDBOX,
    );
});
```

Puis injectez `PawapayInterface` dans vos contrôleurs, jobs, listeners.

---

## 💰 1. Initier un dépôt (bas niveau)

### Ce que fait cette route

Elle permet à un client de payer avec son Mobile Money. Vous envoyez le numéro de téléphone, le montant et le fournisseur, et Pawapay initie le paiement.

### Endpoint

```
POST /v2/deposits
```

### Étape 1 : Créer les Value Objects

```php
<?php

use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\ReferenceVO;
use AndyDefer\PhpPawapay\ValueObjects\MessageVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\AccountDetailsVO;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\Enums\PayerType;

// 1. Identifiant unique du dépôt (UUID v4)
$depositId = new UuidVO('f4401bd2-1568-4140-bf2d-eb77d2b2b639');

// 2. Numéro de téléphone du client (format international sans le +)
$phoneNumber = new PhoneNumberVO('260763456789'); // Zambie

// 3. Montant
$amount = new AmountVO(15.00);

// 4. Détails du compte Mobile Money
$accountDetails = new AccountDetailsVO(
    phoneNumber: $phoneNumber,
    provider: Provider::MTN_MOMO_ZMB
);

// 5. Payeur
$payer = new PayerVO(
    type: PayerType::MMO,
    accountDetails: $accountDetails
);

// 6. Créer le dépôt
$deposit = new InitiateDepositVO(
    depositId: $depositId,
    payer: $payer,
    amount: $amount,
    currency: Currency::ZMW,
    clientReferenceId: new ReferenceVO('INV-123456'),
    customerMessage: new MessageVO('Payment for order #123456')
);
```

### Étape 2 : Envoyer la requête

```php
$response = $client->initiateDeposit($deposit);
```

### Étape 3 : Traiter la réponse

```php
if ($response->isSuccess()) {
    if ($response->isAccepted()) {
        echo "✅ Dépôt accepté !\n";
        echo "ID: " . $response->getDepositId() . "\n";
        echo "Statut: " . $response->getStatus()->value;
        echo "Créé: " . $response->getCreated();
    } elseif ($response->isDuplicateIgnored()) {
        echo "⚠️ Dépôt dupliqué ignoré\n";
    }
} else {
    echo "❌ Échec du dépôt\n";
    echo "Statut: " . $response->getStatus()->value;

    if ($response->hasFailureReason()) {
        $failure = $response->getFailureReason();
        echo "Code: " . $failure->failureCode;
        echo "Message: " . $failure->failureMessage;
    }
}
```

### Avec le Builder (bas niveau, recommandé)

```php
$deposit = InitiateDepositBuilder::create()
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('260763456789')
    ->withProvider(Provider::MTN_MOMO_ZMB)
    ->withAmount(15.00)
    ->withCurrency(Currency::ZMW)
    ->withClientReferenceId('INV-123456')
    ->withCustomerMessage('Payment for order #123456')
    ->withMetadataKey('orderId', 'ORD-123456789')
    ->build();
```

---

## 🔍 2. Vérifier le statut d'un dépôt

### Endpoint

```
GET /v2/deposits/{depositId}
```

### Utilisation

```php
use AndyDefer\PhpPawapay\Builders\CheckDepositStatusBuilder;

$response = CheckDepositStatusBuilder::create('your-api-token')
    ->withDepositId('60bd6a3d-177e-4ec2-a65c-d622ede29c99')
    ->execute();

if ($response->isFound()) {
    $data = $response->getDepositData();

    echo "Statut: " . $data->status->value;

    if ($data->status->isCompleted()) {
        echo "✅ Le paiement a été confirmé !";
    } elseif ($data->status->isPending()) {
        echo "⏳ En attente de confirmation...";
    } elseif ($data->status->isFailed()) {
        echo "❌ Le paiement a échoué.";
        if ($data->failureReason !== null) {
            echo "Raison: " . $data->failureReason->failureMessage;
        }
    }
} else {
    echo "❌ Dépôt non trouvé";
}
```

### Les statuts possibles

| Statut | Signification |
|--------|---------------|
| `ACCEPTED` | Dépôt accepté, en attente |
| `PROCESSING` | En cours de traitement |
| `IN_RECONCILIATION` | En réconciliation |
| `COMPLETED` | ✅ Terminé avec succès |
| `FAILED` | ❌ Échoué |
| `REJECTED` | ❌ Rejeté |

---

## 🔄 3. Renvoyer un callback de dépôt

### Endpoint

```
POST /v2/deposits/resend-callback/{depositId}
```

### Utilisation

```php
use AndyDefer\PhpPawapay\Builders\ResendDepositCallbackBuilder;

$response = ResendDepositCallbackBuilder::create('your-api-token')
    ->withDepositId('9b724dbf-32a7-4e63-96bb-59a4747e43ca')
    ->execute();

if ($response->isAccepted()) {
    echo "✅ Callback renvoyé avec succès !";
} else {
    echo "❌ Échec";
    $failure = $response->getFailureReason();
    echo "Code: " . $failure->failureCode;
    echo "Message: " . $failure->failureMessage;
}
```

---

## 📄 4. Créer une page de paiement

### Endpoint

```
POST /v2/paymentpage
```

### Utilisation

```php
use AndyDefer\PhpPawapay\Builders\CreatePaymentPageBuilder;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Language;

$response = CreatePaymentPageBuilder::create('your-api-token')
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('243827833329')
    ->withAmount(24.60)
    ->withCurrency(Currency::USD)
    ->withReturnUrl('https://example.com/success')
    ->withLanguage(Language::FR)
    ->withCountry(Country::COD)
    ->withCustomerMessage('Paiement commande')
    ->withReason('Commande #12345')
    ->execute();

if ($response->isSuccess()) {
    $redirectUrl = $response->getRedirectUrl();
    header('Location: ' . $redirectUrl);
    exit;
}
```

---

## 🧩 Les Value Objects

Les Value Objects valident les données à leur création.

| VO | Validation | Exemple |
|----|------------|---------|
| `UuidVO` | Format UUID v4 | `new UuidVO('f4401bd2-...')` |
| `PhoneNumberVO` | Format E.164 sans `+` | `new PhoneNumberVO('260763456789')` |
| `AmountVO` | Positif, 2 décimales max | `new AmountVO(15.00)` |
| `ReferenceVO` | 4 à 64 caractères, `a-z A-Z 0-9 -` | `new ReferenceVO('INV-123456')` |
| `CustomerMessageVO` | 4 à 22 caractères après normalisation | `new CustomerMessageVO('Payment order')` |
| `MetadataVO` | 10 champs max, valeurs scalaires | `new MetadataVO($data)` |

---

## 🌍 Enums disponibles

| Enum | Valeurs | Exemple |
|------|---------|---------|
| `Provider` | Fournisseurs Mobile Money | `Provider::MTN_MOMO_ZMB` |
| `Currency` | Devises | `Currency::ZMW`, `Currency::USD` |
| `Country` | Pays | `Country::ZMB`, `Country::COD` |
| `Language` | Langues | `Language::EN`, `Language::FR` |
| `DepositStatus` | Statuts de dépôt | `DepositStatus::COMPLETED` |
| `DepositSearchStatus` | Résultat de recherche | `DepositSearchStatus::FOUND` |
| `ResendCallbackStatus` | Résultat de renvoi | `ResendCallbackStatus::ACCEPTED` |
| `FailureCode` | Codes d'erreur | `FailureCode::INVALID_PHONE_NUMBER` |
| `PawaPayBaseUrl` | URL de base | `PawaPayBaseUrl::SANDBOX` |

---

## ❌ Gestion des erreurs

### Depuis le service (via `Data`)

Aucune exception métier n'est levée : tout se traduit par `failureReason` non-null dans la `Data` de retour.

```php
$data = $service->initiateDeposit($record);

if ($data->hasFailureReason) {
    match ($data->failureReason->failureCode) {
        FailureCode::INVALID_PHONE_NUMBER => /* corriger le numéro */,
        FailureCode::AUTHENTICATION_ERROR => /* vérifier le token */,
        FailureCode::PROVIDER_TEMPORARILY_UNAVAILABLE => /* réessayer plus tard */,
        default => /* log */,
    };
}
```

### Depuis le client (via `Response`)

```php
try {
    $response = $client->initiateDeposit($deposit);

    if ($response->isSuccess()) {
        return;
    }

    if ($response->hasFailureReason()) {
        $failure = $response->getFailureReason();

        switch ($failure->failureCode) {
            case FailureCode::INVALID_PHONE_NUMBER:
                // Demander à l'utilisateur de corriger son numéro
                break;

            case FailureCode::AUTHENTICATION_ERROR:
                // Vérifier le token API
                break;

            case FailureCode::PROVIDER_TEMPORARILY_UNAVAILABLE:
                // Réessayer plus tard
                break;

            default:
                // Erreur inconnue
                break;
        }
    }

} catch (Exception $e) {
    Log::error('Pawapay error: ' . $e->getMessage());
}
```

---

## 📝 Exemples concrets par pays

### Zambie — MTN MoMo

```php
$deposit = InitiateDepositBuilder::create()
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('260763456789')
    ->withProvider(Provider::MTN_MOMO_ZMB)
    ->withAmount(15.00)
    ->withCurrency(Currency::ZMW)
    ->build();
```

### RDC — Vodacom MPesa (USD)

```php
$deposit = InitiateDepositBuilder::create()
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('243812345678')
    ->withProvider(Provider::VODACOM_MPESA_COD)
    ->withAmount(25.50)
    ->withCurrency(Currency::USD)
    ->build();
```

### Kenya — M-Pesa

```php
$deposit = InitiateDepositBuilder::create()
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('254712345678')
    ->withProvider(Provider::MPESA_KEN)
    ->withAmount(100.00)
    ->withCurrency(Currency::KES)
    ->build();
```

### Nigeria — MTN MoMo

```php
$deposit = InitiateDepositBuilder::create()
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('2348034567890')
    ->withProvider(Provider::MTN_MOMO_NGA)
    ->withAmount(5000.00)
    ->withCurrency(Currency::NGN)
    ->build();
```

---

## 📄 Licence

MIT © Andy Defer