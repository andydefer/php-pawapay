# Initiate Deposit - Référence Technique

## 📖 Description

L'endpoint `POST /v2/deposits` permet d'initier un dépôt (pay-in) via Mobile Money. Il permet à un client de payer un montant en utilisant son compte Mobile Money.

## 🔗 Endpoint

```
POST /v2/deposits
```

## 📋 Structure de la requête

### Headers

| Header | Valeur | Requis |
|--------|--------|--------|
| `Authorization` | `Bearer <token>` | ✅ Oui |
| `Content-Type` | `application/json` | ✅ Oui |
| `Accept` | `application/json` | ✅ Oui |

### Body (JSON)

| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `depositId` | `string` | ✅ Oui | UUID v4 unique pour le dépôt |
| `payer.type` | `string` | ✅ Oui | Type de payeur (ex: `MMO`) |
| `payer.accountDetails.phoneNumber` | `string` | ✅ Oui | Numéro de téléphone du payeur (format E.164) |
| `payer.accountDetails.provider` | `string` | ✅ Oui | Fournisseur Mobile Money (ex: `MTN_MOMO_ZMB`) |
| `amount` | `string` | ✅ Oui | Montant à déposer |
| `currency` | `string` | ✅ Oui | Devise (ex: `ZMW`) |
| `preAuthorisationCode` | `string` | ❌ Non | Code de pré-autorisation |
| `clientReferenceId` | `string` | ❌ Non | Référence client (ex: `INV-123456`) |
| `customerMessage` | `string` | ❌ Non | Message au client (4-22 caractères) |
| `metadata` | `array` | ❌ Non | Métadonnées personnalisées |

### Exemple de requête

```json
{
  "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "payer": {
    "type": "MMO",
    "accountDetails": {
      "phoneNumber": "260763456789",
      "provider": "MTN_MOMO_ZMB"
    }
  },
  "amount": "15",
  "currency": "ZMW",
  "preAuthorisationCode": "code-123",
  "clientReferenceId": "INV-123456",
  "customerMessage": "Payment for order #123456",
  "metadata": [
    {
      "orderId": "ORD-123456789"
    },
    {
      "customerId": "customer@email.com",
      "isPII": true
    }
  ]
}
```

---

## 📊 Structure de la réponse

### Succès - ACCEPTED (200 OK)

```json
{
  "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "status": "ACCEPTED",
  "created": "2020-10-19T11:17:01Z"
}
```

### Succès - DUPLICATE_IGNORED (200 OK)

```json
{
  "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "status": "DUPLICATE_IGNORED",
  "created": "2020-10-19T11:17:01Z"
}
```

### Erreur - REJECTED (200 OK avec failureReason)

```json
{
  "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "status": "REJECTED",
  "failureReason": {
    "failureCode": "INVALID_PHONE_NUMBER",
    "failureMessage": "The phone number '2607634' seems to be invalid for the provider 'MTN_MOMO_ZMB'."
  }
}
```

### Erreur - INVALID_INPUT (400 Bad Request)

```json
{
  "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "status": "REJECTED",
  "failureReason": {
    "failureCode": "INVALID_INPUT",
    "failureMessage": "We are unable to parse the body of the request."
  }
}
```

### Erreur - AUTHENTICATION_ERROR (401 Unauthorized)

```json
{
  "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "status": "REJECTED",
  "failureReason": {
    "failureCode": "AUTHENTICATION_ERROR",
    "failureMessage": "The API token in the request is invalid."
  }
}
```

### Erreur - UNKNOWN_ERROR (500 Internal Server Error)

```json
{
  "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "failureReason": {
    "failureCode": "UNKNOWN_ERROR",
    "failureMessage": "Unable to process request due to an unknown problem."
  }
}
```

---

## 💻 Utilisation avec le SDK

### Installation

```bash
composer require andydefer/php-pawapay
```

### Exemple complet

