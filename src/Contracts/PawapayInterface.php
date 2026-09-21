<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts;

use AndyDefer\PhpPawapay\Datas\CheckDepositStatusData;
use AndyDefer\PhpPawapay\Datas\CreatePaymentPageData;
use AndyDefer\PhpPawapay\Datas\ErrorResponseData;
use AndyDefer\PhpPawapay\Datas\InitiateDepositData;
use AndyDefer\PhpPawapay\Datas\ResendDepositCallbackData;
use AndyDefer\PhpPawapay\Records\CheckDepositStatusRecord;
use AndyDefer\PhpPawapay\Records\CreatePaymentPageRecord;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Records\ResendDepositCallbackRecord;

/**
 * Contract for the PawaPay service.
 *
 * Each operation returns its typed {@see AbstractData} on success, or an
 * {@see ErrorResponseData} when a hook short-circuits the flow.
 */
interface PawapayInterface
{
    /**
     * Initiate a Mobile Money deposit.
     *
     * @param  InitiateDepositRecord  $record  The deposit payload.
     * @return InitiateDepositData|ErrorResponseData The deposit data, or an error response if blocked.
     */
    public function initiateDeposit(InitiateDepositRecord $record): InitiateDepositData|ErrorResponseData;

    /**
     * Check the status of an existing deposit.
     *
     * @param  CheckDepositStatusRecord  $record  The deposit identifier.
     * @return CheckDepositStatusData|ErrorResponseData The status data, or an error response if blocked.
     */
    public function checkDepositStatus(CheckDepositStatusRecord $record): CheckDepositStatusData|ErrorResponseData;

    /**
     * Ask PawaPay to resend the callback of a deposit.
     *
     * @param  ResendDepositCallbackRecord  $record  The deposit identifier.
     * @return ResendDepositCallbackData|ErrorResponseData The resend result, or an error response if blocked.
     */
    public function resendDepositCallback(ResendDepositCallbackRecord $record): ResendDepositCallbackData|ErrorResponseData;

    /**
     * Create a PawaPay-hosted payment page.
     *
     * @param  CreatePaymentPageRecord  $record  The payment page payload.
     * @return CreatePaymentPageData|ErrorResponseData The payment page data, or an error response if blocked.
     */
    public function createPaymentPage(CreatePaymentPageRecord $record): CreatePaymentPageData|ErrorResponseData;
}
