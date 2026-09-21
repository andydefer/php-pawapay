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

final class PawapayService implements PawapayInterface
{
    public function __construct(
        private readonly PawapayClientInterface $client,
    ) {}

    public function initiateDeposit(InitiateDepositRecord $record): InitiateDepositData
    {
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

        return InitiateDepositData::from([
            'depositId' => $response->getDepositId(),
            'status' => $response->getStatus(),
            'created' => $response->getCreated(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'isAccepted' => $response->isAccepted(),
            'isRejected' => $response->isRejected(),
            'isDuplicateIgnored' => $response->isDuplicateIgnored(),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);
    }

    public function checkDepositStatus(CheckDepositStatusRecord $record): CheckDepositStatusData
    {
        $response = $this->client->checkDepositStatus($record->depositId->getValue());

        return CheckDepositStatusData::from([
            'searchStatus' => $response->getSearchStatus(),
            'depositData' => $response->getDepositData(),
            'isFound' => $response->isFound(),
            'isNotFound' => $response->isNotFound(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);
    }

    public function resendDepositCallback(ResendDepositCallbackRecord $record): ResendDepositCallbackData
    {
        $response = $this->client->resendDepositCallback($record->depositId->getValue());

        return ResendDepositCallbackData::from([
            'depositId' => $response->getDepositId(),
            'status' => $response->getStatus(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'isAccepted' => $response->isAccepted(),
            'isRejected' => $response->isRejected(),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);
    }

    public function createPaymentPage(CreatePaymentPageRecord $record): CreatePaymentPageData
    {
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

        return CreatePaymentPageData::from([
            'redirectUrl' => $response->getRedirectUrlAsString(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);
    }

    public static function create(string $apiToken, PawaPayBaseUrl $baseUrl): self
    {
        return new self(
            new PawapayClient($apiToken, $baseUrl),
        );
    }

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
