# PawapayService - Référence Technique

## Description

Service applicatif qui encapsule le `PawapayClient` bas niveau. Il traduit les `Record` d'entrée en objets métier PawaPay, exécute l'appel HTTP, puis convertit les `Response` en `Data` typées pour l'appelant.

## Hiérarchie / Implémentations

- Implémente : `AndyDefer\PhpPawapay\Contracts\PawapayInterface`
- Dépend de : `AndyDefer\PhpPawapay\Contracts\PawapayClientInterface`
- Utilise : `PawapayClient`, `InitiateDepositVO`, `ReferenceVO`, `PaymentPageStruct`, `FailureReasonStruct`
- Retourne : `InitiateDepositData`, `CheckDepositStatusData`, `ResendDepositCallbackData`, `CreatePaymentPageData`, `FailureReasonData`

## Rôle principal

Fournir une façade stable, typée et testable au-dessus du SDK PawaPay. Elle expose quatre opérations métier (dépôt, statut, callback, page de paiement) avec une signature uniforme `Record → Data`, sans laisser fuiter les `Struct`, `Graph` ou `Response` bruts du client.

## API

### `__construct(PawapayClientInterface $client)`

| Paramètre | Type | Requis | Défaut | Description |
|-----------|------|--------|--------|-------------|
| `$client` | `PawapayClientInterface` | ✅ | - | Client HTTP bas niveau qui exécute les appels PawaPay |

### `static create(string $apiToken, PawaPayBaseUrl $baseUrl): self`

| Paramètre | Type | Requis | Défaut | Description |
|-----------|------|--------|--------|-------------|
| `$apiToken` | `string` | ✅ | - | Token API PawaPay |
| `$baseUrl` | `PawaPayBaseUrl` | ✅ | - | Environnement ciblé (`SANDBOX` ou `PRODUCTION`) |

Retourne une instance auto-construite avec un `PawapayClient` interne.

### `initiateDeposit(InitiateDepositRecord $record): InitiateDepositData`

Déclenche un dépôt Mobile Money à partir d'un Record.

| Paramètre | Type | Requis | Défaut | Description |
|-----------|------|--------|--------|-------------|
| `$record` | `InitiateDepositRecord` | ✅ | - | Données du dépôt (depositId, payer, amount, currency, clientReferenceId, customerMessage, metadata) |

### `checkDepositStatus(CheckDepositStatusRecord $record): CheckDepositStatusData`

Récupère l'état d'un dépôt existant.

| Paramètre | Type | Requis | Défaut | Description |
|-----------|------|--------|--------|-------------|
| `$record` | `CheckDepositStatusRecord` | ✅ | - | Contient le `depositId` à interroger |

### `resendDepositCallback(ResendDepositCallbackRecord $record): ResendDepositCallbackData`

Demande à PawaPay de renvoyer le webhook d'un dépôt.

| Paramètre | Type | Requis | Défaut | Description |
|-----------|------|--------|--------|-------------|
| `$record` | `ResendDepositCallbackRecord` | ✅ | - | Contient le `depositId` à relancer |

### `createPaymentPage(CreatePaymentPageRecord $record): CreatePaymentPageData`

Crée une page de paiement hébergée par PawaPay et renvoie l'URL de redirection.

| Paramètre | Type | Requis | Défaut | Description |
|-----------|------|--------|--------|-------------|
| `$record` | `CreatePaymentPageRecord` | ✅ | - | depositId, returnUrl, amountDetails, phoneNumber, language, country, customerMessage, metadata |

## Méthodes internes

| Méthode | Visibilité | Description |
|---------|-----------|-------------|
| `failureReasonToData(?FailureReasonStruct): ?FailureReasonData` | `private` | Convertit un `FailureReasonStruct` en `FailureReasonData`, ou `null` si absent |

## Exemples d'utilisation

### Exemple 1 : Instanciation via la factory

```php
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Services\PawapayService;

$service = PawapayService::create(
    apiToken: config('pawapay.api_token'),
    baseUrl: PawaPayBaseUrl::SANDBOX,
);
```

### Exemple 2 : Initier un dépôt

```php
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;

$data = $service->initiateDeposit(
    InitiateDepositRecord::from([
        'depositId' => $uuid,
        'payer' => $payer,
        'amount' => $amount,
        'currency' => $currency,
        'clientReferenceId' => 'ORDER-123',
        'customerMessage' => $message,
    ]),
);

if ($data->isAccepted) {
    // stocker $data->depositId en base
}
```

### Exemple 3 : Vérifier un dépôt

