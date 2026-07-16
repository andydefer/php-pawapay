# Get Deposit - Référence Technique

## 📖 Description

L'endpoint `GET /v2/deposits/{depositId}` permet de récupérer le statut et les détails d'un dépôt existant. Il permet de suivre l'évolution d'un dépôt (pending, completed, failed, etc.).

## 🔗 Endpoint

```
GET /v2/deposits/{depositId}
```

### Paramètres d'URL

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `depositId` | `string` | ✅ Oui | UUID du dépôt à récupérer |

---

## 📋 Structure de la requête

### Headers

| Header | Valeur | Requis |
|--------|--------|--------|
| `Authorization` | `Bearer <token>` | ✅ Oui |
| `Accept` | `application/json` | ✅ Oui |

### Exemple de requête

```http
GET /v2/deposits/8917c345-4791-4285-a416-62f24b6982db
Authorization: Bearer your-api-token
Accept: application/json
```

---

## 📊 Structure de la réponse

### Succès - FOUND (200 OK)

#### Statut COMPLETED

```json
{
  "status": "FOUND",
  "data": {
    "depositId": "8917c345-4791-4285-a416-62f24b6982db",
    "status": "COMPLETED",
    "amount": "123.00",
    "currency": "ZMW",
    "country": "ZMB",
    "payer": {
      "type": "MMO",
      "accountDetails": {
        "phoneNumber": "260763456789",
        "provider": "MTN_MOMO_ZMB"
      }
    },
    "customerMessage": "To ACME company",
    "clientReferenceId": "REF-987654321",
    "created": "2020-10-19T08:17:01Z",
    "providerTransactionId": "12356789",
    "metadata": {
      "orderId": "ORD-123456789",
      "customerId": "customer@email.com"
    }
  }
}
```

#### Statut ACCEPTED

```json
{
  "status": "FOUND",
  "data": {
    "depositId": "8917c345-4791-4285-a416-62f24b6982db",
    "status": "ACCEPTED",
    "amount": "123.00",
    "currency": "ZMW",
    "country": "ZMB",
    "payer": {
      "type": "MMO",
      "accountDetails": {
        "phoneNumber": "260763456789",
        "provider": "MTN_MOMO_ZMB"
      }
    },
    "clientReferenceId": "REF-987654321",
    "customerMessage": "To ACME company",
    "created": "2020-10-19T08:17:01Z",
    "metadata": {
      "orderId": "ORD-123456789",
      "customerId": "customer@email.com"
    }
  }
}
```

#### Statut PROCESSING

```json
{
  "status": "FOUND",
  "data": {
    "depositId": "8917c345-4791-4285-a416-62f24b6982db",
    "status": "PROCESSING",
    "amount": "123.00",
    "currency": "ZMW",
    "country": "ZMB",
    "payer": {
      "type": "MMO",
      "accountDetails": {
        "phoneNumber": "260763456789",
        "provider": "MTN_MOMO_ZMB"
      }
    },
    "clientReferenceId": "REF-987654321",
    "customerMessage": "To ACME company",
    "created": "2020-10-19T08:17:01Z",
    "metadata": {
      "orderId": "ORD-123456789",
      "customerId": "customer@email.com"
    }
  }
}
```

#### Statut IN_RECONCILIATION

```json
{
  "status": "FOUND",
  "data": {
    "depositId": "8917c345-4791-4285-a416-62f24b6982db",
    "status": "IN_RECONCILIATION",
    "amount": "123.00",
    "currency": "ZMW",
    "country": "ZMB",
    "payer": {
      "type": "MMO",
      "accountDetails": {
        "phoneNumber": "260763456789",
        "provider": "MTN_MOMO_ZMB"
      }
    },
    "clientReferenceId": "REF-987654321",
    "customerMessage": "To ACME company",
    "created": "2020-10-19T08:17:01Z",
    "metadata": {
      "orderId": "ORD-123456789",
      "customerId": "customer@email.com"
    }
  }
}
```

#### Statut FAILED avec raison d'échec

```json
{
  "status": "FOUND",
  "data": {
    "depositId": "8917c345-4791-4285-a416-62f24b6982db",
    "status": "FAILED",
    "amount": "123.00",
    "currency": "ZMW",
    "country": "ZMB",
    "payer": {
      "type": "MMO",
      "accountDetails": {
        "phoneNumber": "260763456789",
        "provider": "MTN_MOMO_ZMB"
      }
    },
    "clientReferenceId": "REF-987654321",
    "customerMessage": "To ACME company",
    "created": "2020-10-19T08:17:01Z",
    "failureReason": {
      "failureCode": "PAYMENT_NOT_APPROVED",
      "failureMessage": "The customer did not approve the authorisation for this payment"
    },
    "metadata": {
      "orderId": "ORD-123456789",
      "customerId": "customer@email.com"
    }
  }
}
```

