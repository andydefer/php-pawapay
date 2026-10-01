<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Services;

use AndyDefer\PhpPawapay\Contracts\Callbacks\HandlesCallbacksInterface;
use AndyDefer\PhpPawapay\Contracts\PawapayClientInterface;
use AndyDefer\PhpPawapay\Contracts\PawapayInterface;
use AndyDefer\PhpPawapay\Datas\CheckDepositStatusData;
use AndyDefer\PhpPawapay\Datas\CreatePaymentPageData;
use AndyDefer\PhpPawapay\Datas\ErrorResponseData;
use AndyDefer\PhpPawapay\Datas\FailureReasonData;
use AndyDefer\PhpPawapay\Datas\InitiateDepositData;
use AndyDefer\PhpPawapay\Datas\PredictProviderData;
use AndyDefer\PhpPawapay\Datas\ResendDepositCallbackData;
use AndyDefer\PhpPawapay\Enums\CallbackOperationType;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;
use AndyDefer\PhpPawapay\Records\AmountDetailsRecord;
use AndyDefer\PhpPawapay\Records\CheckDepositStatusRecord;
use AndyDefer\PhpPawapay\Records\CreatePaymentPageRecord;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Records\PredictProviderRecord;
use AndyDefer\PhpPawapay\Records\ResendDepositCallbackRecord;
use AndyDefer\PhpPawapay\Structures\Callbacks\CheckoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\PayoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\RefundCallbackStruct;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;
use AndyDefer\PhpPawapay\Structures\PaymentPageStruct;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;
use AndyDefer\PhpPawapay\ValueObjects\ReferenceVO;

class PawapayService implements PawapayInterface
{
    public function __construct(
        private readonly PawapayClientInterface $client,
    ) {}