```php
use AndyDefer\PhpPawapay\Records\CheckDepositStatusRecord;

$data = $service->checkDepositStatus(
    CheckDepositStatusRecord::from(['depositId' => $uuid]),
);

if ($data->isFound && $data->depositData?->status->isCompleted()) {
    // paiement confirmé
}
```

## Gestion des erreurs

Le service ne lève pas d'exception métier : toute erreur PawaPay ou réseau se traduit par un `FailureReasonData` non-null dans la `Data` de retour.

| Situation | Champ | Message typique |
|-----------|-------|-----------------|
| Token invalide | `failureReason->failureCode` = `FailureCode::AUTHENTICATION_ERROR` | `The API token in the request is invalid.` |
| Token non autorisé | `FailureCode::AUTHORISATION_ERROR` | `The API token in the request is not authorised for this endpoint.` |
| Provider indisponible | `FailureCode::PROVIDER_TEMPORARILY_UNAVAILABLE` | `The provider 'XXX' is currently not able to process payments.` |
| Téléphone invalide | `FailureCode::INVALID_PHONE_NUMBER` | `The phone number 'XXX' seems to be invalid for the provider 'YYY'.` |
| Devise non supportée | `FailureCode::INVALID_CURRENCY` | `The currency 'XXX' is not supported with provider 'YYY'.` |
| Montant hors bornes | `FailureCode::AMOUNT_OUT_OF_BOUNDS` | `The amount needs to be more than 'X' and less than 'Y' for provider 'ZZZ'.` |
| Erreur inconnue | `FailureCode::UNKNOWN_ERROR` | `Unable to process request due to an unknown problem.` |

## Intégration

- **Appel direct** : injecter `PawapayService` dans un contrôleur, un job, un listener.
- **Conteneur Laravel** : binder `PawapayInterface` sur `PawapayService` et laisser l'injection automatique faire le reste.
- **Adaptateur** : si tu veux isoler le métier de PawaPay (utile pour les tests d'intégration ou changer de fournisseur), tu peux interposer un `LaravelPaymentGateway` qui implémente la même interface.
- **Records / Data** : les Records viennent de ton domaine (contrôleur, formulaire) ; les Data retournées sont ce que tu renvoies au client ou que tu persistes.

## Performance

- **Une instance par appel** ou **singleton** : `PawapayService` est stateless, il peut être enregistré en `singleton` dans le conteneur sans risque.
- **Pas de cache interne** : chaque appel correspond à un appel HTTP. Si tu veux cacher les résultats, fais-le à un niveau supérieur (Repository, Cache Laravel).
- **Coût de conversion** : les `Record → VO` et `Response → Data` sont des `from()` légers, aucune requête supplémentaire.
- **Testabilité** : `PawapayClientInterface` est injectable, donc le service peut être testé avec un mock (voir `MockPawapayClient`).

## Compatibilité

| Version | Support |
|---------|---------|
| PHP 8.1+ | ✅ Complet (`readonly`, enums, `never`) |
| PHP 8.4 | ✅ Complet |
| PHP 8.5 | ✅ Complet (testé sur ta stack) |

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
use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\ReferenceVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

$service = PawapayService::create(
    apiToken: 'sandbox-token',
    baseUrl: PawaPayBaseUrl::SANDBOX,
);

$accountDetails = AccountDetailsVO::from([
    'phoneNumber' => PhoneNumberVO::from('243812345678'),
    'provider' => Provider::VODACOM_MPESA_COD,
]);

$payer = PayerVO::from([
    'type' => PayerType::MMO,
    'accountDetails' => $accountDetails,
]);

$data = $service->initiateDeposit(
    InitiateDepositRecord::from([
        'depositId' => UuidVO::from('9b724dbf-32a7-4e63-96bb-59a4747e43ca'),
        'payer' => $payer,
        'amount' => AmountVO::from(25.50),
        'currency' => Currency::USD,
        'clientReferenceId' => 'REF-RDC-123456',
        'customerMessage' => CustomerMessageVO::from('Paiement commande'),
    ]),
);

echo $data->depositId;    // 9b724dbf-...
echo $data->status->value; // ACCEPTED
```

## Voir aussi

- `PawapayInterface` — contrat public du service
- `PawapayClientInterface` — client HTTP bas niveau
- `InitiateDepositRecord` / `InitiateDepositData` — entrée/sortie de `initiateDeposit`
- `CheckDepositStatusRecord` / `CheckDepositStatusData` — entrée/sortie de `checkDepositStatus`
- `ResendDepositCallbackRecord` / `ResendDepositCallbackData` — entrée/sortie de `resendDepositCallback`
- `CreatePaymentPageRecord` / `CreatePaymentPageData` — entrée/sortie de `createPaymentPage`
- `FailureReasonData` — représentation unifiée des erreurs PawaPay