# CallbackBuilder - Référence Technique

## Description

Construit et exécute le dispatch d'un callback Pawapay vers la méthode de handler correspondant au type d'opération détecté dans le payload.

## Hiérarchie / Implémentations

```
CallbackBuilder (final class)
```

Aucune interface implémentée, aucune classe parente. Classe autonome.

## Rôle principal

`CallbackBuilder` est le point d'entrée unique pour traiter un callback Pawapay. Il :

1. détecte le type d'opération à partir du payload brut,
2. hydrate le `Struct` correspondant,
3. invoque la méthode adaptée sur le handler fourni.

Il masque à l'utilisateur la logique de discrimination (`checkoutId` avant `depositId`, etc.) et le `match` sur les types d'opération.

## Installation

Aucune installation spécifique. La classe est fournie par le package `andydefer/php-pawapay`.

```bash
composer require andydefer/php-pawapay
```

## API / Méthodes publiques

### `withHandler(HandlesCallbacksInterface $handler): self`

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$handler` | `HandlesCallbacksInterface` | Handler qui recevra le `Struct` hydraté |

**Retourne :** `self` — Instance courante pour chaînage.

**Exceptions :** Aucune.

**Exemple :**

```php
$builder = CallbackBuilder::create()
    ->withHandler($handler);
```

---

### `execute(array $payload): void`

Détecte l'opération, hydrate le `Struct` et invoque la méthode correspondante du handler.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$payload` | `array<string, mixed>` | Payload brut reçu de Pawapay |

**Retourne :** `void`

**Exceptions :**
- `InvalidArgumentException` — si `withHandler()` n'a pas été appelé.
- `InvalidArgumentException` — si le payload ne contient aucun champ discriminant (`checkoutId`, `depositId`, `payoutId`, `refundId`).
- `InvalidArgumentException` (via hydratation) — si un champ requis du `Struct` est manquant ou invalide.

**Exemple :**

```php
CallbackBuilder::create()
    ->withHandler($handler)
    ->execute($payload);
```

---

### `create(): self`

Factory statique.

**Retourne :** `self` — Nouvelle instance.

**Exemple :**

```php
$builder = CallbackBuilder::create();
```

---

## Cas d'utilisation

### Cas 1 : Endpoint unique avec détection automatique

Pawapay envoie tous les callbacks sur la même route. Le payload détermine l'opération.

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\CallbackBuilder;

$payload = json_decode($request->getContent(), true);

CallbackBuilder::create()
    ->withHandler(app(HandlesCallbacksInterface::class))
    ->execute($payload);
```

### Cas 2 : Contrôleur Laravel complet

Réception d'un callback, vérification de signature, puis dispatch typé.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use AndyDefer\PhpPawapay\Builders\CallbackBuilder;
use AndyDefer\PhpPawapay\Contracts\Callbacks\HandlesCallbacksInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PawapayCallbackController
{
    public function __invoke(Request $request, HandlesCallbacksInterface $handler): JsonResponse
    {
        $payload = $request->json()->all();

        // Vérification de signature à faire AVANT (hors périmètre)

        CallbackBuilder::create()
            ->withHandler($handler)
            ->execute($payload);

        return response()->json(['status' => 'ok']);
    }
}
```

### Cas 3 : Handler ciblé sur une seule opération

Un handler qui ne traite que les dépôts, les autres méthodes restent vides.

```php
<?php

declare(strict_types=1);

namespace App\Services;

use AndyDefer\PhpPawapay\Contracts\Callbacks\HandlesCallbacksInterface;
use AndyDefer\PhpPawapay\Structures\Callbacks\CheckoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\PayoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\RefundCallbackStruct;

final class DepositOnlyHandler implements HandlesCallbacksInterface
{
    public function handleDeposit(DepositCallbackStruct $struct): void
    {
        // traiter le dépôt uniquement
    }

    public function handlePayout(PayoutCallbackStruct $struct): void {}
    public function handleRefund(RefundCallbackStruct $struct): void {}
    public function handleCheckout(CheckoutCallbackStruct $struct): void {}
}
```

## Flux d'exécution

