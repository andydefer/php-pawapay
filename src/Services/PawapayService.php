<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Services;

use AndyDefer\PhpPawapay\Contracts\PawapayClientInterface;
use AndyDefer\PhpPawapay\Contracts\PawapayInterface;
use AndyDefer\PhpPawapay\Datas\CheckDepositStatusData;
use AndyDefer\PhpPawapay\Datas\CreatePaymentPageData;
use AndyDefer\PhpPawapay\Datas\FailureReasonData;
use AndyDefer\PhpPawapay\Datas\InitiateDepositData;
use AndyDefer\PhpPawapay\Datas\ResendDepositCallbackData;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;
use AndyDefer\PhpPawapay\Records\CheckDepositStatusRecord;
use AndyDefer\PhpPawapay\Records\CreatePaymentPageRecord;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Records\ResendDepositCallbackRecord;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;
use AndyDefer\PhpPawapay\Structures\PaymentPageStruct;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;
use AndyDefer\PhpPawapay\ValueObjects\ReferenceVO;

class PawapayService implements PawapayInterface
{
    public function __construct(
        private readonly PawapayClientInterface $client,
    ) {}

    public function initiateDeposit(InitiateDepositRecord $record): InitiateDepositData
    {
        $this->beforeInitiateDeposit($record);

        $deposit = InitiateDepositVO::from([
            'depositId' => $record->depositId,
            'payer' => $record->payer,
            'amount' => $record->amount,
            'currency' => $record->currency,
            'preAuthorisationCode' => $record->preAuthorisationCode,
            'clientReferenceId' => $record->clientReferenceId !== null
                ? ReferenceVO::from($record->clientReferenceId)
                : null,
            'customerMessage' => $record->customerMessage,
            'metadata' => $record->metadata,
        ]);

        $response = $this->client->initiateDeposit($deposit);

        $data = InitiateDepositData::from([
            'depositId' => $response->getDepositId(),
            'status' => $response->getStatus(),
            'created' => $response->getCreated(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'isAccepted' => $response->isAccepted(),
            'isRejected' => $response->isRejected(),
            'isDuplicateIgnored' => $response->isDuplicateIgnored(),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);

        $this->afterInitiateDeposit($record, $data);

        return $data;
    }

    public function checkDepositStatus(CheckDepositStatusRecord $record): CheckDepositStatusData
    {
        $this->beforeCheckDepositStatus($record);

        $response = $this->client->checkDepositStatus($record->depositId->getValue());

        $data = CheckDepositStatusData::from([
            'searchStatus' => $response->getSearchStatus(),
            'depositData' => $response->getDepositData(),
            'isFound' => $response->isFound(),
            'isNotFound' => $response->isNotFound(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);

        $this->afterCheckDepositStatus($record, $data);

        return $data;
    }

    public function resendDepositCallback(ResendDepositCallbackRecord $record): ResendDepositCallbackData
    {
        $this->beforeResendDepositCallback($record);

        $response = $this->client->resendDepositCallback($record->depositId->getValue());

        $data = ResendDepositCallbackData::from([
            'depositId' => $response->getDepositId(),
            'status' => $response->getStatus(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'isAccepted' => $response->isAccepted(),
            'isRejected' => $response->isRejected(),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);

        $this->afterResendDepositCallback($record, $data);

        return $data;
    }

    public function createPaymentPage(CreatePaymentPageRecord $record): CreatePaymentPageData
    {
        $this->beforeCreatePaymentPage($record);

        $struct = PaymentPageStruct::from([
            'depositId' => $record->depositId,
            'returnUrl' => $record->returnUrl,
            'amountDetails' => $record->amountDetails,
            'phoneNumber' => $record->phoneNumber,
            'language' => $record->language,
            'country' => $record->country,
            'customerMessage' => $record->customerMessage,
            'metadata' => $record->metadata,
        ]);

        $response = $this->client->createPaymentPage($struct);

        $data = CreatePaymentPageData::from([
            'redirectUrl' => $response->getRedirectUrlAsString(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);

        $this->afterCreatePaymentPage($record, $data);

        return $data;
    }

    public static function create(string $apiToken, PawaPayBaseUrl $baseUrl): self
    {
        return new self(
            new PawapayClient($apiToken, $baseUrl),
        );
    }

    // ============================================================
    // HOOKS
    // ============================================================

    protected function beforeInitiateDeposit(InitiateDepositRecord $record): void {}

    protected function afterInitiateDeposit(InitiateDepositRecord $record, InitiateDepositData $data): void {}

    protected function beforeCheckDepositStatus(CheckDepositStatusRecord $record): void {}

    protected function afterCheckDepositStatus(CheckDepositStatusRecord $record, CheckDepositStatusData $data): void {}

    protected function beforeResendDepositCallback(ResendDepositCallbackRecord $record): void {}

    protected function afterResendDepositCallback(ResendDepositCallbackRecord $record, ResendDepositCallbackData $data): void {}

    protected function beforeCreatePaymentPage(CreatePaymentPageRecord $record): void {}

    protected function afterCreatePaymentPage(CreatePaymentPageRecord $record, CreatePaymentPageData $data): void {}

    // ============================================================
    // CONVERSIONS
    // ============================================================

    private function failureReasonToData(?FailureReasonStruct $reason): ?FailureReasonData
    {
        if ($reason === null) {
            return null;
        }

        return FailureReasonData::from([
            'failureCode' => $reason->failureCode,
            'failureMessage' => $reason->failureMessage,
        ]);
    }
}
