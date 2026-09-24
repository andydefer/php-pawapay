# ResendDepositCallbackBuilder - Référence Technique

## Description

Construit et exécute une requête demandant à Pawapay de renvoyer le callback d'un dépôt existant.

## Hiérarchie / Implémentations

```
ResendDepositCallbackBuilder (final class)
```

Aucune interface implémentée, aucune classe parente. Classe autonome.

## Rôle principal

`ResendDepositCallbackBuilder` offre une API fluide pour demander à Pawapay de **renvoyer** le callback associé à un dépôt donné.

Il est utilisé lorsque :

1. votre webhook a échoué (timeout, HTTP 500, indisponibilité),
2. un callback a été manqué pour une raison quelconque,
3. vous souhaitez forcer une resynchronisation d'état.

Il encapsule :

1. la configuration du client HTTP (token, URL de base),
2. l'identifiant UUID du dépôt concerné,
3. la construction du `PawapayClient` sous-jacent,
4. l'appel réseau et la récupération de la réponse typée.

Ce builder est **à préférer au polling** : au lieu d'interroger plusieurs fois le statut, on demande à Pawapay de rejouer le callback.

## Installation

Aucune installation spécifique. La classe est fournie par le package `andydefer/php-pawapay`.

```bash
composer require andydefer/php-pawapay
```

## API / Méthodes publiques

### `__construct(string $apiToken)`

Initialise le builder avec un token d'API Pawapay.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$apiToken` | `string` | Token d'authentification Bearer fourni par Pawapay |

**Retourne :** Aucun (constructeur).

**Note :** La `baseUrl` est initialisée à `PawaPayBaseUrl::SANDBOX` par défaut.

**Exemple :**

```php
$builder = new ResendDepositCallbackBuilder('votre-token-api');
```

---

### `create(string $apiToken): self`

Factory statique. Équivalent au constructeur.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$apiToken` | `string` | Token d'authentification Bearer |

**Retourne :** `self` — Nouvelle instance.

**Exemple :**

```php
$builder = ResendDepositCallbackBuilder::create('votre-token-api');
```

---

### `withBaseUrl(PawaPayBaseUrl $baseUrl): self`

Définit l'environnement Pawapay à utiliser.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$baseUrl` | `PawaPayBaseUrl` | Enum de l'URL de base (`SANDBOX` ou `PRODUCTION`) |

**Retourne :** `self` — Instance courante pour chaînage.

**Exemple :**

```php
$builder->withBaseUrl(PawaPayBaseUrl::PRODUCTION);
```

---

### `withDepositId(string $depositId): self`

Définit l'identifiant UUID du dépôt dont on veut renvoyer le callback.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$depositId` | `string` | UUID v4 du dépôt tel que retourné par Pawapay |

**Retourne :** `self` — Instance courante pour chaînage.

**Exemple :**

```php
$builder->withDepositId('9b724dbf-32a7-4e63-96bb-59a4747e43ca');
```

---

### `build(): PawapayClient`

Construit le client HTTP Pawapay à partir de la configuration courante.

**Retourne :** `PawapayClient` — Client bas niveau prêt à l'emploi.

**Exceptions :** Aucune.

**Exemple :**

```php
$client = $builder->build();
```

---

### `execute(): ResendDepositCallbackResponseInterface`

Construit le client et exécute l'appel HTTP `POST /v2/deposits/resend-callback/{depositId}`.

**Retourne :** `ResendDepositCallbackResponseInterface` — Réponse typée contenant le statut de la demande de renvoi.

**Exceptions :**
- `InvalidArgumentException` — si `$depositId` n'a pas été défini (propriété non initialisée).
- Exception réseau de Guzzle — si la requête échoue au niveau transport.

**Exemple :**

```php
$response = $builder->execute();
```

---

## Cas d'utilisation

### Cas 1 : Renvoi après échec webhook

Votre endpoint webhook a renvoyé une erreur, vous demandez un nouveau callback.

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\ResendDepositCallbackBuilder;

$response = ResendDepositCallbackBuilder::create('votre-token')
    ->withDepositId($order->deposit_id)
    ->execute();

if ($response->isAccepted()) {
    Log::info('Callback renvoyé');
} else {
    Log::error('Renvoyer callback échoué', [
        'code' => $response->getFailureReason()?->failureCode->value,
    ]);
}
```

### Cas 2 : Job de récupération avec backoff

Replanification automatique en cas d'échec réseau.

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use AndyDefer\PhpPawapay\Builders\ResendDepositCallbackBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

final class ResendPawapayCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 5;

    public function __construct(
        public readonly string $depositId,
    ) {}

    public function handle(): void
    {
        $response = ResendDepositCallbackBuilder::create(config('pawapay.api_token'))
            ->withBaseUrl(PawaPayBaseUrl::PRODUCTION)
            ->withDepositId($this->depositId)
            ->execute();

        if ($response->isAccepted()) {
            return;
        }

        $this->release(now()->addMinutes(2 ** $this->attempts()));
    }
}
```

