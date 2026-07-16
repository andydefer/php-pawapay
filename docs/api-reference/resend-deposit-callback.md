# Resend Deposit Callback - Référence Technique

## 📖 Description

L'endpoint `POST /v2/deposits/resend-callback/{depositId}` permet de renvoyer le callback webhook pour un dépôt existant. Utile en cas de non-réception du callback par le serveur du marchand.

## 🔗 Endpoint

```
POST /v2/deposits/resend-callback/{depositId}
```

### Paramètres d'URL

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `depositId` | `string` | ✅ Oui | UUID du dépôt dont le callback doit être renvoyé |

---

## 📋 Structure de la requête

### Headers

| Header | Valeur | Requis |
|--------|--------|--------|
| `Authorization` | `Bearer <token>` | ✅ Oui |
| `Content-Type` | `application/json` | ✅ Oui |
| `Accept` | `application/json` | ✅ Oui |

### Exemple de requête

```http
POST /v2/deposits/resend-callback/9b724dbf-32a7-4e63-96bb-59a4747e43ca
Authorization: Bearer your-api-token
Accept: application/json
```

---

## 📊 Structure de la réponse

### Succès - ACCEPTED (200 OK)

```json
{
  "depositId": "9b724dbf-32a7-4e63-96bb-59a4747e43ca",
  "status": "ACCEPTED"
}
```

### Erreur - NOT_FOUND (200 OK avec failureReason)

```json
{
  "depositId": "9b724dbf-32a7-4e63-96bb-59a4747e43ca",
  "status": "REJECTED",
  "failureReason": {
    "failureCode": "NOT_FOUND",
    "failureMessage": "Payout with ID 9b724dbf-32a7-4e63-96bb-59a4747e43ca not found"
  }
}
```

### Erreur - INVALID_STATE (200 OK avec failureReason)

```json
{
  "depositId": "9b724dbf-32a7-4e63-96bb-59a4747e43ca",
  "status": "REJECTED",
  "failureReason": {
    "failureCode": "INVALID_STATE",
    "failureMessage": "Payout with ID 9b724dbf-32a7-4e63-96bb-59a4747e43ca has not finished processing"
  }
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
| `depositId` | `string` | ID du dépôt concerné |
| `status` | `string` | Statut de la demande (`ACCEPTED`, `REJECTED`) |
| `failureReason.failureCode` | `string` | Code d'erreur (optionnel) |
| `failureReason.failureMessage` | `string` | Message d'erreur (optionnel) |

---

## 💻 Utilisation avec le SDK

### Installation

```bash
composer require andydefer/php-pawapay
```

### Exemple avec Builder (recommandé)

```php
<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpPawapay\Builders\ResendDepositCallbackBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

// 1. Renvoyer le callback avec le Builder
$response = ResendDepositCallbackBuilder::create(
    apiToken: 'your-api-token-here'
)
    ->withBaseUrl(PawaPayBaseUrl::SANDBOX)
    ->withDepositId('9b724dbf-32a7-4e63-96bb-59a4747e43ca')
    ->execute();

// 2. Traiter la réponse
if ($response->isAccepted()) {
    echo "✅ Callback renvoyé avec succès !\n";
    echo 'Deposit ID: ' . $response->getDepositId() . "\n";
    echo 'Statut: ' . $response->getStatus()->value . "\n";
} elseif ($response->isRejected()) {
    echo "❌ Échec du renvoi du callback\n";
    echo 'Deposit ID: ' . $response->getDepositId() . "\n";
    echo 'Statut: ' . $response->getStatus()->value . "\n";

    if ($response->hasFailureReason()) {
        $failure = $response->getFailureReason();
        echo "Code d'erreur: " . $failure->failureCode . "\n";
        echo 'Message: ' . $failure->failureMessage . "\n";
    }
}

if ($response->hasFailureReason()) {
    $failure = $response->getFailureReason();
    echo '❌ Erreur API: ' . $failure->failureCode . "\n";
    echo 'Message: ' . $failure->failureMessage . "\n";
}
```

### Exemple sans Builder (manuel)

```php
<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;

// 1. Créer le client
$client = new PawapayClient(
    apiToken: 'your-api-token-here',
    baseUrl: PawaPayBaseUrl::SANDBOX
);

// 2. Renvoyer le callback
$depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';
$response = $client->resendDepositCallback($depositId);

// 3. Traiter la réponse
if ($response->isAccepted()) {
    echo "✅ Callback renvoyé avec succès !\n";
    echo 'Deposit ID: ' . $response->getDepositId() . "\n";
} else {
    echo "❌ Échec\n";
    if ($response->hasFailureReason()) {
        $failure = $response->getFailureReason();
        echo "Code: " . $failure->failureCode . "\n";
        echo 'Message: ' . $failure->failureMessage . "\n";
    }
}
```

---

## 🧩 Value Objects disponibles

| Value Object | Description | Validation |
|--------------|-------------|------------|
| `UuidVO` | UUID v4 | Format UUID v4 |

## 🌍 Enums disponibles

| Enum | Description |
|------|-------------|
| `ResendCallbackStatus` | Statut de la demande (`ACCEPTED`, `REJECTED`) |
| `FailureCode` | Codes d'erreur de l'API |
| `PawaPayBaseUrl` | URL de base (`SANDBOX`, `PRODUCTION`) |

## 🔧 Codes d'erreur

| Code | Description |
|------|-------------|
| `NOT_FOUND` | Dépôt non trouvé |
| `INVALID_STATE` | Le dépôt n'a pas terminé son traitement |
| `AUTHENTICATION_ERROR` | Token API invalide |
| `AUTHORISATION_ERROR` | Token non autorisé |
| `UNKNOWN_ERROR` | Erreur inconnue |

---

## 🧪 Tests

```bash
./vendor/bin/phpunit --filter ResendDepositCallbackTest
```

### Exemple de test

```php
public function test_resend_deposit_callback_accepted(): void
{
    $depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';

    $this->client->addSuccessResponse([
        'depositId' => $depositId,
        'status' => 'ACCEPTED',
    ]);

    $response = $this->client->resendDepositCallback($depositId);

    $this->assertTrue($response->isAccepted());
    $this->assertSame($depositId, $response->getDepositId());
    $this->assertSame(ResendCallbackStatus::ACCEPTED, $response->getStatus());
    $this->assertFalse($response->hasFailureReason());
}
```

---

## 📝 Notes importantes

1. **État requis** : Le callback ne peut être renvoyé que si le dépôt est dans un état terminal (`COMPLETED`, `FAILED`, `REJECTED`).

2. **Idempotence** : Cette endpoint est idempotente. Elle peut être appelée plusieurs fois sans effet secondaire.

3. **Webhook** : Pawapay enverra le callback webhook configuré pour ce dépôt.

4. **Timeout** : Le SDK utilise un timeout de 30 secondes par défaut.

## 🔗 Voir aussi

- [Documentation officielle Pawapay](https://docs.pawapay.io)
- [API Reference - Deposits](https://docs.pawapay.io/api/deposits)
- [SDK GitHub](https://github.com/andydefer/php-pawapay)
- `InitiateDeposit` - Initiation de dépôt
- `CheckDepositStatus` - Vérification du statut d'un dépôt
- `CreatePaymentPage` - Création d'une page de paiement