    public function initiateDeposit(InitiateDepositRecord $record): InitiateDepositData|ErrorResponseData
    {
        $before = $this->beforeInitiateDeposit($record);

        if ($before instanceof ErrorResponseData) {
            return $before;
        }

        $record = $before;

        $deposit = InitiateDepositVO::from([
            'depositId' => $record->depositId,
            'payer' => $record->payer,
            'amount' => AmountVO::from(
                $this->normalizeAmount($record->amount, $record->currency),
            ),
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

    public function checkDepositStatus(CheckDepositStatusRecord $record): CheckDepositStatusData|ErrorResponseData
    {
        $before = $this->beforeCheckDepositStatus($record);

        if ($before instanceof ErrorResponseData) {
            return $before;
        }

        $record = $before;

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

    public function resendDepositCallback(ResendDepositCallbackRecord $record): ResendDepositCallbackData|ErrorResponseData
    {
        $before = $this->beforeResendDepositCallback($record);

        if ($before instanceof ErrorResponseData) {
            return $before;
        }

        $record = $before;

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

    public function createPaymentPage(CreatePaymentPageRecord $record): CreatePaymentPageData|ErrorResponseData
    {
        $before = $this->beforeCreatePaymentPage($record);

        if ($before instanceof ErrorResponseData) {
            return $before;
        }

        $record = $before;

        $amountDetails = $record->amountDetails;

        $normalizedAmountDetails = new AmountDetailsRecord(
            amount: AmountVO::from(
                $this->normalizeAmount($amountDetails->amount, $amountDetails->currency),
            ),
            currency: $amountDetails->currency,
        );

        $struct = PaymentPageStruct::from([
            'depositId' => $record->depositId,
            'returnUrl' => $record->returnUrl,
            'amountDetails' => $normalizedAmountDetails,
            'phoneNumber' => $record->phoneNumber,
            'language' => $record->language,
            'country' => $record->country,
            'customerMessage' => $record->customerMessage,
            'reason' => $record->customerMessage,
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

    public function predictProvider(PredictProviderRecord $record): PredictProviderData|ErrorResponseData
    {
        $before = $this->beforePredictProvider($record);

        if ($before instanceof ErrorResponseData) {
            return $before;
        }

        $record = $before;

        $response = $this->client->predictProvider($record->phoneNumber);

        if ($response->hasFailureReason() && ! $response->isSuccess()) {
            $reason = $response->getFailureReason();

            return ErrorResponseData::from([
                'message' => $reason->failureMessage,
                'status' => $response->getStatusCode(),
                'errorCode' => $reason->failureCode->value,
            ]);
        }

        $data = PredictProviderData::from([
            'country' => $response->getCountry(),
            'provider' => $response->getProvider(),
            'phoneNumber' => $response->getPhoneNumber(),
            'isFound' => $response->isFound(),
            'failureReason' => $this->failureReasonToData($response->getFailureReason()),
            'hasFailureReason' => $response->hasFailureReason(),
        ]);

        $after = $this->afterPredictProvider($record, $data);

        if ($after instanceof ErrorResponseData) {
            return $after;
        }

        return $data;
    }

    public function handleCallback(
        DepositCallbackStruct|PayoutCallbackStruct|RefundCallbackStruct|CheckoutCallbackStruct $struct,
        HandlesCallbacksInterface $handler,
    ): void {
        $operation = $this->operationOf($struct);

        $this->beforeHandleCallback($struct, $operation);

        match (true) {
            $struct instanceof DepositCallbackStruct => $handler->handleDeposit($struct),
            $struct instanceof PayoutCallbackStruct => $handler->handlePayout($struct),
            $struct instanceof RefundCallbackStruct => $handler->handleRefund($struct),
            $struct instanceof CheckoutCallbackStruct => $handler->handleCheckout($struct),
        };

        $this->afterHandleCallback($struct, $operation);
    }

    public static function create(string $apiToken, PawaPayBaseUrl $baseUrl): self
    {
        return new self(
            new PawapayClient($apiToken, $baseUrl),
        );
    }

    // ============================================================
    // HOOKS — before* retourne soit le Record muté, soit l'erreur
    // ============================================================

    protected function beforeInitiateDeposit(InitiateDepositRecord $record): InitiateDepositRecord|ErrorResponseData
    {
        return $record;
    }

    protected function afterInitiateDeposit(InitiateDepositRecord $record, InitiateDepositData $data): ?ErrorResponseData
    {
        return null;
    }

    protected function beforeCheckDepositStatus(CheckDepositStatusRecord $record): CheckDepositStatusRecord|ErrorResponseData
    {
        return $record;
    }

    protected function afterCheckDepositStatus(CheckDepositStatusRecord $record, CheckDepositStatusData $data): ?ErrorResponseData
    {
        return null;
    }

    protected function beforeResendDepositCallback(ResendDepositCallbackRecord $record): ResendDepositCallbackRecord|ErrorResponseData
    {
        return $record;
    }

    protected function afterResendDepositCallback(ResendDepositCallbackRecord $record, ResendDepositCallbackData $data): ?ErrorResponseData
    {
        return null;
    }

    protected function beforeCreatePaymentPage(CreatePaymentPageRecord $record): CreatePaymentPageRecord|ErrorResponseData
    {
        return $record;
    }

    protected function afterCreatePaymentPage(CreatePaymentPageRecord $record, CreatePaymentPageData $data): ?ErrorResponseData
    {
        return null;
    }

    protected function beforePredictProvider(PredictProviderRecord $record): PredictProviderRecord|ErrorResponseData
    {
        return $record;
    }

    protected function afterPredictProvider(PredictProviderRecord $record, PredictProviderData $data): ?ErrorResponseData
    {
        return null;
    }

    protected function beforeHandleCallback(
        DepositCallbackStruct|PayoutCallbackStruct|RefundCallbackStruct|CheckoutCallbackStruct $struct,
        CallbackOperationType $operation,
    ): void {}

    protected function afterHandleCallback(
        DepositCallbackStruct|PayoutCallbackStruct|RefundCallbackStruct|CheckoutCallbackStruct $struct,
        CallbackOperationType $operation,
    ): void {}

    // ============================================================
    // CONVERSIONS
    // ============================================================

    private function operationOf(
        DepositCallbackStruct|PayoutCallbackStruct|RefundCallbackStruct|CheckoutCallbackStruct $struct,
    ): CallbackOperationType {
        return match (true) {
            $struct instanceof DepositCallbackStruct => CallbackOperationType::DEPOSIT,
            $struct instanceof PayoutCallbackStruct => CallbackOperationType::PAYOUT,
            $struct instanceof RefundCallbackStruct => CallbackOperationType::REFUND,
            $struct instanceof CheckoutCallbackStruct => CallbackOperationType::CHECKOUT,
        };
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

    /**
     * Normalize the amount according to the currency decimals.
     *
     * USD keeps 2 decimals (unchanged). Every other supported currency
     * is rounded UP to the next integer (ceil), so the user is never
     * charged less than what PawaPay expects.
     */
    private function normalizeAmount(AmountVO $amount, Currency $currency): float
    {
        if ($currency === Currency::USD) {
            return $amount->toFloat();
        }

        return ceil($amount->toFloat());
    }
}
