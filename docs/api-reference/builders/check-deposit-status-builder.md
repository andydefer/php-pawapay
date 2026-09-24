# CheckDepositStatusBuilder - Référence Technique

## Description

Construit et exécute une requête de vérification du statut d'un dépôt auprès de l'API Pawapay.

## Hiérarchie / Implémentations

```
CheckDepositStatusBuilder (final class)
```

Aucune interface implémentée, aucune classe parente. Classe autonome.

## Rôle principal

`CheckDepositStatusBuilder` offre une API fluide pour interroger le statut d'un dépôt existant à partir de son identifiant UUID.

Il encapsule :

1. la configuration du client HTTP (token, URL de base),
2. l'identifiant du dépôt à interroger,
3. la construction du `PawapayClient` sous-jacent,
4. l'appel réseau et la récupération de la réponse typée.

Il est destiné aux cas où l'on souhaite vérifier manuellement un statut (polling, réconciliation, re-check après webhook manquant) sans passer par la façade `PawapayService`.

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
$builder = new CheckDepositStatusBuilder('votre-token-api');
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
$builder = CheckDepositStatusBuilder::create('votre-token-api');
```

---

### `withBaseUrl(PawaPayBaseUrl $baseUrl): self`

Définit l'environnement Pawapay à utiliser (sandbox ou production).

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

Définit l'identifiant UUID du dépôt à interroger.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$depositId` | `string` | UUID v4 du dépôt tel que retourné par Pawapay lors de l'initiation |

**Retourne :** `self` — Instance courante pour chaînage.

**Exemple :**

```php
$builder->withDepositId('f4401bd2-1568-4140-bf2d-eb77d2b2b639');
```

---

### `build(): PawapayClient`

Construit le client HTTP Pawapay à partir de la configuration courante.

**Retourne :** `PawapayClient` — Client bas niveau prêt à l'emploi.

**Exceptions :** Aucune. La validation de l'UUID se produit lors de l'appel à `execute()`.

**Exemple :**

```php
$client = $builder->build();
```

---

### `execute(): CheckDepositStatusResponse`

Construit le client et exécute l'appel HTTP `GET /v2/deposits/{depositId}`.

**Retourne :** `CheckDepositStatusResponse` — Réponse typée contenant le statut de recherche et les données du dépôt.

**Exceptions :**
- `InvalidArgumentException` — si `$depositId` n'est pas un UUID valide (levée par `UuidVO` lors de la construction de la requête).
- `InvalidArgumentException` — si `$depositId` n'a pas été défini (propriété non initialisée).
- Exception réseau de Guzzle — si la requête échoue au niveau transport.

**Exemple :**

```php
$response = $builder->execute();
```

---

## Cas d'utilisation

### Cas 1 : Vérification simple après initiation

Après avoir initié un dépôt, vérifier son statut une première fois.

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\CheckDepositStatusBuilder;

$response = CheckDepositStatusBuilder::create('votre-token')
    ->withDepositId('f4401bd2-1568-4140-bf2d-eb77d2b2b639')
    ->execute();

if ($response->isFound()) {
    $status = $response->getDepositData()->status;
    echo $status->value; // 'PROCESSING', 'COMPLETED', etc.
}
```

### Cas 2 : Re-check après webhook manquant

Un webhook n'a pas été reçu, on interroge activement Pawapay.

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\CheckDepositStatusBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

$response = CheckDepositStatusBuilder::create(config('pawapay.api_token'))
    ->withBaseUrl(PawaPayBaseUrl::PRODUCTION)
    ->withDepositId($order->deposit_id)
    ->execute();

if ($response->isFound() && $response->getDepositData()->status->isCompleted()) {
    $order->markAsPaid();
}
```

### Cas 3 : Job de polling avec backoff

Vérification périodique tant que le statut n'est pas final.

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\CheckDepositStatusBuilder;

$response = CheckDepositStatusBuilder::create($token)
    ->withDepositId($order->deposit_id)
    ->execute();

if (! $response->isFound()) {
    return;
}

