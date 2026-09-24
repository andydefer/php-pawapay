<?php

declare(strict_types=1);

namespace Tests\Unit\Builders;

use AndyDefer\PhpPawapay\Builders\CallbackBuilder;
use AndyDefer\PhpPawapay\Contracts\Callbacks\HandlesCallbacksInterface;
use AndyDefer\PhpPawapay\Structures\Callbacks\CheckoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\PayoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\RefundCallbackStruct;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CallbackBuilderTest extends TestCase
{
    private const DEPOSIT_PAYLOAD = [
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

    private const PAYOUT_PAYLOAD = [
        'payoutId' => '6f53f5f3-2f97-4879-8ed6-50072fe9d2fc',
        'status' => 'COMPLETED',
        'amount' => '100.00',
        'currency' => 'ZMW',
        'recipient' => [
            'type' => 'MMO',
            'accountDetails' => [
                'phoneNumber' => '260973024456',
                'provider' => 'MTN_MOMO_ZMB',
            ],
        ],
        'clientReferenceId' => 'REF-987654321',
        'customerMessage' => 'To ACME company',
        'providerTransactionId' => '12356789',
        'created' => '2025-01-15T10:35:00Z',
    ];

    private const REFUND_PAYLOAD = [
        'refundId' => 'a1b2c3d4-1111-2222-3333-444455556666',
        'status' => 'COMPLETED',
        'amount' => '100.00',
        'currency' => 'ZMW',
        'clientReferenceId' => 'REF-987654321',
        'created' => '2025-01-15T10:35:00Z',
    ];

    private const CHECKOUT_PAYLOAD = [
        'checkoutId' => 'afb57b93-7849-49aa-babb-4c3ccbfe3d79',
        'status' => 'COMPLETED',
        'depositStatus' => 'COMPLETED',
        'deposit' => [
            'depositId' => 'eac4d2f3-cf36-4a24-a9eb-7014c630f8f0',
            'status' => 'COMPLETED',
            'amount' => '100.00',
            'currency' => 'ZMW',
            'country' => 'ZMB',
            'payer' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => '260973024434',
                    'provider' => 'MTN_MOMO_ZMB',
                ],
            ],
            'customerMessage' => 'Checkout payment',
            'clientReferenceId' => 'REF-987654321',
            'created' => '2025-01-15T10:35:00Z',
            'providerTransactionId' => '12356789',
        ],
        'depositsHistory' => [
            [
                'depositId' => 'eac4d2f3-cf36-4a24-a9eb-7014c630f8f0',
                'status' => 'COMPLETED',
                'amount' => '100.00',
                'currency' => 'ZMW',
                'country' => 'ZMB',
                'payer' => [
                    'type' => 'MMO',
                    'accountDetails' => [
                        'phoneNumber' => '260973024434',
                        'provider' => 'MTN_MOMO_ZMB',
                    ],
                ],
                'customerMessage' => 'Checkout payment',
                'clientReferenceId' => 'REF-987654321',
                'created' => '2025-01-15T10:35:00Z',
                'providerTransactionId' => '12356789',
            ],
        ],
    ];

    public function test_it_throws_when_handler_is_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Callback handler is required.');

        CallbackBuilder::create()->execute(self::DEPOSIT_PAYLOAD);
    }

    public function test_it_throws_when_payload_has_no_discriminating_field(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown Pawapay callback payload: no discriminating field found.');

        CallbackBuilder::create()
            ->withHandler($this->spyHandler())
            ->execute(['status' => 'COMPLETED']);
    }

    public function test_it_dispatches_deposit_callback(): void
    {
        $handler = $this->spyHandler();

        CallbackBuilder::create()
            ->withHandler($handler)
            ->execute(self::DEPOSIT_PAYLOAD);

        $this->assertInstanceOf(DepositCallbackStruct::class, $handler->deposit);
        $this->assertSame('f4401bd2-1568-4140-bf2d-eb77d2b2b639', $handler->deposit->depositId->getValue());
        $this->assertNull($handler->payout);
        $this->assertNull($handler->refund);
        $this->assertNull($handler->checkout);
    }

    public function test_it_dispatches_payout_callback(): void
    {
        $handler = $this->spyHandler();

        CallbackBuilder::create()
            ->withHandler($handler)
            ->execute(self::PAYOUT_PAYLOAD);

        $this->assertInstanceOf(PayoutCallbackStruct::class, $handler->payout);
        $this->assertSame('6f53f5f3-2f97-4879-8ed6-50072fe9d2fc', $handler->payout->payoutId->getValue());
        $this->assertNull($handler->deposit);
        $this->assertNull($handler->refund);
        $this->assertNull($handler->checkout);
    }

    public function test_it_dispatches_refund_callback(): void
    {
        $handler = $this->spyHandler();

        CallbackBuilder::create()
            ->withHandler($handler)
            ->execute(self::REFUND_PAYLOAD);

        $this->assertInstanceOf(RefundCallbackStruct::class, $handler->refund);
        $this->assertSame('a1b2c3d4-1111-2222-3333-444455556666', $handler->refund->refundId->getValue());
        $this->assertNull($handler->deposit);
        $this->assertNull($handler->payout);
        $this->assertNull($handler->checkout);
    }

    public function test_it_dispatches_checkout_callback(): void
    {
        $handler = $this->spyHandler();

        CallbackBuilder::create()
            ->withHandler($handler)
            ->execute(self::CHECKOUT_PAYLOAD);

        $this->assertInstanceOf(CheckoutCallbackStruct::class, $handler->checkout);
        $this->assertSame('afb57b93-7849-49aa-babb-4c3ccbfe3d79', $handler->checkout->checkoutId->getValue());
        $this->assertCount(1, $handler->checkout->depositsHistory);
        $this->assertNull($handler->deposit);
        $this->assertNull($handler->payout);
        $this->assertNull($handler->refund);
    }

    public function test_it_prefers_checkout_when_payload_contains_deposit_id(): void
    {
        $handler = $this->spyHandler();

        CallbackBuilder::create()
            ->withHandler($handler)
            ->execute(self::CHECKOUT_PAYLOAD);

        $this->assertInstanceOf(CheckoutCallbackStruct::class, $handler->checkout);
        $this->assertNull($handler->deposit);
    }

    /**
     * Retourne un handler espion qui capture la méthode appelée et le Struct reçu.
     */
    private function spyHandler(): HandlesCallbacksInterface
    {
        return new class implements HandlesCallbacksInterface
        {
            public ?DepositCallbackStruct $deposit = null;

            public ?PayoutCallbackStruct $payout = null;

            public ?RefundCallbackStruct $refund = null;

            public ?CheckoutCallbackStruct $checkout = null;

            public function handleDeposit(DepositCallbackStruct $struct): void
            {
                $this->deposit = $struct;
            }

            public function handlePayout(PayoutCallbackStruct $struct): void
            {
                $this->payout = $struct;
            }

            public function handleRefund(RefundCallbackStruct $struct): void
            {
                $this->refund = $struct;
            }

            public function handleCheckout(CheckoutCallbackStruct $struct): void
            {
                $this->checkout = $struct;
            }
        };
    }
}
