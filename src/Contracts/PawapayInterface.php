<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts;

use AndyDefer\PhpPawapay\Datas\CheckDepositStatusData;
use AndyDefer\PhpPawapay\Datas\CreatePaymentPageData;
use AndyDefer\PhpPawapay\Datas\InitiateDepositData;
use AndyDefer\PhpPawapay\Datas\ResendDepositCallbackData;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Records\CheckDepositStatusRecord;
use AndyDefer\PhpPawapay\Records\CreatePaymentPageRecord;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Records\ResendDepositCallbackRecord;

interface PawapayInterface
{
    public static function create(string $apiToken, PawaPayBaseUrl $baseUrl): self;

    public function initiateDeposit(InitiateDepositRecord $record): InitiateDepositData;

    public function checkDepositStatus(CheckDepositStatusRecord $record): CheckDepositStatusData;

    public function resendDepositCallback(ResendDepositCallbackRecord $record): ResendDepositCallbackData;

    public function createPaymentPage(CreatePaymentPageRecord $record): CreatePaymentPageData;
}
