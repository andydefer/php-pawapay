<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts;

use AndyDefer\PhpPawapay\Contracts\Callbacks\HandlesCallbacksInterface;
use AndyDefer\PhpPawapay\Datas\CheckDepositStatusData;
use AndyDefer\PhpPawapay\Datas\CreatePaymentPageData;
use AndyDefer\PhpPawapay\Datas\ErrorResponseData;
use AndyDefer\PhpPawapay\Datas\InitiateDepositData;
use AndyDefer\PhpPawapay\Datas\ResendDepositCallbackData;
use AndyDefer\PhpPawapay\Records\CheckDepositStatusRecord;
use AndyDefer\PhpPawapay\Records\CreatePaymentPageRecord;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Records\ResendDepositCallbackRecord;
use AndyDefer\PhpPawapay\Structures\Callbacks\CheckoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\PayoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\RefundCallbackStruct;

/**
 * Contract for the PawaPay service.
 *
 * Each operation returns its typed {@see AbstractData} on success, or an
 * {@see ErrorResponseData} when a hook short-circuits the flow.
 */
interface PawapayInterface
{
    public function initiateDeposit(InitiateDepositRecord $record): InitiateDepositData|ErrorResponseData;

    public function checkDepositStatus(CheckDepositStatusRecord $record): CheckDepositStatusData|ErrorResponseData;

    public function resendDepositCallback(ResendDepositCallbackRecord $record): ResendDepositCallbackData|ErrorResponseData;

    public function createPaymentPage(CreatePaymentPageRecord $record): CreatePaymentPageData|ErrorResponseData;

    /**
     * Handle an incoming PawaPay callback.
     *
     * Accepts any of the four typed callback structs. The operation is
     * dispatched to the corresponding handler method.
     *
     * @param  HandlesCallbacksInterface  $handler  The handler that will process the callback.
     */
    public function handleCallback(
        DepositCallbackStruct|PayoutCallbackStruct|RefundCallbackStruct|CheckoutCallbackStruct $struct,
        HandlesCallbacksInterface $handler,
    ): void;
}
