# Predict Provider - Référence Technique

## 📖 Description

L'endpoint `POST /v2/predict-provider` permet de déterminer le pays et le fournisseur Mobile Money associés à un numéro de téléphone. Il est utilisé avant un dépôt ou un payout pour résoudre dynamiquement le `provider` et le `country` à utiliser, évitant à l'intégrateur de mapper manuellement les préfixes téléphoniques.

## 🔗 Endpoint

```
POST /v2/predict-provider
```

### Paramètres du corps

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `phoneNumber` | `string` | ✅ Oui | Numéro de téléphone (format international, avec ou sans espaces/tirets) |

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
POST /v2/predict-provider
Authorization: Bearer your-api-token
Content-Type: application/json
Accept: application/json

{
  "phoneNumber": "+260 763-456789"
}
```

---

## 📊 Structure de la réponse

### Succès (200 OK)

```json
{
  "country": "ZMB",
  "provider": "MTN_MOMO_ZMB",
  "phoneNumber": "260763456789"
}
```

### Erreur - INVALID_INPUT (400 Bad Request)

```json
{
  "failureReason": {
    "failureCode": "INVALID_INPUT",
    "failureMessage": "We are unable to parse the body of the request. Please consult API documentation for valid request payload."
  }
}
```

### Erreur - AUTHENTICATION_ERROR (401 Unauthorized)

```json
{
  "failureReason": {
    "failureCode": "AUTHENTICATION_ERROR",
    "failureMessage": "The API token in the request is invalid."
  }
}
```

### Erreur - AUTHORISATION_ERROR (403 Forbidden)

```json
{
  "failureReason": {
    "failureCode": "AUTHORISATION_ERROR",
    "failureMessage": "The API token in the request is not authorised for this endpoint."
  }
}
```

### Erreur - INVALID_COUNTRY (422 Unprocessable Entity)

```json
{
  "failureReason": {
    "failureCode": "INVALID_COUNTRY",
    "failureMessage": "The country of the provided phone number is not supported."
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
| `country` | `string` | Code pays ISO 3166-1 alpha-3 (`ZMB`, `COD`, `KEN`, etc.) |
| `provider` | `string` | Identifiant du provider Mobile Money (`MTN_MOMO_ZMB`, `VODACOM_MPESA_COD`, etc.) |
| `phoneNumber` | `string` | Numéro normalisé au format E.164 sans `+` |

En cas d'erreur, la réponse contient uniquement un objet `failureReason` :

| Champ | Type | Description |
|-------|------|-------------|
| `failureReason.failureCode` | `string` | Code d'erreur |
| `failureReason.failureMessage` | `string` | Message d'erreur |

---

## 💻 Utilisation avec le SDK

### Exemple avec Builder (recommandé)

```php
<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpPawapay\Builders\PredictProviderBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

// 1. Prédire le provider avec le Builder
$response = PredictProviderBuilder::create(
    apiToken: 'your-api-token-here'
)
    ->withBaseUrl(PawaPayBaseUrl::SANDBOX)
    ->withPhoneNumber('260763456789')
    ->execute();

// 2. Traiter la réponse
if ($response->isFound()) {
    echo "✅ Provider identifié !\n";
    echo 'Pays: ' . $response->getCountry()->value . "\n";
    echo 'Provider: ' . $response->getProvider()->value . "\n";
    echo 'Numéro normalisé: ' . $response->getPhoneNumber()->getValue() . "\n";
}

if ($response->hasFailureReason()) {
    $failure = $response->getFailureReason();
    echo '❌ Erreur: ' . $failure->failureCode->value . "\n";
    echo 'Message: ' . $failure->failureMessage . "\n";
}
```

### Exemple sans Builder (client bas niveau)

```php
<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

$client = new PawapayClient(
    apiToken: 'your-api-token-here',
    baseUrl: PawaPayBaseUrl::SANDBOX
);

$response = $client->predictProvider(
    PhoneNumberVO::from('260763456789')
);

if ($response->isFound()) {
    echo "✅ Provider identifié !\n";
    echo 'Pays: ' . $response->getCountry()->value . "\n";
    echo 'Provider: ' . $response->getProvider()->value . "\n";
}
```

### Exemple via la façade PawapayService

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Records\PredictProviderRecord;
use AndyDefer\PhpPawapay\Services\PawapayService;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

$service = PawapayService::create(
    apiToken: 'your-api-token-here',
    baseUrl: PawaPayBaseUrl::SANDBOX,
);

$data = $service->predictProvider(
    PredictProviderRecord::from([
        'phoneNumber' => PhoneNumberVO::from('260763456789'),
    ]),
);

if ($data->isFound) {
    echo 'Provider: ' . $data->provider->value . "\n";
    echo 'Pays: ' . $data->country->value . "\n";
    echo 'Numéro normalisé: ' . $data->phoneNumber->getValue() . "\n";
} elseif ($data->hasFailureReason) {
    echo 'Erreur: ' . $data->failureReason->failureCode->value . "\n";
}
```

---

## 🌍 Providers et pays supportés

Exemples de combinaisons valides :

| Numéro | Pays | Provider |
|--------|------|----------|
| `260763456789` | `ZMB` | `MTN_MOMO_ZMB` |
| `260973024456` | `ZMB` | `MTN_MOMO_ZMB` |
| `243812345678` | `COD` | `VODACOM_MPESA_COD` |
| `254712345678` | `KEN` | `MPESA_KEN` |
| `2250700000000` | `CIV` | `ORANGE_CIV` |
| `237670000000` | `CMR` | `MTN_MOMO_CMR` |

> **Note :** un numéro de pays non supporté par PawaPay déclenche une erreur `INVALID_COUNTRY`.

---

## 🧩 Codes d'erreur spécifiques

| Code | Signification |
|------|---------------|
| `INVALID_INPUT` | Payload malformé ou numéro absent |
| `AUTHENTICATION_ERROR` | Token API invalide |
| `AUTHORISATION_ERROR` | Token non autorisé sur cet endpoint |
| `INVALID_COUNTRY` | Pays du numéro non supporté par PawaPay |
| `INVALID_PHONE_NUMBER` | Numéro invalide ou mal formé |
| `UNKNOWN_ERROR` | Erreur générique côté PawaPay |

---

## 🧪 Tests

```bash
./vendor/bin/phpunit --filter PredictProvider
```

### Exemple de test

```php
public function test_predict_provider_found_returns_data(): void
{
    $this->client->addSuccessResponse([
        'country' => 'ZMB',
        'provider' => 'MTN_MOMO_ZMB',
        'phoneNumber' => '260763456789',
    ]);

    $response = $this->client->predictProvider(
        PhoneNumberVO::from('260763456789'),
    );

    $this->assertTrue($response->isFound());
    $this->assertSame(Country::ZMB, $response->getCountry());
    $this->assertSame(Provider::MTN_MOMO_ZMB, $response->getProvider());
    $this->assertSame('260763456789', $response->getPhoneNumber()->getValue());
}
```

---

## 📝 Notes importantes

1. **Normalisation automatique** : le SDK accepte un numéro avec `+`, espaces ou tirets. PawaPay le normalise et le renvoie au format E.164 sans `+`.

2. **Stateless** : cet endpoint ne persiste rien. Il résout simplement le mapping numéro → pays/provider.

3. **Utilisation recommandée** : appeler `predict-provider` avant `initiateDeposit` lorsqu'on ne connaît pas le provider du client. Cela évite de deviner ou de demander à l'utilisateur.

4. **Pays non supportés** : un numéro d'un pays non couvert déclenche `INVALID_COUNTRY`. Prévoir un fallback UX (ex. message « paiement non disponible dans votre pays »).

5. **Timeout** : le SDK utilise un timeout de 30 secondes par défaut, comme pour les autres endpoints.

6. **Pas de rate limit spécifique documenté**, mais rester raisonnable en cas d'appel en masse — préférer un cache côté application (mapping numéro → provider).

---

## 🔗 Voir aussi

- [Initiate Deposit - Référence Technique](./initiate-deposit.md)
- [Check Deposit Status - Référence Technique](./check-deposit-status.md)
- [Create Payment Page - Référence Technique](./create-payment-page.md)
- [Documentation officielle PawaPay](https://docs.pawapay.io)
- [API Reference - Toolkit](https://docs.pawapay.io/api/toolkit)
- [SDK GitHub](https://github.com/andydefer/php-pawapay)