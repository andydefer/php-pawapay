# PawapayService - Référence Technique

## Description

Façade métier par défaut du SDK PawaPay : orchestre les quatre opérations PawaPay et le dispatch des callbacks, en convertissant les `Record` en Value Objects et les `Response` en `Data`.

## Hiérarchie / Implémentations

```
PawapayInterface
    └── PawapayService
```

Classe non `final` — conçue pour être étendue via héritage afin de greffer des hooks métier.

## Rôle principal

`PawapayService` est la couche d'orchestration entre votre code métier et le client HTTP PawaPay. Elle :

1. reçoit des `Record` (entrées typées),
2. applique les hooks `before*`,
3. convertit les `Record` en Value Objects (`InitiateDepositVO`, `PaymentPageStruct`, etc.),
4. délègue l'appel HTTP au `PawapayClient`,
5. convertit les `Response` en `Data` typées,
6. applique les hooks `after*`,
7. retourne la `Data` ou propage un `ErrorResponseData`.

Elle expose également `handleCallback()` pour dispatcher un callback PawaPay vers la bonne méthode d'un `HandlesCallbacksInterface`.

Chaque hook peut **court-circuiter** le flux en retournant un `ErrorResponseData` (les quatre opérations métier uniquement).

## Installation

Aucune installation spécifique. Fournie par le package `andydefer/php-pawapay`.

```bash
composer require andydefer/php-pawapay
```

## API / Méthodes publiques

### `__construct(PawapayClientInterface $client)`

Initialise le service avec un client PawaPay.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$client` | `PawapayClientInterface` | Client HTTP bas niveau |

**Exemple :**

```php
$service = new PawapayService(new PawapayClient($token, PawaPayBaseUrl::SANDBOX));
```

---

### `create(string $apiToken, PawaPayBaseUrl $baseUrl): self`

Factory statique qui instancie un `PawapayClient` par défaut.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$apiToken` | `string` | Token d'authentification Bearer |
| `$baseUrl` | `PawaPayBaseUrl` | Enum `SANDBOX` ou `PRODUCTION` |

**Retourne :** `self` — Instance prête à l'emploi.

**Exemple :**

```php
$service = PawapayService::create('eyJraWQiOiIx...', PawaPayBaseUrl::SANDBOX);
```

---

### `initiateDeposit(InitiateDepositRecord $record): InitiateDepositData|ErrorResponseData`

Initie un dépôt Mobile Money.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$record` | `InitiateDepositRecord` | Données du dépôt |

**Retourne :** `InitiateDepositData` en succès, `ErrorResponseData` si un hook court-circuite.

**Exceptions :** `InvalidArgumentException` si un Value Object interne est invalide.

---

### `checkDepositStatus(CheckDepositStatusRecord $record): CheckDepositStatusData|ErrorResponseData`

Vérifie le statut d'un dépôt existant.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$record` | `CheckDepositStatusRecord` | Identifiant du dépôt |

**Retourne :** `CheckDepositStatusData` en succès, `ErrorResponseData` si court-circuit.

---

### `resendDepositCallback(ResendDepositCallbackRecord $record): ResendDepositCallbackData|ErrorResponseData`

Demande à PawaPay de renvoyer le callback d'un dépôt.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$record` | `ResendDepositCallbackRecord` | Identifiant du dépôt |

**Retourne :** `ResendDepositCallbackData` en succès, `ErrorResponseData` si court-circuit.

---

### `createPaymentPage(CreatePaymentPageRecord $record): CreatePaymentPageData|ErrorResponseData`

Crée une page de paiement hébergée PawaPay.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$record` | `CreatePaymentPageRecord` | Données de la page |

**Retourne :** `CreatePaymentPageData` en succès, `ErrorResponseData` si court-circuit.

---

### `handleCallback(DepositCallbackStruct|PayoutCallbackStruct|RefundCallbackStruct|CheckoutCallbackStruct $struct, HandlesCallbacksInterface $handler): void`

Dispatche un callback PawaPay vers la méthode correspondante du handler.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$struct` | `DepositCallbackStruct\|PayoutCallbackStruct\|RefundCallbackStruct\|CheckoutCallbackStruct` | Struct du callback hydraté |
| `$handler` | `HandlesCallbacksInterface` | Handler qui reçoit le callback |

**Retourne :** `void`

**Exemple :**

```php
$service->handleCallback($struct, $handler);
```

---

## Cas d'utilisation

### Cas 1 : Dépôt simple

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Services\PawapayService;

$service = PawapayService::create(
    apiToken: $_ENV['PAWAPAY_API_TOKEN'],
    baseUrl: PawaPayBaseUrl::SANDBOX,
);

$data = $service->initiateDeposit(InitiateDepositRecord::from([
    // ...
]));

if ($data->isAccepted) {
    echo "Dépôt accepté : {$data->depositId->getValue()}\n";
}
```

### Cas 2 : Service étendu avec hooks

```php
<?php

declare(strict_types=1);

namespace App\Services;