### Cas 3 : Commande artisan de réconciliation

Renvoi en masse des callbacks pour un lot de dépôts.

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use AndyDefer\PhpPawapay\Builders\ResendDepositCallbackBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use Illuminate\Console\Command;

final class ResendPawapayCallbacksCommand extends Command
{
    protected $signature = 'pawapay:resend-callbacks';

    public function handle(): int
    {
        $depositIds = Deposit::query()
            ->whereNull('webhook_received_at')
            ->where('created_at', '<', now()->subHours(2))
            ->pluck('deposit_id');

        foreach ($depositIds as $depositId) {
            ResendDepositCallbackBuilder::create(config('pawapay.api_token'))
                ->withBaseUrl(PawaPayBaseUrl::PRODUCTION)
                ->withDepositId($depositId)
                ->execute();
        }

        return self::SUCCESS;
    }
}
```

## Flux d'exécution

```
ResendDepositCallbackBuilder::create($token)
    │
    ├── withBaseUrl(PawaPayBaseUrl)     // optionnel, défaut SANDBOX
    │
    ├── withDepositId($uuid)            // obligatoire
    │
    └── execute()
            │
            ├── build() → new PawapayClient($token, $baseUrl)
            │
            ├── $client->resendDepositCallback($depositId)
            │       │
            │       ├── new ResendDepositCallbackRequest($depositId, $baseUrl)
            │       │
            │       ├── headers: Authorization, Content-Type, Accept
            │       ├── options: timeout=30, connectTimeout=10, httpErrors=false
            │       │
            │       └── ClientService::post(...)
            │
            └── ResendDepositCallbackResponse
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| `$depositId` non défini | `Error` (PHP) | `Typed property must not be accessed before initialization` |
| Token expiré / invalide | Pas d'exception | `$response->hasFailureReason()` retourne `true` |
| Dépôt inexistant | Pas d'exception | `$response->isRejected()` retourne `true` |
| Dépôt trop récent | Pas d'exception | Pawapay rejette — consulter `failureReason` |
| Erreur réseau | `GuzzleException` | Message dépendant de Guzzle |

## Intégration

| Composant | Rôle |
|-----------|------|
| `PawapayClient` | Client HTTP bas niveau construit via `build()` |
| `PawaPayBaseUrl` | Enum de l'URL de base (sandbox / production) |
| `ResendDepositCallbackRequest` | Requête HTTP générée en interne par le client |
| `ResendDepositCallbackResponse` | Réponse typée retournée par `execute()` |
| `ResendCallbackStatus` | Enum du statut (`ACCEPTED`, `REJECTED`) |

Le builder est **synchrone** et **bloquant**. À utiliser dans un job de queue pour ne pas bloquer le cycle HTTP.

## Performance

- **Une seule requête HTTP** par appel à `execute()`.
- `build()` instancie un nouveau `PawapayClient` à chaque appel.
- Aucun cache interne.
- Le coût dominant est le round-trip réseau vers Pawapay (typiquement 100–500 ms).

Pawapay applique un **rate limit** sur cet endpoint. Espacer les appels (backoff exponentiel recommandé) et ne pas boucler agressivement.

## Compatibilité

| Version PHP | Support |
|-------------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.0 | ✅ Complet |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\ResendDepositCallbackBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

$response = ResendDepositCallbackBuilder::create('eyJraWQiOiIx...')
    ->withBaseUrl(PawaPayBaseUrl::PRODUCTION)
    ->withDepositId('9b724dbf-32a7-4e63-96bb-59a4747e43ca')
    ->execute();

if ($response->isAccepted()) {
    echo "Callback renvoyé avec succès\n";
    exit;
}

if ($response->isRejected()) {
    $reason = $response->getFailureReason();
    echo "Rejeté : {$reason?->failureCode->value} - {$reason?->failureMessage}\n";
    exit;
}

echo "Statut indéterminé\n";
```

## Voir aussi

- `PawapayClient` - Client HTTP sous-jacent
- `ResendDepositCallbackResponse` - Réponse typée de l'appel
- `ResendDepositCallbackRequest` - Requête HTTP générée
- `PawaPayBaseUrl` - Enum sandbox / production
- `ResendCallbackStatus` - Enum du statut de renvoi
- `CheckDepositStatusBuilder` - Alternative : vérification directe du statut