### Statut NOT_FOUND (200 OK)

```json
{
  "status": "NOT_FOUND"
}
```

### Erreur - AUTHENTICATION_ERROR (401 Unauthorized)

```json
{
  "status": "REJECTED",
  "failureReason": {
    "failureCode": "AUTHENTICATION_ERROR",
    "failureMessage": "The API token in the request is invalid."
  }
}
```

### Erreur - AUTHORISATION_ERROR (403 Forbidden)

```json
{
  "status": "REJECTED",
  "failureReason": {
    "failureCode": "AUTHORISATION_ERROR",
    "failureMessage": "The API token in the request is not authorised for this endpoint."
  }
}
```

### Erreur - UNKNOWN_ERROR (500 Internal Server Error)

```json
{
  "failureReason": {
    "failureCode": "UNKNOWN_ERROR",
    "failureMessage": "Unable to process request due to an unknown problem."
  }
}
```

---

## 📋 Champs de la réponse

| Champ | Type | Description |
|-------|------|-------------|
| `status` | `string` | Statut de recherche (`FOUND`, `NOT_FOUND`, `REJECTED`) |
| `data.depositId` | `string` | ID unique du dépôt |
| `data.status` | `string` | Statut du dépôt (`ACCEPTED`, `PROCESSING`, `IN_RECONCILIATION`, `COMPLETED`, `FAILED`, `REJECTED`) |
| `data.amount` | `string` | Montant du dépôt |
| `data.currency` | `string` | Devise du dépôt |
| `data.country` | `string` | Code pays (ISO 3166-1 alpha-3) |
| `data.payer.type` | `string` | Type de payeur (`MMO`) |
| `data.payer.accountDetails.phoneNumber` | `string` | Numéro de téléphone du payeur |
| `data.payer.accountDetails.provider` | `string` | Fournisseur Mobile Money |
| `data.clientReferenceId` | `string` | Référence client (optionnel) |
| `data.customerMessage` | `string` | Message au client (optionnel) |
| `data.created` | `string` | Date de création (ISO 8601) |
| `data.providerTransactionId` | `string` | ID transaction du fournisseur (optionnel) |
| `data.failureReason.failureCode` | `string` | Code d'erreur (optionnel) |
| `data.failureReason.failureMessage` | `string` | Message d'erreur (optionnel) |
| `data.metadata` | `object` | Métadonnées personnalisées (optionnel) |

---

## 💻 Utilisation avec le SDK

### Exemple complet

```php
<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpPawapay\PawapayClient;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpClient\Enums\ContentType;

// 1. Créer le client
$client = new PawapayClient(
    apiToken: 'your-api-token-here',
    baseUrl: PawaPayBaseUrl::SANDBOX
);

// 2. Récupérer le dépôt
$depositId = '8917c345-4791-4285-a416-62f24b6982db';
$response = $client->getDeposit($depositId);

// 3. Traiter la réponse
if ($response->isFound()) {
    $data = $response->getDepositData();
    if ($data !== null) {
        echo "✅ Dépôt trouvé !\n";
        echo "ID: " . $data->depositId . "\n";
        echo "Statut: " . $data->status->value . "\n";
        echo "Montant: " . $data->amount . " " . $data->currency->value . "\n";
        echo "Pays: " . $data->country->getName() . "\n";
        echo "Payeur: " . $data->payer->accountDetails->phoneNumber . "\n";
        echo "Provider: " . $data->payer->accountDetails->provider->value . "\n";
        echo "Créé: " . $data->created . "\n";
        
        if ($data->providerTransactionId !== null) {
            echo "ID Transaction Provider: " . $data->providerTransactionId . "\n";
        }
        
        if ($data->clientReferenceId !== null) {
            echo "Référence client: " . $data->clientReferenceId . "\n";
        }
        
        if ($data->customerMessage !== null) {
            echo "Message client: " . $data->customerMessage . "\n";
        }
        
        if ($data->metadata !== null) {
            echo "Métadonnées:\n";
            foreach ($data->metadata as $key => $value) {
                echo "  - " . $key . ": " . (is_array($value) ? json_encode($value) : $value) . "\n";
            }
        }
        
        if ($data->failureReason !== null) {
            echo "❌ Échec:\n";
            echo "Code: " . $data->failureReason->failureCode . "\n";
            echo "Message: " . $data->failureReason->failureMessage . "\n";
        }
    }
} else {
    echo "❌ Dépôt non trouvé\n";
}

// 4. Si erreur d'authentification ou autre
if ($response->hasFailureReason()) {
    $failure = $response->getFailureReason();
    echo "❌ Erreur: " . $failure->failureCode . "\n";
    echo "Message: " . $failure->failureMessage . "\n";
}
```