use AndyDefer\PhpPawapay\Datas\InitiateDepositData;
use AndyDefer\PhpPawapay\Datas\ErrorResponseData;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Services\PawapayService;

final class AfyaPawapayService extends PawapayService
{
    protected function beforeInitiateDeposit(InitiateDepositRecord $record): ?ErrorResponseData
    {
        if ($record->amount->toFloat() > 10_000) {
            return ErrorResponseData::from([
                'message' => 'Montant trop élevé',
                'status' => \AndyDefer\PhpVo\Enums\HttpStatusCode::FORBIDDEN,
                'errorCode' => 'AMOUNT_TOO_HIGH',
            ]);
        }

        return null;
    }

    protected function afterInitiateDeposit(
        InitiateDepositRecord $record,
        InitiateDepositData $data,
    ): ?ErrorResponseData {
        // Persister la tentative
        return null;
    }
}
```

### Cas 3 : Dispatch d'un callback

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Enums\CallbackOperationType;
use AndyDefer\PhpPawapay\Services\PawapayService;
use App\Services\PawapayCallbackHandler;

$payload = $request->json()->all();

$operation = CallbackOperationType::fromPayload($payload);
$structClass = $operation->structClass();
$struct = $structClass::from($payload);

/** @var PawapayService $service */
$service->handleCallback($struct, new PawapayCallbackHandler());
```

## Flux d'exécution

### Opération métier standard

```
Record
    │
    ▼
before<Operation>(Record)
    │
    ├── ErrorResponseData → return (court-circuit)
    │
    ▼
Value Object / Struct
    │
    ▼
PawapayClient HTTP call
    │
    ▼
Response → Data::from(...)
    │
    ▼
after<Operation>(Record, Data)
    │
    ├── ErrorResponseData → return (court-circuit)
    │
    ▼
return Data
```

### Dispatch de callback

```
Struct + Handler
    │
    ▼
operationOf(Struct) → CallbackOperationType
    │
    ▼
beforeHandleCallback(Struct, Operation)
    │
    ▼
match (Struct)
    ├── DepositCallbackStruct  → handler->handleDeposit
    ├── PayoutCallbackStruct   → handler->handlePayout
    ├── RefundCallbackStruct   → handler->handleRefund
    └── CheckoutCallbackStruct → handler->handleCheckout
    │
    ▼
afterHandleCallback(Struct, Operation)
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Value Object invalide | `InvalidArgumentException` | Message dépendant du VO |
| Struct callback invalide | `InvalidArgumentException` | Message dépendant du Struct |
| Erreur réseau | `GuzzleException` | Message dépendant de Guzzle |
| Court-circuit hook | *(retour `ErrorResponseData`)* | N/A |

## Intégration

| Composant | Rôle |
|-----------|------|
| `PawapayClientInterface` | Client HTTP injecté au constructeur |
| `InitiateDepositRecord`, etc. | Entrées des 4 opérations |
| `InitiateDepositData`, etc. | Sorties des 4 opérations |
| `ErrorResponseData` | Retour de court-circuit via hook |
| `HandlesCallbacksInterface` | Handler pour `handleCallback()` |
| `CallbackOperationType` | Résolution du type d'opération |
| `DepositCallbackStruct`, etc. | Struct de callbacks |
| `FailureReasonStruct` | Conversion en `FailureReasonData` |

Le service est **sans état** (hors dépendance injectée). Il peut être enregistré comme singleton dans un container DI.

## Performance

- Aucune I/O hors appels HTTP délégués au client.
- Conversion `Response` → `Data` en O(1).
- `operationOf()` fait un `match` sur 4 cas — O(1).
- Les hooks vides ne coûtent qu'un appel de méthode.

## Compatibilité

| Version PHP | Support |
|-------------|---------|
| PHP 8.1+ | ✅ Complet (enums, `match`, `readonly`) |
| PHP 8.0 | ❌ Non supporté |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Enums\PayerType;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Services\PawapayService;
use AndyDefer\PhpPawapay\ValueObjects\AccountDetailsVO;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

$service = PawapayService::create(
    apiToken: $_ENV['PAWAPAY_API_TOKEN'],
    baseUrl: PawaPayBaseUrl::SANDBOX,
);

$data = $service->initiateDeposit(InitiateDepositRecord::from([
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
]));

if ($data->isAccepted) {
    echo "Dépôt accepté : {$data->depositId->getValue()}\n";
} elseif ($data->hasFailureReason) {
    echo "Échec : {$data->failureReason->failureCode->value}\n";
}
```

## Voir aussi

- `PawapayInterface` - Contrat implémenté
- `PawapayClient` - Client HTTP sous-jacent
- `InitiateDepositRecord`, `CheckDepositStatusRecord`, `ResendDepositCallbackRecord`, `CreatePaymentPageRecord` - Entrées
- `InitiateDepositData`, `CheckDepositStatusData`, `ResendDepositCallbackData`, `CreatePaymentPageData` - Sorties
- `ErrorResponseData` - Court-circuit de hook
- `HandlesCallbacksInterface` - Handler de callbacks
- `CallbackOperationType` - Détection du type d'opération