$status = $response->getDepositData()->status;

if ($status->isFinal()) {
    $status->isCompleted() ? $order->markAsPaid() : $order->markAsFailed();
    return;
}

// Replanifier
CheckDepositJob::dispatch($order)->delay(now()->addMinutes(2));
```

## Flux d'exécution

```
CheckDepositStatusBuilder::create($token)
    │
    ├── withBaseUrl(PawaPayBaseUrl)      // optionnel, défaut SANDBOX
    │
    ├── withDepositId($uuid)             // obligatoire
    │
    └── execute()
            │
            ├── build() → new PawapayClient($token, $baseUrl)
            │
            ├── $client->checkDepositStatus($depositId)
            │       │
            │       ├── new CheckDepositStatusRequest($depositId, $baseUrl)
            │       │       └── UuidVO::from($depositId)  // validation
            │       │
            │       ├── headers: Authorization, Accept
            │       ├── options: timeout=30, connectTimeout=10, httpErrors=false
            │       │
            │       └── ClientService::get(...)
            │
            └── CheckDepositStatusResponse
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| `$depositId` non défini | `Error` (PHP) | `Typed property must not be accessed before initialization` |
| UUID invalide | `InvalidArgumentException` | `Invalid UUID: xxx` |
| Token expiré / invalide | Pas d'exception | `$response->hasFailureReason()` retourne `true` |
| Dépôt inexistant | Pas d'exception | `$response->isNotFound()` retourne `true` |
| Erreur réseau | `GuzzleException` | Message dépendant de Guzzle |

## Intégration

| Composant | Rôle |
|-----------|------|
| `PawapayClient` | Client HTTP bas niveau construit via `build()` |
| `PawaPayBaseUrl` | Enum de l'URL de base (sandbox / production) |
| `CheckDepositStatusRequest` | Requête HTTP générée en interne par le client |
| `CheckDepositStatusResponse` | Réponse typée retournée par `execute()` |
| `DepositDataStruct` | Struct accessible via `$response->getDepositData()` |

Le builder est **synchrone** et **bloquant**. Pour une utilisation asynchrone, l'injecter dans un job de queue et appeler `execute()` depuis le job.

## Performance

- **Une seule requête HTTP** par appel à `execute()`.
- `build()` instancie un nouveau `PawapayClient` à chaque appel — pas de pooling.
- Aucun cache interne.
- Le coût dominant est le round-trip réseau vers Pawapay (typiquement 100–500 ms).

Pour des vérifications en masse, préférer un job batch avec un client partagé plutôt que N builders indépendants.

## Compatibilité

| Version PHP | Support |
|-------------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.0 | ✅ Complet |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Builders\CheckDepositStatusBuilder;
use AndyDefer\PhpPawapay\Enums\DepositStatus;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

$response = CheckDepositStatusBuilder::create('eyJraWQiOiIx...')
    ->withBaseUrl(PawaPayBaseUrl::SANDBOX)
    ->withDepositId('60bd6a3d-177e-4ec2-a65c-d622ede29c99')
    ->execute();

if (! $response->isFound()) {
    echo "Dépôt introuvable\n";
    exit;
}

$deposit = $response->getDepositData();

echo match (true) {
    $deposit->status->isCompleted() => "Dépôt confirmé\n",
    $deposit->status->isFailed()    => "Dépôt échoué\n",
    $deposit->status->isPending()   => "Dépôt en cours\n",
    default                         => "Statut inconnu : {$deposit->status->value}\n",
};

if ($response->hasFailureReason()) {
    $reason = $response->getFailureReason();
    echo "Erreur : {$reason->failureCode->value} - {$reason->failureMessage}\n";
}
```

## Voir aussi

- `PawapayClient` - Client HTTP sous-jacent
- `CheckDepositStatusResponse` - Réponse typée de l'appel
- `CheckDepositStatusRequest` - Requête HTTP générée
- `PawaPayBaseUrl` - Enum sandbox / production
- `DepositStatus` - Enum des statuts de dépôt
- `DepositDataStruct` - Struct des données du dépôt