```php
<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpPawapay\PawapayClient;
use AndyDefer\PhpPawapay\Enums\MMO;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\ReferenceVO;
use AndyDefer\PhpPawapay\ValueObjects\MessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\AccountDetailsVO;
use AndyDefer\DomainStructures\Utils\StrictDataObject;

// 1. Créer le client
$client = new PawapayClient(
    apiToken: 'your-api-token-here',
    baseUrl: PawaPayBaseUrl::SANDBOX
);

// 2. Créer les Value Objects
$depositId = new UuidVO('f4401bd2-1568-4140-bf2d-eb77d2b2b639');
$phoneNumber = new PhoneNumberVO('260763456789');
$amount = new AmountVO(15.00);

$accountDetails = new AccountDetailsVO(
    phoneNumber: $phoneNumber,
    provider: Provider::MTN_MOMO_ZMB
);

$payer = new PayerVO(
    type: MMO::MTN,
    accountDetails: $accountDetails
);

$metadata = new MetadataVO(
    new StrictDataObject([
        'orderId' => 'ORD-123456789',
        'customerId' => 'customer@email.com',
        'isPII' => true
    ])
);

// 3. Créer le dépôt
$deposit = new InitiateDepositVO(
    depositId: $depositId,
    payer: $payer,
    amount: $amount,
    currency: Currency::ZMW,
    preAuthorisationCode: null,
    clientReferenceId: new ReferenceVO('INV-123456'),
    customerMessage: new MessageVO('Payment for order #123456'),
    metadata: $metadata
);

// 4. Initier le dépôt
$response = $client->initiateDeposit($deposit);

// 5. Traiter la réponse
if ($response->isSuccess()) {
    if ($response->isAccepted()) {
        echo "✅ Dépôt accepté !\n";
        echo "ID: " . $response->getDepositId() . "\n";
        echo "Statut: " . $response->getStatus()->value . "\n";
        echo "Créé: " . $response->getCreated() . "\n";
    } elseif ($response->isDuplicateIgnored()) {
        echo "⚠️ Dépôt dupliqué ignoré\n";
        echo "ID: " . $response->getDepositId() . "\n";
    }
} else {
    echo "❌ Échec du dépôt\n";
    echo "Statut: " . $response->getStatus()->value . "\n";
    
    if ($response->hasFailureReason()) {
        $failure = $response->getFailureReason();
        echo "Code d'erreur: " . $failure->failureCode . "\n";
        echo "Message: " . $failure->failureMessage . "\n";
    }
}
```

---

## 🧩 Value Objects disponibles

| Value Object | Description | Validation |
|--------------|-------------|------------|
| `UuidVO` | UUID v4 | Format UUID v4 |
| `PhoneNumberVO` | Numéro de téléphone | Format E.164 |
| `AmountVO` | Montant | Positif, numérique |
| `ReferenceVO` | Référence client | String |
| `MessageVO` | Message client | String |
| `MetadataVO` | Métadonnées | StrictDataObject |
| `PayerVO` | Informations du payeur | Type + AccountDetails |
| `AccountDetailsVO` | Détails du compte | PhoneNumber + Provider |

## 🌍 Enums disponibles

| Enum | Description |
|------|-------------|
| `MMO` | Types de Mobile Money (MTN, Orange, Airtel, etc.) |
| `Provider` | Fournisseurs Mobile Money (MTN_MOMO_ZMB, etc.) |
| `Currency` | Devises supportées (ZMW, KES, etc.) |
| `PawaPayBaseUrl` | URL de base (SANDBOX, PRODUCTION) |
| `DepositStatus` | Statuts de dépôt (ACCEPTED, REJECTED, DUPLICATE_IGNORED) |
| `ContentType` | Types de contenu (JSON, JSON_UTF8, etc.) |

## 🔧 Codes d'erreur

| Code | Description |
|------|-------------|
| `INVALID_PHONE_NUMBER` | Numéro de téléphone invalide |
| `INVALID_CURRENCY` | Devise non supportée |
| `INVALID_AMOUNT` | Montant invalide |
| `AMOUNT_OUT_OF_BOUNDS` | Montant hors limites |
| `PROVIDER_TEMPORARILY_UNAVAILABLE` | Fournisseur indisponible |
| `INVALID_INPUT` | Corps de requête invalide |
| `MISSING_PARAMETER` | Paramètre manquant |
| `INVALID_PARAMETER` | Paramètre invalide |
| `UNSUPPORTED_PARAMETER` | Paramètre non supporté |
| `AUTHENTICATION_ERROR` | Token API invalide |
| `AUTHORISATION_ERROR` | Token non autorisé |
| `UNKNOWN_ERROR` | Erreur inconnue |

## 📝 Notes importantes

1. **Idempotence** : Chaque dépôt doit avoir un `depositId` unique. Les doublons sont ignorés avec le statut `DUPLICATE_IGNORED`.

2. **Format du numéro de téléphone** : Utiliser le format E.164 sans le `+` (ex: `260763456789` pour la Zambie).

3. **Validation des montants** : Le montant doit être valide pour le fournisseur et la devise choisis.

4. **Timeout** : Le SDK utilise un timeout de 30 secondes par défaut.

5. **Rate Limiting** : Respecter les limites de taux de l'API Pawapay.

6. **Content-Type** : Utiliser l'enum `ContentType::JSON` pour les headers.

## 🔗 Voir aussi

- [Documentation officielle Pawapay](https://docs.pawapay.io)
- [API Reference - Deposits](https://docs.pawapay.io/api/deposits)
- [SDK GitHub](https://github.com/andydefer/php-pawapay)