<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\Services;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Datas\CheckDepositStatusData;
use AndyDefer\PhpPawapay\Datas\CreatePaymentPageData;
use AndyDefer\PhpPawapay\Datas\ErrorResponseData;
use AndyDefer\PhpPawapay\Datas\InitiateDepositData;
use AndyDefer\PhpPawapay\Datas\ResendDepositCallbackData;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\DepositSearchStatus;
use AndyDefer\PhpPawapay\Enums\DepositStatus;
use AndyDefer\PhpPawapay\Enums\FailureCode;
use AndyDefer\PhpPawapay\Enums\Language;
use AndyDefer\PhpPawapay\Enums\PayerType;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\Enums\ResendCallbackStatus;
use AndyDefer\PhpPawapay\Graphs\AmountDetailsGraph;
use AndyDefer\PhpPawapay\Records\CheckDepositStatusRecord;
use AndyDefer\PhpPawapay\Records\CreatePaymentPageRecord;
use AndyDefer\PhpPawapay\Records\InitiateDepositRecord;
use AndyDefer\PhpPawapay\Records\ResendDepositCallbackRecord;
use AndyDefer\PhpPawapay\Services\PawapayService;
use AndyDefer\PhpPawapay\Tests\Fixtures\Service\ErroringPawapayService;
use AndyDefer\PhpPawapay\Tests\MockPawapayClient;
use AndyDefer\PhpPawapay\ValueObjects\AccountDetailsVO;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
use AndyDefer\PhpVo\Enums\HttpStatusCode;
use PHPUnit\Framework\TestCase;

final class PawapayServiceTest extends TestCase
{
    private MockPawapayClient $client;

    private PawapayService $service;

    protected function setUp(): void
    {
        $this->client = new MockPawapayClient;
        $this->service = new PawapayService($this->client);
    }

    private function createInitiateDepositRecord(): InitiateDepositRecord
    {
        $accountDetails = AccountDetailsVO::from([
            'phoneNumber' => PhoneNumberVO::from('260763456789'),
            'provider' => Provider::MTN_MOMO_ZMB,
        ]);

        $payer = PayerVO::from([
            'type' => PayerType::MMO,
            'accountDetails' => $accountDetails,
        ]);

        return InitiateDepositRecord::from([
            'depositId' => UuidVO::from('f4401bd2-1568-4140-bf2d-eb77d2b2b639'),
            'payer' => $payer,
            'amount' => AmountVO::from(15.00),
            'currency' => Currency::ZMW,
            'preAuthorisationCode' => null,
            'clientReferenceId' => 'INV-123456',
            'customerMessage' => CustomerMessageVO::from('Payment order 123'),
            'metadata' => MetadataVO::from(new StrictDataObject([
                'orderId' => 'ORD-123456789',
            ])),
        ]);
    }

    private function createCreatePaymentPageRecord(): CreatePaymentPageRecord
    {
        return CreatePaymentPageRecord::from([
            'depositId' => UuidVO::from('9b724dbf-32a7-4e63-96bb-59a4747e43ca'),
            'returnUrl' => UrlVO::from('https://merchant.example.com/checkout-result'),
            'amountDetails' => AmountDetailsGraph::from([
                'amount' => AmountVO::from(25.50),
                'currency' => Currency::USD,
            ]),
            'phoneNumber' => PhoneNumberVO::from('243812345678'),
            'language' => Language::EN,
            'country' => Country::COD,
            'customerMessage' => CustomerMessageVO::from('Payment order 123456'),
            'metadata' => null,
        ]);
    }

    // ==================== INITIATE DEPOSIT ====================