### Exemple avec traitement des statuts

```php
<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpPawapay\PawapayClient;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Enums\DepositStatus;

$client = new PawapayClient(
    apiToken: 'your-api-token-here',
    baseUrl: PawaPayBaseUrl::SANDBOX
);

$depositId = '8917c345-4791-4285-a416-62f24b6982db';
$response = $client->getDeposit($depositId);

if ($response->isFound()) {
    $data = $response->getDepositData();
    if ($data !== null) {
        $status = $data->status;
        
        if ($status->isCompleted()) {
            echo "✅ Dépôt terminé avec succès !\n";
        } elseif ($status->isPending()) {
            echo "⏳ Dépôt en cours de traitement...\n";
            echo "Statut: " . $status->value . "\n";
        } elseif ($status->isFailed() || $status->isRejected()) {
            echo "❌ Dépôt échoué\n";
            if ($data->failureReason !== null) {
                echo "Code: " . $data->failureReason->failureCode . "\n";
                echo "Message: " . $data->failureReason->failureMessage . "\n";
            }
        } else {
            echo "Statut: " . $status->value . "\n";
        }
    }
} elseif ($response->isNotFound()) {
    echo "❌ Dépôt non trouvé\n";
}

if ($response->hasFailureReason()) {
    $failure = $response->getFailureReason();
    echo "❌ Erreur API: " . $failure->failureCode . "\n";
    echo "Message: " . $failure->failureMessage . "\n";
}
```

### Exemple avec cURL

```bash
curl -X GET "https://api.sandbox.pawapay.io/v2/deposits/8917c345-4791-4285-a416-62f24b6982db" \
  -H "Authorization: Bearer your-api-token" \
  -H "Accept: application/json"
```

---

## 🧩 Statuts des dépôts

| Statut | Description | Final |
|--------|-------------|-------|
| `ACCEPTED` | Dépôt accepté, en attente de traitement | ❌ |
| `PROCESSING` | Dépôt en cours de traitement | ❌ |
| `IN_RECONCILIATION` | Dépôt en réconciliation | ❌ |
| `COMPLETED` | Dépôt terminé avec succès | ✅ |
| `FAILED` | Dépôt échoué | ✅ |
| `REJECTED` | Dépôt rejeté | ✅ |
| `DUPLICATE_IGNORED` | Dépôt dupliqué ignoré | ✅ |

### Méthodes utilitaires

```php
$status = $data->status;

$status->isCompleted();      // COMPLETED
$status->isFailed();         // FAILED
$status->isRejected();       // REJECTED
$status->isProcessing();     // PROCESSING
$status->isInReconciliation(); // IN_RECONCILIATION
$status->isAccepted();       // ACCEPTED
$status->isPending();        // ACCEPTED, PROCESSING, IN_RECONCILIATION
$status->isFinal();          // COMPLETED, FAILED, REJECTED
```

---

## 🔧 Codes d'erreur

| Code | Description |
|------|-------------|
| `AUTHENTICATION_ERROR` | Token API invalide |
| `AUTHORISATION_ERROR` | Token non autorisé pour cet endpoint |
| `UNKNOWN_ERROR` | Erreur inconnue du serveur |
| `PAYMENT_NOT_APPROVED` | Le client n'a pas approuvé le paiement |

---

## 📝 Notes importantes

1. **Polling** : Utilisez cette endpoint pour suivre l'état d'un dépôt après initiation. Les statuts évoluent de `ACCEPTED` → `PROCESSING` → `IN_RECONCILIATION` → `COMPLETED` ou `FAILED`.

2. **Idempotence** : Cette endpoint est idempotente. Vous pouvez l'appeler plusieurs fois avec le même `depositId`.

3. **Gestion des erreurs** : Les erreurs de l'API peuvent être récupérées via `failureReason` dans la réponse.

4. **Timeout** : Le SDK utilise un timeout de 30 secondes par défaut.

5. **ProviderTransactionId** : Présent uniquement lorsque le fournisseur a confirmé la transaction.

## 🔗 Voir aussi

- [Initiate Deposit - Référence Technique](./initiate-deposit.md)
- [Documentation officielle Pawapay](https://docs.pawapay.io)
- [API Reference - Deposits](https://docs.pawapay.io/api/deposits)
- [SDK GitHub](https://github.com/andydefer/php-pawapay)