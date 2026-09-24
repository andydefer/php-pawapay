# PHP Pawapay SDK

**SDK PHP pour l'intégration des paiements Mobile Money Pawapay à travers les marchés africains.**

[![PHP Version](https://img.shields.io/badge/PHP-%5E8.1-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

---

## Table des matières

- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [Opérations](#opérations)
  - [Initier un dépôt](#1-initier-un-dépôt)
  - [Vérifier le statut d'un dépôt](#2-vérifier-le-statut-dun-dépôt)
  - [Renvoyer un callback](#3-renvoyer-un-callback-de-dépôt)
  - [Créer une page de paiement](#4-créer-une-page-de-paiement)
  - [Prédire le provider d'un numéro](#5-prédire-le-provider-dun-numéro)
- [Traitement des callbacks](#traitement-des-callbacks)
- [Hooks applicatifs](#hooks-applicatifs)
- [Value Objects](#value-objects)
- [Enums](#enums)
- [Gestion des erreurs](#gestion-des-erreurs)
- [Intégration Laravel](#intégration-laravel)
- [Exemples par pays](#exemples-par-pays)
- [Références techniques](#références-techniques)
- [Licence](#licence)

---

## Introduction

### Qu'est-ce que Pawapay ?

Pawapay est une plateforme de paiement qui permet d'accepter des paiements via Mobile Money (M-Pesa, MTN MoMo, Orange Money, etc.) dans plus de 20 pays africains.

### Ce que fait ce SDK

Ce SDK transforme les appels HTTP bruts vers l'API Pawapay en **objets PHP typés, validés et documentés**. Il expose trois niveaux d'utilisation :

- **`PawapayClient`** — client HTTP bas niveau, expose les `Response` typées du SDK.
- **`PawapayService`** — façade applicative haut niveau, prend des `Record` et retourne des `Data`.
- **Builders & `CallbackBuilder`** — API fluide pour construire des Value Objects et dispatcher les callbacks.

### Bénéfices

| Sans SDK | Avec SDK |
|----------|----------|
| `json_decode()` manuel | Objets PHP typés (`DepositDataStruct`, `InitiateDepositData`) |
| Validation manuelle | Value Objects auto-validants (`PhoneNumberVO`, `UuidVO`, `AmountVO`) |
| Strings magiques | Enums (`Provider::MTN_MOMO_ZMB`, `Currency::ZMW`) |
| Code répétitif | Builders fluides + façade `PawapayService` |
| Mélange HTTP/métier | Séparation stricte `Record` (entrée) / `Data` (sortie) |
| Dispatch manuel des callbacks | `CallbackBuilder` + `HandlesCallbacksInterface` typés |

### Compatibilité PHP

| Version | Support |
|---------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.0 | ❌ Non supporté (enums requis) |

---

## Installation

```bash
composer require andydefer/php-pawapay
```

**Prérequis :**

- PHP 8.1 ou supérieur
- Extension `bcmath` (opérations monétaires précises)
- Extension `json`
- Extension `curl` (via Guzzle)

---

## Configuration

### Via la façade `PawapayService` (recommandé)

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Services\PawapayService;

$service = PawapayService::create(
    apiToken: $_ENV['PAWAPAY_API_TOKEN'],
    baseUrl: PawaPayBaseUrl::SANDBOX, // ou PRODUCTION
);
```

### Via le client bas niveau

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;

$client = new PawapayClient(
    apiToken: $_ENV['PAWAPAY_API_TOKEN'],
    baseUrl: PawaPayBaseUrl::SANDBOX,
);
```

### Variables d'environnement recommandées

```env
PAWAPAY_API_TOKEN=eyJraWQiOiIxIiwiYWxnIjoiRVMyNTYifQ...
PAWAPAY_ENVIRONMENT=sandbox
```

> **Ne jamais** committer un token dans le code source. Utiliser un gestionnaire de secrets en production.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Votre code métier                        │
└────────────────────────────────┬────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                        PawapayService                           │
│   Façade haut niveau — Record en entrée, Data en sortie         │
│   + hooks before/after sur chaque opération                     │
└────────────────────────────────┬────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                        PawapayClient                            │
│   Client HTTP — Value Objects en entrée, Response en sortie     │
└────────────────────────────────┬────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                          API Pawapay                            │
│              https://api.sandbox.pawapay.io                     │
│              https://api.pawapay.io                             │
└─────────────────────────────────────────────────────────────────┘
```

### Composants

| Couche | Composant | Rôle |
|--------|-----------|------|
| Façade | `PawapayService` | Orchestration métier, `Record` → `Data` |
| Client | `PawapayClient` | Communication HTTP, `Value Object` → `Response` |
| Builders | `InitiateDepositBuilder`, etc. | API fluide pour construire les Value Objects |
| Callback | `CallbackBuilder` + `HandlesCallbacksInterface` | Dispatch typé des callbacks |
| Structures | `InitiateDepositStruct`, etc. | Corps de requête typés |
| Responses | `InitiateDepositResponse`, etc. | Réponses HTTP typées |
| Records | `InitiateDepositRecord`, etc. | Entrées de la façade |
| Datas | `InitiateDepositData`, etc. | Sorties de la façade |
| Value Objects | `PhoneNumberVO`, `AmountVO`, `UuidVO` | Validation auto-validante |
| Enums | `Provider`, `Currency`, `Country` | Choix typés |
| Graphs | `PayerGraph`, `AccountDetailsGraph` | Sous-structures |

---

## Opérations

### 1. Initier un dépôt

Initie un paiement Mobile Money auprès du payeur.

**Endpoint :** `POST /v2/deposits`

#### Avec la façade (recommandé)

```php
<?php

declare(strict_types=1);

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
        'depositId' => UuidVO::generate(),
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
    // Stocker $data->depositId en base pour la suite
}
```

#### Avec le builder bas niveau

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\InitiateDepositBuilder;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\Provider;

$deposit = InitiateDepositBuilder::create()
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('260763456789')
    ->withProvider(Provider::MTN_MOMO_ZMB)
    ->withAmount(15.00)
    ->withCurrency(Currency::ZMW)
    ->withClientReferenceId('INV-123456')
    ->withCustomerMessage('Payment order 123')
    ->withMetadataKey('orderId', 'ORD-123456789')
    ->build();
```

#### Champs du `InitiateDepositData` retourné

| Propriété | Type | Description |
|-----------|------|-------------|
| `$depositId` | `?UuidVO` | UUID du dépôt |
| `$status` | `DepositStatus` | Statut d'initiation |
| `$created` | `?DateTimeZuluVO` | Horodatage de création |
| `$failureReason` | `?FailureReasonData` | Raison d'échec éventuelle |
| `$isAccepted` | `bool` | `true` si accepté |
| `$isRejected` | `bool` | `true` si rejeté |
| `$isDuplicateIgnored` | `bool` | `true` si doublon ignoré |
| `$hasFailureReason` | `bool` | `true` si une raison d'échec existe |

---

### 2. Vérifier le statut d'un dépôt

**Endpoint :** `GET /v2/deposits/{depositId}`

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\CheckDepositStatusBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

$response = CheckDepositStatusBuilder::create($apiToken)
    ->withBaseUrl(PawaPayBaseUrl::PRODUCTION)
    ->withDepositId('60bd6a3d-177e-4ec2-a65c-d622ede29c99')
    ->execute();

if (! $response->isFound()) {
    return;
}

$deposit = $response->getDepositData();

match (true) {
    $deposit->status->isCompleted() => $order->markAsPaid(),
    $deposit->status->isFailed()    => $order->markAsFailed(),
    $deposit->status->isPending()   => $order->keepPending(),
    default                         => null,
};
```

#### Statuts possibles

| Statut | Signification |
|--------|---------------|
| `ACCEPTED` | Dépôt accepté, en attente |
| `PROCESSING` | En cours de traitement |
| `IN_RECONCILIATION` | En réconciliation |
| `COMPLETED` | ✅ Terminé avec succès |
| `FAILED` | ❌ Échoué |
| `REJECTED` | ❌ Rejeté |
| `DUPLICATE_IGNORED` | Doublon ignoré |

---

### 3. Renvoyer un callback de dépôt

Demande à Pawapay de rejouer le callback d'un dépôt. Utile lorsque votre webhook a échoué.

**Endpoint :** `POST /v2/deposits/resend-callback/{depositId}`

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\ResendDepositCallbackBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

$response = ResendDepositCallbackBuilder::create($apiToken)
    ->withBaseUrl(PawaPayBaseUrl::PRODUCTION)
    ->withDepositId('9b724dbf-32a7-4e63-96bb-59a4747e43ca')
    ->execute();

if ($response->isAccepted()) {
    Log::info('Callback renvoyé');
} else {
    Log::error('Échec renvoi callback', [
        'code' => $response->getFailureReason()?->failureCode->value,
    ]);
}
```

> **À privilégier au polling.** Espacer les appels (backoff exponentiel) — Pawapay applique un rate limit.

---

### 4. Créer une page de paiement

Génère une page hébergée par Pawapay vers laquelle rediriger le client.

**Endpoint :** `POST /v2/paymentpage`

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\CreatePaymentPageBuilder;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\Language;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

$response = CreatePaymentPageBuilder::create($apiToken)
    ->withBaseUrl(PawaPayBaseUrl::PRODUCTION)
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('243827833329')
    ->withAmount(24.60)
    ->withCurrency(Currency::USD)
    ->withReturnUrl('https://example.com/success')
    ->withLanguage(Language::FR)
    ->withCountry(Country::COD)
    ->withCustomerMessage('Paiement commande')
    ->withReason('Commande 12345')
    ->execute();

if ($response->hasFailureReason()) {
    throw new RuntimeException($response->getFailureReason()->failureMessage);
}

header('Location: ' . $response->getRedirectUrlAsString());
exit;
```

---

### 5. Prédire le provider d'un numéro

Détermine le pays et le provider Mobile Money associés à un numéro de téléphone. Utile avant un dépôt ou un payout lorsque le provider n'est pas connu.

**Endpoint :** `POST /v2/predict-provider`

#### Avec la façade (recommandé)

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Records\PredictProviderRecord;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

$data = $service->predictProvider(
    PredictProviderRecord::from([
        'phoneNumber' => PhoneNumberVO::from('260763456789'),
    ]),
);

if ($data->isFound) {
    echo $data->provider->value;  // 'MTN_MOMO_ZMB'
    echo $data->country->value;   // 'ZMB'
} elseif ($data->hasFailureReason) {
    echo $data->failureReason->failureCode->value;
}
```

#### Avec le builder bas niveau

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\PredictProviderBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

$response = PredictProviderBuilder::create($apiToken)
    ->withBaseUrl(PawaPayBaseUrl::SANDBOX)
    ->withPhoneNumber('260763456789')
    ->execute();

if ($response->isFound()) {
    echo $response->getProvider()->value;  // 'MTN_MOMO_ZMB'
    echo $response->getCountry()->value;   // 'ZMB'
}
```

#### Champs du `PredictProviderData` retourné

| Propriété | Type | Description |
|-----------|------|-------------|
| `$country` | `?Country` | Code pays ISO 3166-1 alpha-3 |
| `$provider` | `?Provider` | Provider Mobile Money identifié |
| `$phoneNumber` | `?PhoneNumberVO` | Numéro normalisé E.164 sans `+` |
| `$isFound` | `bool` | `true` si le provider a été identifié |
| `$failureReason` | `?FailureReasonData` | Raison d'échec éventuelle |
| `$hasFailureReason` | `bool` | `true` si une raison d'échec existe |

#### Cas d'usage typique

```php
// 1. Résoudre dynamiquement le provider
$prediction = $service->predictProvider(
    PredictProviderRecord::from([
        'phoneNumber' => PhoneNumberVO::from($userInput),
    ]),
);

if (! $prediction->isFound) {
    return response()->json(['error' => 'Pays non supporté'], 422);
}

// 2. Initier le dépôt avec le provider résolu
$data = $service->initiateDeposit(
    InitiateDepositRecord::from([
        'depositId' => UuidVO::generate(),
        'payer' => PayerVO::from([
            'type' => PayerType::MMO,
            'accountDetails' => AccountDetailsVO::from([
                'phoneNumber' => $prediction->phoneNumber,
                'provider' => $prediction->provider,
            ]),
        ]),
        'amount' => AmountVO::from(15.00),
        'currency' => Currency::ZMW,
    ]),
);
```

---

## Traitement des callbacks

Pawapay envoie les callbacks (deposit, payout, refund, checkout) sur votre endpoint. Le SDK fournit deux façons de les traiter :

1. **Via `CallbackBuilder`** — approche bas niveau, prend un payload brut.
2. **Via `PawapayService::handleCallback()`** — approche haut niveau, prend un `Struct` typé.

### Interface `HandlesCallbacksInterface`

Un handler unique avec quatre méthodes typées.

```php
<?php

declare(strict_types=1);

namespace App\Services;

use AndyDefer\PhpPawapay\Contracts\Callbacks\HandlesCallbacksInterface;
use AndyDefer\PhpPawapay\Structures\Callbacks\CheckoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\PayoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\RefundCallbackStruct;

final class PawapayCallbackHandler implements HandlesCallbacksInterface
{
    public function handleDeposit(DepositCallbackStruct $struct): void
    {
        // traiter le dépôt
    }

    public function handlePayout(PayoutCallbackStruct $struct): void {}

    public function handleRefund(RefundCallbackStruct $struct): void {}

    public function handleCheckout(CheckoutCallbackStruct $struct): void {}
}
```

### Approche 1 — `CallbackBuilder` (payload brut)

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\CallbackBuilder;

$payload = json_decode($request->getContent(), true);

CallbackBuilder::create()
    ->withHandler(app(HandlesCallbacksInterface::class))
    ->execute($payload);
```

### Approche 2 — `PawapayService::handleCallback()` (Struct typé)

L'appelant hydrate lui-même le `Struct` à partir du payload, puis le passe au service.

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Enums\CallbackOperationType;
use App\Services\PawapayCallbackHandler;

$payload = $request->json()->all();

$operation = CallbackOperationType::fromPayload($payload);
$structClass = $operation->structClass();

/** @var \AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct $struct */
$struct = $structClass::from($payload);

$pawapay->handleCallback($struct, new PawapayCallbackHandler());
```

### Détection automatique du type

`CallbackOperationType::fromPayload()` détecte l'opération à partir des champs présents :

| Champ discriminant | Opération |
|--------------------|-----------|
| `checkoutId` | `CHECKOUT` |
| `depositId` | `DEPOSIT` |
| `payoutId` | `PAYOUT` |
| `refundId` | `REFUND` |

> **Ordre important :** `checkoutId` est vérifié en premier car un checkout contient aussi un objet `deposit`.

`CallbackOperationType::structClass()` retourne la classe du `Struct` à hydrater pour l'opération détectée.

> **Sécurité :** La vérification de signature et l'idempotence restent à votre charge — elles ne sont pas gérées par ce SDK.

---

## Hooks applicatifs

`PawapayService` expose **12 hooks** surchargeables : 2 par opération métier (5 opérations) + 2 pour les callbacks. Chaque hook `before*` / `after*` des opérations métier peut **court-circuiter** le flux en retournant un `ErrorResponseData`. Les hooks de callback retournent `void`.

### Opérations métier

| Hook | Signature |
|------|-----------|
| `beforeInitiateDeposit` | `(InitiateDepositRecord): ?ErrorResponseData` |
| `afterInitiateDeposit` | `(InitiateDepositRecord, InitiateDepositData): ?ErrorResponseData` |
| `beforeCheckDepositStatus` | `(CheckDepositStatusRecord): ?ErrorResponseData` |
| `afterCheckDepositStatus` | `(CheckDepositStatusRecord, CheckDepositStatusData): ?ErrorResponseData` |
| `beforeResendDepositCallback` | `(ResendDepositCallbackRecord): ?ErrorResponseData` |
| `afterResendDepositCallback` | `(ResendDepositCallbackRecord, ResendDepositCallbackData): ?ErrorResponseData` |
| `beforeCreatePaymentPage` | `(CreatePaymentPageRecord): ?ErrorResponseData` |
| `afterCreatePaymentPage` | `(CreatePaymentPageRecord, CreatePaymentPageData): ?ErrorResponseData` |
| `beforePredictProvider` | `(PredictProviderRecord): ?ErrorResponseData` |
| `afterPredictProvider` | `(PredictProviderRecord, PredictProviderData): ?ErrorResponseData` |

### Callbacks

| Hook | Signature |
|------|-----------|
| `beforeHandleCallback` | `(Struct, CallbackOperationType): void` |
| `afterHandleCallback` | `(Struct, CallbackOperationType): void` |

### Exemple — service étendu

```php
<?php

declare(strict_types=1);

namespace App\Services;

use AndyDefer\PhpPawapay\Datas\ErrorResponseData;
use AndyDefer\PhpPawapay\Datas\InitiateDepositData;
use AndyDefer\PhpPawapay\Enums\CallbackOperationType;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Services\PawapayService;
use AndyDefer\PhpPawapay\Structures\Callbacks\CheckoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\PayoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\RefundCallbackStruct;
use AndyDefer\PhpVo\Enums\HttpStatusCode;

final class AfyaPawapayService extends PawapayService
{
    protected function beforeInitiateDeposit(InitiateDepositRecord $record): ?ErrorResponseData
    {
        if ($record->amount->toFloat() > 10_000) {
            return ErrorResponseData::from([
                'message' => 'Montant trop élevé',
                'status' => HttpStatusCode::FORBIDDEN,
                'errorCode' => 'AMOUNT_TOO_HIGH',
            ]);
        }

        return null;
    }

    protected function afterInitiateDeposit(
        InitiateDepositRecord $record,
        InitiateDepositData $data,
    ): ?ErrorResponseData {
        // Persister la tentative, notifier, ...
        return null;
    }

    protected function beforeHandleCallback(
        DepositCallbackStruct|PayoutCallbackStruct|RefundCallbackStruct|CheckoutCallbackStruct $struct,
        CallbackOperationType $operation,
    ): void {
        // Vérification de signature, déduplication, ...
    }

    protected function afterHandleCallback(
        DepositCallbackStruct|PayoutCallbackStruct|RefundCallbackStruct|CheckoutCallbackStruct $struct,
        CallbackOperationType $operation,
    ): void {
        // Audit trail, métriques, ...
    }
}
```

---

## Value Objects

Les Value Objects valident les données à leur construction. Une valeur invalide déclenche immédiatement une `InvalidArgumentException`.

| VO | Validation | Exemple |
|----|------------|---------|
| `UuidVO` | Format UUID v4 | `UuidVO::from('f4401bd2-...')` ou `UuidVO::generate()` |
| `PhoneNumberVO` | Format E.164 sans `+` | `PhoneNumberVO::from('260763456789')` |
| `AmountVO` | Positif, 2 décimales max | `AmountVO::from(15.00)` |
| `ReferenceVO` | 4 à 64 caractères | `ReferenceVO::from('INV-123456')` |
| `CustomerMessageVO` | 4 à 22 caractères | `CustomerMessageVO::from('Payment order')` |
| `MetadataVO` | 1 à 10 champs, valeurs scalaires | `new MetadataVO($data)` |
| `ClientReferenceIdVO` | 4 à 64 caractères, `a-z A-Z 0-9 -` | `new ClientReferenceIdVO('INV-123')` |

> **`MetadataVO`** : refuse les `StrictDataObject` vides ou ne contenant que des clés exclues (`isPII`). Au moins un champ utile est requis.

---

## Enums

| Enum | Description | Exemples |
|------|-------------|----------|
| `Provider` | Fournisseurs Mobile Money | `MTN_MOMO_ZMB`, `VODACOM_MPESA_COD`, `MPESA_KEN` |
| `Currency` | Devises supportées | `ZMW`, `USD`, `KES`, `NGN` |
| `Country` | Pays supportés (ISO 3166-1 alpha-3) | `ZMB`, `COD`, `KEN` |
| `Language` | Langues des pages | `EN`, `FR` |
| `DepositStatus` | Statuts de dépôt | `ACCEPTED`, `COMPLETED`, `FAILED` |
| `DepositSearchStatus` | Résultat de recherche | `FOUND`, `NOT_FOUND` |
| `ResendCallbackStatus` | Résultat de renvoi | `ACCEPTED`, `REJECTED` |
| `FailureCode` | Codes d'erreur normalisés | `INVALID_PHONE_NUMBER`, `INVALID_COUNTRY` |
| `PawaPayBaseUrl` | URL de base | `SANDBOX`, `PRODUCTION` |
| `CallbackOperationType` | Type de callback | `DEPOSIT`, `PAYOUT`, `REFUND`, `CHECKOUT` |

---

## Gestion des erreurs

### Depuis le service (`Data`)

Aucune exception métier n'est levée par la façade pour les erreurs PawaPay. Toute erreur se traduit par `failureReason` non-null dans la `Data` de retour.

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

### Depuis un hook (court-circuit)

Un hook `before*` ou `after*` peut retourner un `ErrorResponseData`. Le service lève alors immédiatement la `Data` correspondante et n'appelle pas le client HTTP (ou n'exécute pas le `after*`).

```php
protected function beforeInitiateDeposit(InitiateDepositRecord $record): ?ErrorResponseData
{
    if ($record->amount->toFloat() > 10_000) {
        return ErrorResponseData::from([
            'message' => 'Montant trop élevé',
            'status' => HttpStatusCode::FORBIDDEN,
            'errorCode' => 'AMOUNT_TOO_HIGH',
        ]);
    }

    return null;
}
```

### Depuis le client (`Response`)

```php
try {
    $response = $client->initiateDeposit($deposit);

    if (! $response->isSuccess()) {
        throw new RuntimeException('Pawapay error: ' . $response->getStatusCode()->getPhrase());
    }
} catch (GuzzleException $e) {
    Log::error('Réseau Pawapay', ['exception' => $e]);
}
```

### Depuis les Value Objects

```php
try {
    $phone = PhoneNumberVO::from($input);
} catch (InvalidArgumentException $e) {
    return response()->json(['error' => $e->getMessage()], 422);
}
```

---

## Intégration Laravel

### Service Provider

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use AndyDefer\PhpPawapay\Contracts\PawapayInterface;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Services\PawapayService;
use Illuminate\Support\ServiceProvider;

final class PawapayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PawapayInterface::class, function () {
            return PawapayService::create(
                apiToken: config('pawapay.api_token'),
                baseUrl: config('pawapay.environment') === 'production'
                    ? PawaPayBaseUrl::PRODUCTION
                    : PawaPayBaseUrl::SANDBOX,
            );
        });
    }
}
```

### Fichier de configuration

```php
<?php

// config/pawapay.php

return [
    'api_token' => env('PAWAPAY_API_TOKEN'),
    'environment' => env('PAWAPAY_ENVIRONMENT', 'sandbox'),
];
```

### Injection dans un contrôleur

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use AndyDefer\PhpPawapay\Contracts\PawapayInterface;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use Illuminate\Http\JsonResponse;

final class PaymentController
{
    public function __construct(
        private readonly PawapayInterface $pawapay,
    ) {}

    public function pay(Request $request): JsonResponse
    {
        $data = $this->pawapay->initiateDeposit(
            InitiateDepositRecord::from($request->validated()),
        );

        return response()->json([
            'depositId' => $data->depositId?->getValue(),
            'status' => $data->status->value,
        ]);
    }
}
```

### Job de polling avec backoff

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use AndyDefer\PhpPawapay\Builders\CheckDepositStatusBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

final class RecheckDepositJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 10;

    public function __construct(public readonly int $orderId) {}

    public function handle(): void
    {
        $order = Order::findOrFail($this->orderId);

        $response = CheckDepositStatusBuilder::create(config('pawapay.api_token'))
            ->withDepositId($order->deposit_id)
            ->execute();

        if (! $response->isFound()) {
            $this->release(now()->addMinutes(2));
            return;
        }

        $status = $response->getDepositData()->status;

        if ($status->isFinal()) {
            $status->isCompleted() ? $order->markAsPaid() : $order->markAsFailed();
            return;
        }

        $this->release(now()->addMinutes(2));
    }
}
```

---

## Exemples par pays

### Zambie — MTN MoMo (ZMW)

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

### Kenya — M-Pesa (KES)

```php
$deposit = InitiateDepositBuilder::create()
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('254712345678')
    ->withProvider(Provider::MPESA_KEN)
    ->withAmount(100.00)
    ->withCurrency(Currency::KES)
    ->build();
```

### Nigeria — MTN MoMo (NGN)

```php
$deposit = InitiateDepositBuilder::create()
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('2348034567890')
    ->withProvider(Provider::MTN_MOMO_NGA)
    ->withAmount(5000.00)
    ->withCurrency(Currency::NGN)
    ->build();
```

### Ghana — MTN MoMo (GHS)

```php
$deposit = InitiateDepositBuilder::create()
    ->withAutoGeneratedDepositId()
    ->withPhoneNumber('233244123456')
    ->withProvider(Provider::MTN_MOMO_GHA)
    ->withAmount(50.00)
    ->withCurrency(Currency::GHS)
    ->build();
```

---

## Références techniques

Documentation détaillée de chaque composant :

### Services

- [`PawapayService`](docs/services/PawapayService.md) — façade métier, hooks, dispatch callbacks
- [`PawapayClient`](docs/services/PawapayClient.md) — client HTTP bas niveau

### Builders

- [`InitiateDepositBuilder`](docs/builders/InitiateDepositBuilder.md)
- [`CheckDepositStatusBuilder`](docs/builders/CheckDepositStatusBuilder.md)
- [`ResendDepositCallbackBuilder`](docs/builders/ResendDepositCallbackBuilder.md)
- [`CreatePaymentPageBuilder`](docs/builders/CreatePaymentPageBuilder.md)
- [`PredictProviderBuilder`](docs/builders/PredictProviderBuilder.md)
- [`CallbackBuilder`](docs/builders/CallbackBuilder.md)

### Enums

- [`CallbackOperationType`](docs/enums/CallbackOperationType.md)
- [`Provider`](docs/enums/Provider.md)
- [`Currency`](docs/enums/Currency.md)
- [`Country`](docs/enums/Country.md)
- [`DepositStatus`](docs/enums/DepositStatus.md)
- [`FailureCode`](docs/enums/FailureCode.md)

### Concepts

- [Value Objects](docs/concepts/value-objects.md)
- [Records vs Data](docs/concepts/records-vs-data.md)
- [Gestion des callbacks](docs/concepts/callbacks.md)
- [Hooks applicatifs](docs/concepts/hooks.md)

### Endpoints

- [Initiate Deposit](docs/endpoints/initiate-deposit.md)
- [Check Deposit Status](docs/endpoints/check-deposit-status.md)
- [Resend Deposit Callback](docs/endpoints/resend-deposit-callback.md)
- [Create Payment Page](docs/endpoints/create-payment-page.md)
- [Predict Provider](docs/endpoints/predict-provider.md)

---

## Licence

MIT © Andy Defer

Voir le fichier [LICENSE](LICENSE) pour les détails.