    public function test_initiate_deposit_returns_data(): void
    {
        // Arrange: enqueue a canned 200 response
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'ACCEPTED',
            'created' => '2020-10-19T11:17:01Z',
        ]);

        // Act: call the service with a valid record
        $data = $this->service->initiateDeposit($this->createInitiateDepositRecord());

        // Assert: the typed data reflects the response
        $this->assertInstanceOf(InitiateDepositData::class, $data);
        $this->assertSame('f4401bd2-1568-4140-bf2d-eb77d2b2b639', $data->depositId->getValue());
        $this->assertSame(DepositStatus::ACCEPTED, $data->status);
        $this->assertSame('2020-10-19T11:17:01Z', $data->created->getValue());
        $this->assertNull($data->failureReason);
        $this->assertTrue($data->isAccepted);
        $this->assertFalse($data->isRejected);
        $this->assertFalse($data->isDuplicateIgnored);
        $this->assertFalse($data->hasFailureReason);
    }

    public function test_initiate_deposit_duplicate_ignored_returns_data(): void
    {
        // Arrange: enqueue a canned 200 response with the DUPLICATE_IGNORED status
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'DUPLICATE_IGNORED',
            'created' => '2020-10-19T11:17:01Z',
        ]);

        // Act: call the service with a valid record
        $data = $this->service->initiateDeposit($this->createInitiateDepositRecord());

        // Assert: the typed data flags the duplicate
        $this->assertSame(DepositStatus::DUPLICATE_IGNORED, $data->status);
        $this->assertTrue($data->isDuplicateIgnored);
        $this->assertFalse($data->isAccepted);
        $this->assertFalse($data->isRejected);
    }

    public function test_initiate_deposit_rejected_with_failure_reason_returns_data(): void
    {
        // Arrange: enqueue a canned 200 response with a failure reason
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'PROVIDER_TEMPORARILY_UNAVAILABLE',
                'failureMessage' => "The provider 'MTN_MOMO_ZMB' is currently not able to process payments.",
            ],
        ]);

        // Act: call the service with a valid record
        $data = $this->service->initiateDeposit($this->createInitiateDepositRecord());

        // Assert: the failure reason is mapped into typed data
        $this->assertSame(DepositStatus::REJECTED, $data->status);
        $this->assertTrue($data->isRejected);
        $this->assertTrue($data->hasFailureReason);
        $this->assertNotNull($data->failureReason);
        $this->assertSame(FailureCode::PROVIDER_TEMPORARILY_UNAVAILABLE, $data->failureReason->failureCode);
        $this->assertSame(
            "The provider 'MTN_MOMO_ZMB' is currently not able to process payments.",
            $data->failureReason->failureMessage,
        );
    }

    // ==================== CHECK DEPOSIT STATUS ====================

    public function test_check_deposit_status_found_returns_data(): void
    {
        // Arrange: enqueue a canned 200 response for a found deposit
        $this->client->addDepositFoundResponse([
            'depositId' => '8917c345-4791-4285-a416-62f24b6982db',
            'status' => 'COMPLETED',
            'amount' => '25.50',
            'currency' => 'USD',
            'country' => 'COD',
            'payer' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => '243812345678',
                    'provider' => 'VODACOM_MPESA_COD',
                ],
            ],
            'clientReferenceId' => 'REF-RDC-123456',
            'created' => '2020-10-19T08:17:01Z',
        ]);

        // Act: call the service with a valid record
        $data = $this->service->checkDepositStatus(
            CheckDepositStatusRecord::from(['depositId' => '8917c345-4791-4285-a416-62f24b6982db']),
        );

        // Assert: the typed data flags the deposit as found
        $this->assertInstanceOf(CheckDepositStatusData::class, $data);
        $this->assertSame(DepositSearchStatus::FOUND, $data->searchStatus);
        $this->assertTrue($data->isFound);
        $this->assertFalse($data->isNotFound);
        $this->assertFalse($data->hasFailureReason);
        $this->assertNotNull($data->depositData);
    }

    public function test_check_deposit_status_not_found_returns_data(): void
    {
        // Arrange: enqueue a canned 200 response with the NOT_FOUND search status
        $this->client->addNotFoundResponse();

        // Act: call the service with a valid record
        $data = $this->service->checkDepositStatus(
            CheckDepositStatusRecord::from(['depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639']),
        );

        // Assert: the typed data flags the deposit as not found
        $this->assertSame(DepositSearchStatus::NOT_FOUND, $data->searchStatus);
        $this->assertFalse($data->isFound);
        $this->assertTrue($data->isNotFound);
        $this->assertNull($data->depositData);
        $this->assertFalse($data->hasFailureReason);
    }

    public function test_check_deposit_status_authentication_error_returns_data(): void
    {
        // Arrange: enqueue a canned 401 response
        $this->client->addAuthenticationErrorResponse();

        // Act: call the service with a valid record
        $data = $this->service->checkDepositStatus(
            CheckDepositStatusRecord::from(['depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639']),
        );

        // Assert: the failure reason is mapped into typed data
        $this->assertTrue($data->hasFailureReason);
        $this->assertNotNull($data->failureReason);
        $this->assertSame(FailureCode::AUTHENTICATION_ERROR, $data->failureReason->failureCode);
        $this->assertSame('The API token in the request is invalid.', $data->failureReason->failureMessage);
    }

    // ==================== RESEND DEPOSIT CALLBACK ====================

    public function test_resend_deposit_callback_accepted_returns_data(): void
    {
        // Arrange: enqueue a canned 200 response with an accepted callback
        $this->client->addSuccessResponse([
            'depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca',
            'status' => 'ACCEPTED',
        ]);

        // Act: call the service with a valid record
        $data = $this->service->resendDepositCallback(
            ResendDepositCallbackRecord::from(['depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca']),
        );

        // Assert: the typed data flags the callback as accepted
        $this->assertInstanceOf(ResendDepositCallbackData::class, $data);
        $this->assertSame('9b724dbf-32a7-4e63-96bb-59a4747e43ca', $data->depositId->getValue());
        $this->assertSame(ResendCallbackStatus::ACCEPTED, $data->status);
        $this->assertTrue($data->isAccepted);
        $this->assertFalse($data->isRejected);
        $this->assertFalse($data->hasFailureReason);
        $this->assertNull($data->failureReason);
    }

    public function test_resend_deposit_callback_rejected_returns_data(): void
    {
        // Arrange: enqueue a canned 200 response with a rejected callback
        $this->client->addSuccessResponse([
            'depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca',
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'PAYMENT_NOT_APPROVED',
                'failureMessage' => 'Payout not found',
            ],
        ]);

        // Act: call the service with a valid record
        $data = $this->service->resendDepositCallback(
            ResendDepositCallbackRecord::from(['depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca']),
        );

        // Assert: the typed data flags the callback as rejected
        $this->assertSame(ResendCallbackStatus::REJECTED, $data->status);
        $this->assertFalse($data->isAccepted);
        $this->assertTrue($data->isRejected);
        $this->assertTrue($data->hasFailureReason);
        $this->assertNotNull($data->failureReason);
        $this->assertSame(FailureCode::PAYMENT_NOT_APPROVED, $data->failureReason->failureCode);
    }

    public function test_resend_deposit_callback_authentication_error_returns_data(): void
    {
        // Arrange: enqueue a canned 401 response
        $this->client->addAuthenticationErrorResponse();

        // Act: call the service with a valid record
        $data = $this->service->resendDepositCallback(
            ResendDepositCallbackRecord::from(['depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca']),
        );

        // Assert: the failure reason is mapped into typed data
        $this->assertTrue($data->hasFailureReason);
        $this->assertNotNull($data->failureReason);
        $this->assertSame(FailureCode::AUTHENTICATION_ERROR, $data->failureReason->failureCode);
    }

    // ==================== CREATE PAYMENT PAGE ====================

    public function test_create_payment_page_success_returns_data(): void
    {
        // Arrange: enqueue a canned 200 response with a redirect URL
        $this->client->addSuccessResponse([
            'redirectUrl' => 'https://sandbox.paywith.pawapay.io/v2?token=xxx',
        ]);

        // Act: call the service with a valid record
        $data = $this->service->createPaymentPage($this->createCreatePaymentPageRecord());

        // Assert: the typed data exposes the redirect URL
        $this->assertInstanceOf(CreatePaymentPageData::class, $data);
        $this->assertSame('https://sandbox.paywith.pawapay.io/v2?token=xxx', $data->redirectUrl->getValue());
        $this->assertNull($data->failureReason);
        $this->assertFalse($data->hasFailureReason);
    }

    public function test_create_payment_page_rejected_returns_data(): void
    {
        // Arrange: enqueue a canned 200 response with a failure reason
        $this->client->addSuccessResponse([
            'redirectUrl' => 'https://sandbox.paywith.pawapay.io/v2?token=xxx',
            'failureReason' => [
                'failureCode' => 'INVALID_PHONE_NUMBER',
                'failureMessage' => "The phone number '243812345678' seems to be invalid for the provider 'VODACOM_MPESA_COD'.",
            ],
        ]);

        // Act: call the service with a valid record
        $data = $this->service->createPaymentPage($this->createCreatePaymentPageRecord());

        // Assert: the failure reason is mapped into typed data
        $this->assertTrue($data->hasFailureReason);
        $this->assertNotNull($data->failureReason);
        $this->assertSame(FailureCode::INVALID_PHONE_NUMBER, $data->failureReason->failureCode);
        $this->assertSame(
            "The phone number '243812345678' seems to be invalid for the provider 'VODACOM_MPESA_COD'.",
            $data->failureReason->failureMessage,
        );
    }

    public function test_create_payment_page_authentication_error_returns_data(): void
    {
        // Arrange: enqueue a canned 401 response
        $this->client->addAuthenticationErrorResponse();

        // Act: call the service with a valid record
        $data = $this->service->createPaymentPage($this->createCreatePaymentPageRecord());

        // Assert: the failure reason is mapped into typed data
        $this->assertTrue($data->hasFailureReason);
        $this->assertNotNull($data->failureReason);
        $this->assertSame(FailureCode::AUTHENTICATION_ERROR, $data->failureReason->failureCode);
    }

    // ==================== HOOKS ====================

    public function test_initiate_deposit_returns_error_when_before_hook_short_circuits(): void
    {
        // Arrange: force the before hook to return an error
        $service = new ErroringPawapayService($this->client);
        $service->beforeInitiateDepositError = ErrorResponseData::from([
            'message' => 'Blocked by hook',
            'status' => HttpStatusCode::FORBIDDEN,
            'errorCode' => 'HOOK_BLOCKED',
        ]);

        // Act: call the service with a valid record
        $result = $service->initiateDeposit($this->createInitiateDepositRecord());

        // Assert: the hook short-circuits the flow and returns the error
        $this->assertInstanceOf(ErrorResponseData::class, $result);
        $this->assertSame('Blocked by hook', $result->message);
        $this->assertSame(HttpStatusCode::FORBIDDEN, $result->status);
        $this->assertSame('HOOK_BLOCKED', $result->errorCode);
    }

    public function test_initiate_deposit_returns_error_when_after_hook_short_circuits(): void
    {
        // Arrange: enqueue a canned success response and force the after hook to return an error
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'ACCEPTED',
            'created' => '2020-10-19T11:17:01Z',
        ]);

        $service = new ErroringPawapayService($this->client);
        $service->afterInitiateDepositError = ErrorResponseData::from([
            'message' => 'Rejected after flow',
            'status' => HttpStatusCode::UNPROCESSABLE_ENTITY,
            'errorCode' => 'AFTER_HOOK_REJECTED',
        ]);

        // Act: call the service with a valid record
        $result = $service->initiateDeposit($this->createInitiateDepositRecord());

        // Assert: the after hook short-circuits and returns the error
        $this->assertInstanceOf(ErrorResponseData::class, $result);
        $this->assertSame('Rejected after flow', $result->message);
        $this->assertSame(HttpStatusCode::UNPROCESSABLE_ENTITY, $result->status);
        $this->assertSame('AFTER_HOOK_REJECTED', $result->errorCode);
    }

    public function test_check_deposit_status_returns_error_when_before_hook_short_circuits(): void
    {
        // Arrange: force the before hook to return an error
        $service = new ErroringPawapayService($this->client);
        $service->beforeCheckDepositStatusError = ErrorResponseData::from([
            'message' => 'Status check blocked',
            'status' => HttpStatusCode::FORBIDDEN,
            'errorCode' => 'STATUS_BLOCKED',
        ]);

        // Act: call the service with a valid record
        $result = $service->checkDepositStatus(
            CheckDepositStatusRecord::from(['depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639']),
        );

        // Assert: the hook short-circuits the flow and returns the error
        $this->assertInstanceOf(ErrorResponseData::class, $result);
        $this->assertSame('Status check blocked', $result->message);
        $this->assertSame(HttpStatusCode::FORBIDDEN, $result->status);
        $this->assertSame('STATUS_BLOCKED', $result->errorCode);
    }

    public function test_check_deposit_status_returns_error_when_after_hook_short_circuits(): void
    {
        // Arrange: enqueue a canned success response and force the after hook to return an error
        $this->client->addDepositFoundResponse([
            'depositId' => '8917c345-4791-4285-a416-62f24b6982db',
            'status' => 'COMPLETED',
            'amount' => '25.50',
            'currency' => 'USD',
            'country' => 'COD',
            'payer' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => '243812345678',
                    'provider' => 'VODACOM_MPESA_COD',
                ],
            ],
            'created' => '2020-10-19T08:17:01Z',
        ]);

        $service = new ErroringPawapayService($this->client);
        $service->afterCheckDepositStatusError = ErrorResponseData::from([
            'message' => 'Rejected after status check',
            'status' => HttpStatusCode::UNPROCESSABLE_ENTITY,
            'errorCode' => 'AFTER_STATUS_REJECTED',
        ]);

        // Act: call the service with a valid record
        $result = $service->checkDepositStatus(
            CheckDepositStatusRecord::from(['depositId' => '8917c345-4791-4285-a416-62f24b6982db']),
        );

        // Assert: the after hook short-circuits and returns the error
        $this->assertInstanceOf(ErrorResponseData::class, $result);
        $this->assertSame('Rejected after status check', $result->message);
        $this->assertSame(HttpStatusCode::UNPROCESSABLE_ENTITY, $result->status);
        $this->assertSame('AFTER_STATUS_REJECTED', $result->errorCode);
    }

    public function test_resend_deposit_callback_returns_error_when_before_hook_short_circuits(): void
    {
        // Arrange: force the before hook to return an error
        $service = new ErroringPawapayService($this->client);
        $service->beforeResendDepositCallbackError = ErrorResponseData::from([
            'message' => 'Resend blocked',
            'status' => HttpStatusCode::FORBIDDEN,
            'errorCode' => 'RESEND_BLOCKED',
        ]);

        // Act: call the service with a valid record
        $result = $service->resendDepositCallback(
            ResendDepositCallbackRecord::from(['depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca']),
        );

        // Assert: the hook short-circuits the flow and returns the error
        $this->assertInstanceOf(ErrorResponseData::class, $result);
        $this->assertSame('Resend blocked', $result->message);
        $this->assertSame(HttpStatusCode::FORBIDDEN, $result->status);
        $this->assertSame('RESEND_BLOCKED', $result->errorCode);
    }

    public function test_resend_deposit_callback_returns_error_when_after_hook_short_circuits(): void
    {
        // Arrange: enqueue a canned success response and force the after hook to return an error
        $this->client->addSuccessResponse([
            'depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca',
            'status' => 'ACCEPTED',
        ]);

        $service = new ErroringPawapayService($this->client);
        $service->afterResendDepositCallbackError = ErrorResponseData::from([
            'message' => 'Rejected after resend',
            'status' => HttpStatusCode::UNPROCESSABLE_ENTITY,
            'errorCode' => 'AFTER_RESEND_REJECTED',
        ]);

        // Act: call the service with a valid record
        $result = $service->resendDepositCallback(
            ResendDepositCallbackRecord::from(['depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca']),
        );

        // Assert: the after hook short-circuits and returns the error
        $this->assertInstanceOf(ErrorResponseData::class, $result);
        $this->assertSame('Rejected after resend', $result->message);
        $this->assertSame(HttpStatusCode::UNPROCESSABLE_ENTITY, $result->status);
        $this->assertSame('AFTER_RESEND_REJECTED', $result->errorCode);
    }

    public function test_create_payment_page_returns_error_when_before_hook_short_circuits(): void
    {
        // Arrange: force the before hook to return an error
        $service = new ErroringPawapayService($this->client);
        $service->beforeCreatePaymentPageError = ErrorResponseData::from([
            'message' => 'Payment page blocked',
            'status' => HttpStatusCode::FORBIDDEN,
            'errorCode' => 'PAGE_BLOCKED',
        ]);

        // Act: call the service with a valid record
        $result = $service->createPaymentPage($this->createCreatePaymentPageRecord());

        // Assert: the hook short-circuits the flow and returns the error
        $this->assertInstanceOf(ErrorResponseData::class, $result);
        $this->assertSame('Payment page blocked', $result->message);
        $this->assertSame(HttpStatusCode::FORBIDDEN, $result->status);
        $this->assertSame('PAGE_BLOCKED', $result->errorCode);
    }

    public function test_create_payment_page_returns_error_when_after_hook_short_circuits(): void
    {
        // Arrange: enqueue a canned success response and force the after hook to return an error
        $this->client->addSuccessResponse([
            'redirectUrl' => 'https://sandbox.paywith.pawapay.io/v2?token=xxx',
        ]);

        $service = new ErroringPawapayService($this->client);
        $service->afterCreatePaymentPageError = ErrorResponseData::from([
            'message' => 'Rejected after page creation',
            'status' => HttpStatusCode::UNPROCESSABLE_ENTITY,
            'errorCode' => 'AFTER_PAGE_REJECTED',
        ]);

        // Act: call the service with a valid record
        $result = $service->createPaymentPage($this->createCreatePaymentPageRecord());

        // Assert: the after hook short-circuits and returns the error
        $this->assertInstanceOf(ErrorResponseData::class, $result);
        $this->assertSame('Rejected after page creation', $result->message);
        $this->assertSame(HttpStatusCode::UNPROCESSABLE_ENTITY, $result->status);
        $this->assertSame('AFTER_PAGE_REJECTED', $result->errorCode);
    }
}