```
execute($payload)
    │
    ├── handler null ? → InvalidArgumentException
    │
    ├── CallbackOperationType::fromPayload($payload)
    │       │
    │       ├── checkoutId présent → CHECKOUT
    │       ├── depositId présent  → DEPOSIT
    │       ├── payoutId présent   → PAYOUT
    │       ├── refundId présent   → REFUND
    │       └── aucun              → InvalidArgumentException
    │
    └── match($operation)
            ├── DEPOSIT  → $handler->handleDeposit(DepositCallbackStruct::from($payload))
            ├── PAYOUT   → $handler->handlePayout(PayoutCallbackStruct::from($payload))
            ├── REFUND   → $handler->handleRefund(RefundCallbackStruct::from($payload))
            └── CHECKOUT → $handler->handleCheckout(CheckoutCallbackStruct::from($payload))
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| `withHandler()` non appelé | `InvalidArgumentException` | `Callback handler is required.` |
| Payload sans champ discriminant | `InvalidArgumentException` | `Unknown Pawapay callback payload: no discriminating field found.` |
| Champ requis manquant à l'hydratation | `InvalidArgumentException` | Message dépendant du `Struct` ciblé |
| Valeur invalide à l'hydratation | `InvalidArgumentException` | Message dépendant du Value Object ciblé |

## Intégration

`CallbackBuilder` s'intègre avec :

| Composant | Rôle |
|-----------|------|
| `CallbackOperationType` | Enum qui expose `fromPayload()` pour la détection |
| `HandlesCallbacksInterface` | Interface obligatoire pour le handler |
| `DepositCallbackStruct` | Struct hydraté pour l'opération `DEPOSIT` |
| `PayoutCallbackStruct` | Struct hydraté pour l'opération `PAYOUT` |
| `RefundCallbackStruct` | Struct hydraté pour l'opération `REFUND` |
| `CheckoutCallbackStruct` | Struct hydraté pour l'opération `CHECKOUT` |

Le builder ne connaît ni HTTP, ni framework, ni stockage. Il opère uniquement sur le payload et le handler. La vérification de signature et l'idempotence restent à la charge de l'appelant.

## Performance

- Détection via `array_key_exists` — O(1), pas de boucle.
- Un seul `match` par exécution — pas de chaîne de `if`.
- Hydratation unique du `Struct` ciblé — les autres ne sont jamais instanciés.
- Aucun état persistant, aucune allocation superflue.

## Compatibilité

| Version PHP | Support |
|-------------|---------|
| PHP 8.1+ | ✅ Complet (`enum`, `match`, `readonly` sur les Structs) |
| PHP 8.0 | ❌ Non supporté (enum requis) |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\CallbackBuilder;
use AndyDefer\PhpPawapay\Contracts\Callbacks\HandlesCallbacksInterface;
use AndyDefer\PhpPawapay\Structures\Callbacks\CheckoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\PayoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\RefundCallbackStruct;

$handler = new class implements HandlesCallbacksInterface {
    public function handleDeposit(DepositCallbackStruct $struct): void
    {
        echo "Deposit {$struct->depositId->getValue()} : {$struct->status->value}\n";
    }

    public function handlePayout(PayoutCallbackStruct $struct): void
    {
        echo "Payout {$struct->payoutId->getValue()}\n";
    }

    public function handleRefund(RefundCallbackStruct $struct): void
    {
        echo "Refund {$struct->refundId->getValue()}\n";
    }

    public function handleCheckout(CheckoutCallbackStruct $struct): void
    {
        echo "Checkout {$struct->checkoutId->getValue()}\n";
    }
};

$payload = [
    'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
    'status' => 'COMPLETED',
    'amount' => '123.00',
    'currency' => 'ZMW',
    'country' => 'ZMB',
    'payer' => [
        'type' => 'MMO',
        'accountDetails' => [
            'phoneNumber' => '260763456789',
            'provider' => 'MTN_MOMO_ZMB',
        ],
    ],
    'customerMessage' => 'To ACME company',
    'clientReferenceId' => 'REF-987654321',
    'created' => '2020-10-19T08:17:01Z',
    'providerTransactionId' => '12356789',
];

CallbackBuilder::create()
    ->withHandler($handler)
    ->execute($payload);

// Affiche : Deposit f4401bd2-1568-4140-bf2d-eb77d2b2b639 : COMPLETED
```

## Voir aussi

- `CallbackOperationType` - Enum de détection du type d'opération
- `HandlesCallbacksInterface` - Interface du handler
- `DepositCallbackStruct` - Struct du callback dépôt
- `PayoutCallbackStruct` - Struct du callback payout
- `RefundCallbackStruct` - Struct du callback refund
- `CheckoutCallbackStruct` - Struct du callback checkout