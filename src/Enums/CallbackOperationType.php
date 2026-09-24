<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Enums;

use InvalidArgumentException;

enum CallbackOperationType: string
{
    case DEPOSIT = 'deposit';
    case PAYOUT = 'payout';
    case REFUND = 'refund';
    case CHECKOUT = 'checkout';

    /**
     * Détecte l'opération à partir du payload brut.
     * checkoutId est vérifié en premier : un checkout callback
     * contient aussi un objet "deposit".
     *
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        return match (true) {
            array_key_exists('checkoutId', $payload) => self::CHECKOUT,
            array_key_exists('depositId', $payload) => self::DEPOSIT,
            array_key_exists('payoutId', $payload) => self::PAYOUT,
            array_key_exists('refundId', $payload) => self::REFUND,
            default => throw new InvalidArgumentException(
                'Unknown Pawapay callback payload: no discriminating field found.'
            ),
        };
    }
}
