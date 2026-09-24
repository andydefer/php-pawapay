<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Builders;

use AndyDefer\PhpPawapay\Contracts\Callbacks\HandlesCallbacksInterface;
use AndyDefer\PhpPawapay\Enums\CallbackOperationType;
use AndyDefer\PhpPawapay\Structures\Callbacks\CheckoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\PayoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\RefundCallbackStruct;
use InvalidArgumentException;

final class CallbackBuilder
{
    private ?HandlesCallbacksInterface $handler = null;

    public function withHandler(HandlesCallbacksInterface $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Détecte l'opération depuis le payload, hydrate le bon Struct,
     * puis exécute la méthode correspondante du handler.
     *
     * @param  array<string, mixed>  $payload
     */
    public function execute(array $payload): void
    {
        if ($this->handler === null) {
            throw new InvalidArgumentException('Callback handler is required.');
        }

        $operation = CallbackOperationType::fromPayload($payload);

        match ($operation) {
            CallbackOperationType::DEPOSIT => $this->handler->handleDeposit(DepositCallbackStruct::from($payload)),
            CallbackOperationType::PAYOUT => $this->handler->handlePayout(PayoutCallbackStruct::from($payload)),
            CallbackOperationType::REFUND => $this->handler->handleRefund(RefundCallbackStruct::from($payload)),
            CallbackOperationType::CHECKOUT => $this->handler->handleCheckout(CheckoutCallbackStruct::from($payload)),
        };
    }

    public static function create(): self
    {
        return new self;
    }
}
