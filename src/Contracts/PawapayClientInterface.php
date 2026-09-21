<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts;

use AndyDefer\PhpPawapay\Contracts\Responses\CheckDepositStatusResponseInterface;
use AndyDefer\PhpPawapay\Contracts\Responses\CreatePaymentPageResponseInterface;
use AndyDefer\PhpPawapay\Contracts\Responses\InitiateDepositResponseInterface;
use AndyDefer\PhpPawapay\Contracts\Responses\ResendDepositCallbackResponseInterface;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Structures\PaymentPageStruct;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;

interface PawapayClientInterface
{
    public function setBaseUrl(PawaPayBaseUrl $baseUrl): self;

    public function initiateDeposit(InitiateDepositVO $deposit): InitiateDepositResponseInterface;

    public function checkDepositStatus(string $depositId): CheckDepositStatusResponseInterface;

    public function resendDepositCallback(string $depositId): ResendDepositCallbackResponseInterface;

    public function createPaymentPage(PaymentPageStruct $paymentPage): CreatePaymentPageResponseInterface;
}
