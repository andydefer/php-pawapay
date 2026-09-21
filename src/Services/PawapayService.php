<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Services;

use AndyDefer\PhpPawapay\Contracts\PawapayClientInterface;
use AndyDefer\PhpPawapay\Contracts\PawapayInterface;
use AndyDefer\PhpPawapay\Datas\CheckDepositStatusData;
use AndyDefer\PhpPawapay\Datas\CreatePaymentPageData;
use AndyDefer\PhpPawapay\Datas\ErrorResponseData;
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

/**
 * Default implementation of {@see PawapayInterface}.
 *
 * Orchestrates the four PawaPay operations by converting the incoming
 * {@see AbstractRecord} into PawaPay value objects, calling the low-level
 * client, and returning the response as typed {@see AbstractData}.
 *
 * Hooks (`before*` / `after*`) let subclasses short-circuit the flow by
 * returning an {@see ErrorResponseData}.
 */
class PawapayService implements PawapayInterface
{
    public function __construct(
        private readonly PawapayClientInterface $client,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function initiateDeposit(InitiateDepositRecord $record): InitiateDepositData|ErrorResponseData
    {
        $before = $this->beforeInitiateDeposit($record);

        if ($before instanceof ErrorResponseData) {
            return $before;
        }

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

        $after = $this->afterInitiateDeposit($record, $data);

        if ($after instanceof ErrorResponseData) {
            return $after;
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function checkDepositStatus(CheckDepositStatusRecord $record): CheckDepositStatusData|ErrorResponseData
    {
        $before = $this->beforeCheckDepositStatus($record);

        if ($before instanceof ErrorResponseData) {
            return $before;
        }

        $response = $this->client->checkDepositStatus($record->depositId->getValue());

        $data = CheckDepositStatusData::from([
            'searchStatus' => $response->getSearchStatus(),
            'depositData' => $response->getDepositData(),
            'isFound' => $response->isFound(),
            'isNotFound' => $response->isNotFound(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);

        $after = $this->afterCheckDepositStatus($record, $data);

        if ($after instanceof ErrorResponseData) {
            return $after;
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function resendDepositCallback(ResendDepositCallbackRecord $record): ResendDepositCallbackData|ErrorResponseData
    {
        $before = $this->beforeResendDepositCallback($record);

        if ($before instanceof ErrorResponseData) {
            return $before;
        }

        $response = $this->client->resendDepositCallback($record->depositId->getValue());

        $data = ResendDepositCallbackData::from([
            'depositId' => $response->getDepositId(),
            'status' => $response->getStatus(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'isAccepted' => $response->isAccepted(),
            'isRejected' => $response->isRejected(),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);

        $after = $this->afterResendDepositCallback($record, $data);

        if ($after instanceof ErrorResponseData) {
            return $after;
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function createPaymentPage(CreatePaymentPageRecord $record): CreatePaymentPageData|ErrorResponseData
    {
        $before = $this->beforeCreatePaymentPage($record);

        if ($before instanceof ErrorResponseData) {
            return $before;
        }

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

        $after = $this->afterCreatePaymentPage($record, $data);

        if ($after instanceof ErrorResponseData) {
            return $after;
        }

        return $data;
    }

    /**
     * Create a service instance from an API token and a base URL.
     *
     * @param  string  $apiToken  The PawaPay API token.
     * @param  PawaPayBaseUrl  $baseUrl  Sandbox or production URL.
     * @return self A ready-to-use service instance.
     */
    public static function create(string $apiToken, PawaPayBaseUrl $baseUrl): self
    {
        return new self(
            new PawapayClient($apiToken, $baseUrl),
        );
    }

    // ============================================================
    // HOOKS
    // ============================================================

    /**
     * Hook executed before initiating a deposit.
     *
     * @param  InitiateDepositRecord  $record  The deposit payload.
     * @return ErrorResponseData|null Return an error to short-circuit, or null to continue.
     */
    protected function beforeInitiateDeposit(InitiateDepositRecord $record): ?ErrorResponseData
    {
        return null;
    }

    /**
     * Hook executed after initiating a deposit.
     *
     * @param  InitiateDepositRecord  $record  The deposit payload.
     * @param  InitiateDepositData  $data  The deposit data returned by PawaPay.
     * @return ErrorResponseData|null Return an error to short-circuit, or null to continue.
     */
    protected function afterInitiateDeposit(InitiateDepositRecord $record, InitiateDepositData $data): ?ErrorResponseData
    {
        return null;
    }

    /**
     * Hook executed before checking a deposit status.
     *
     * @param  CheckDepositStatusRecord  $record  The deposit identifier.
     * @return ErrorResponseData|null Return an error to short-circuit, or null to continue.
     */
    protected function beforeCheckDepositStatus(CheckDepositStatusRecord $record): ?ErrorResponseData
    {
        return null;
    }

    /**
     * Hook executed after checking a deposit status.
     *
     * @param  CheckDepositStatusRecord  $record  The deposit identifier.
     * @param  CheckDepositStatusData  $data  The status data returned by PawaPay.
     * @return ErrorResponseData|null Return an error to short-circuit, or null to continue.
     */
    protected function afterCheckDepositStatus(CheckDepositStatusRecord $record, CheckDepositStatusData $data): ?ErrorResponseData
    {
        return null;
    }

    /**
     * Hook executed before resending a deposit callback.
     *
     * @param  ResendDepositCallbackRecord  $record  The deposit identifier.
     * @return ErrorResponseData|null Return an error to short-circuit, or null to continue.
     */
    protected function beforeResendDepositCallback(ResendDepositCallbackRecord $record): ?ErrorResponseData
    {
        return null;
    }

    /**
     * Hook executed after resending a deposit callback.
     *
     * @param  ResendDepositCallbackRecord  $record  The deposit identifier.
     * @param  ResendDepositCallbackData  $data  The resend result returned by PawaPay.
     * @return ErrorResponseData|null Return an error to short-circuit, or null to continue.
     */
    protected function afterResendDepositCallback(ResendDepositCallbackRecord $record, ResendDepositCallbackData $data): ?ErrorResponseData
    {
        return null;
    }

    /**
     * Hook executed before creating a payment page.
     *
     * @param  CreatePaymentPageRecord  $record  The payment page payload.
     * @return ErrorResponseData|null Return an error to short-circuit, or null to continue.
     */
    protected function beforeCreatePaymentPage(CreatePaymentPageRecord $record): ?ErrorResponseData
    {
        return null;
    }

    /**
     * Hook executed after creating a payment page.
     *
     * @param  CreatePaymentPageRecord  $record  The payment page payload.
     * @param  CreatePaymentPageData  $data  The payment page data returned by PawaPay.
     * @return ErrorResponseData|null Return an error to short-circuit, or null to continue.
     */
    protected function afterCreatePaymentPage(CreatePaymentPageRecord $record, CreatePaymentPageData $data): ?ErrorResponseData
    {
        return null;
    }

    // ============================================================
    // CONVERSIONS
    // ============================================================

    /**
     * Convert a PawaPay failure reason struct into its typed data counterpart.
     *
     * @param  FailureReasonStruct|null  $reason  The failure reason returned by PawaPay, if any.
     * @return FailureReasonData|null The typed failure reason, or null when absent.
     */
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
