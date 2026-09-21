<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\Services;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Datas\CheckDepositStatusData;
use AndyDefer\PhpPawapay\Datas\CreatePaymentPageData;
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
use AndyDefer\PhpPawapay\Tests\MockPawapayClient;
use AndyDefer\PhpPawapay\ValueObjects\AccountDetailsVO;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
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
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'ACCEPTED',
            'created' => '2020-10-19T11:17:01Z',
        ]);

        $data = $this->service->initiateDeposit($this->createInitiateDepositRecord());

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
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'DUPLICATE_IGNORED',
            'created' => '2020-10-19T11:17:01Z',
        ]);

        $data = $this->service->initiateDeposit($this->createInitiateDepositRecord());

        $this->assertSame(DepositStatus::DUPLICATE_IGNORED, $data->status);
        $this->assertTrue($data->isDuplicateIgnored);
        $this->assertFalse($data->isAccepted);
        $this->assertFalse($data->isRejected);
    }

    public function test_initiate_deposit_rejected_with_failure_reason_returns_data(): void
    {
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'PROVIDER_TEMPORARILY_UNAVAILABLE',
                'failureMessage' => "The provider 'MTN_MOMO_ZMB' is currently not able to process payments.",
            ],
        ]);

        $data = $this->service->initiateDeposit($this->createInitiateDepositRecord());

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

        $data = $this->service->checkDepositStatus(
            CheckDepositStatusRecord::from(['depositId' => '8917c345-4791-4285-a416-62f24b6982db']),
        );

        $this->assertInstanceOf(CheckDepositStatusData::class, $data);
        $this->assertSame(DepositSearchStatus::FOUND, $data->searchStatus);
        $this->assertTrue($data->isFound);
        $this->assertFalse($data->isNotFound);
        $this->assertFalse($data->hasFailureReason);
        $this->assertNotNull($data->depositData);
    }

    public function test_check_deposit_status_not_found_returns_data(): void
    {
        $this->client->addNotFoundResponse();

        $data = $this->service->checkDepositStatus(
            CheckDepositStatusRecord::from(['depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639']),
        );

        $this->assertSame(DepositSearchStatus::NOT_FOUND, $data->searchStatus);
        $this->assertFalse($data->isFound);
        $this->assertTrue($data->isNotFound);
        $this->assertNull($data->depositData);
        $this->assertFalse($data->hasFailureReason);
    }

    public function test_check_deposit_status_authentication_error_returns_data(): void
    {
        $this->client->addAuthenticationErrorResponse();

        $data = $this->service->checkDepositStatus(
            CheckDepositStatusRecord::from(['depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639']),
        );

        $this->assertTrue($data->hasFailureReason);
        $this->assertNotNull($data->failureReason);
        $this->assertSame(FailureCode::AUTHENTICATION_ERROR, $data->failureReason->failureCode);
        $this->assertSame('The API token in the request is invalid.', $data->failureReason->failureMessage);
    }

    // ==================== RESEND DEPOSIT CALLBACK ====================

    public function test_resend_deposit_callback_accepted_returns_data(): void
    {
        $this->client->addSuccessResponse([
            'depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca',
            'status' => 'ACCEPTED',
        ]);

        $data = $this->service->resendDepositCallback(
            ResendDepositCallbackRecord::from(['depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca']),
        );

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
        $this->client->addSuccessResponse([
            'depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca',
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'PAYMENT_NOT_APPROVED',
                'failureMessage' => 'Payout not found',
            ],
        ]);

        $data = $this->service->resendDepositCallback(
            ResendDepositCallbackRecord::from(['depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca']),
        );

        $this->assertSame(ResendCallbackStatus::REJECTED, $data->status);
        $this->assertFalse($data->isAccepted);
        $this->assertTrue($data->isRejected);
        $this->assertTrue($data->hasFailureReason);
        $this->assertNotNull($data->failureReason);
        $this->assertSame(FailureCode::PAYMENT_NOT_APPROVED, $data->failureReason->failureCode);
    }

    public function test_resend_deposit_callback_authentication_error_returns_data(): void
    {
        $this->client->addAuthenticationErrorResponse();

        $data = $this->service->resendDepositCallback(
            ResendDepositCallbackRecord::from(['depositId' => '9b724dbf-32a7-4e63-96bb-59a4747e43ca']),
        );

        $this->assertTrue($data->hasFailureReason);
        $this->assertNotNull($data->failureReason);
        $this->assertSame(FailureCode::AUTHENTICATION_ERROR, $data->failureReason->failureCode);
    }

    // ==================== CREATE PAYMENT PAGE ====================

    public function test_create_payment_page_success_returns_data(): void
    {
        $this->client->addSuccessResponse([
            'redirectUrl' => 'https://sandbox.paywith.pawapay.io/v2?token=xxx',
        ]);

        $data = $this->service->createPaymentPage($this->createCreatePaymentPageRecord());

        $this->assertInstanceOf(CreatePaymentPageData::class, $data);
        $this->assertSame('https://sandbox.paywith.pawapay.io/v2?token=xxx', $data->redirectUrl->getValue());
        $this->assertNull($data->failureReason);
        $this->assertFalse($data->hasFailureReason);
    }

    public function test_create_payment_page_rejected_returns_data(): void
    {
        $this->client->addSuccessResponse([
            'redirectUrl' => 'https://sandbox.paywith.pawapay.io/v2?token=xxx',
            'failureReason' => [
                'failureCode' => 'INVALID_PHONE_NUMBER',
                'failureMessage' => "The phone number '243812345678' seems to be invalid for the provider 'VODACOM_MPESA_COD'.",
            ],
        ]);

        $data = $this->service->createPaymentPage($this->createCreatePaymentPageRecord());

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
        $this->client->addAuthenticationErrorResponse();

        $data = $this->service->createPaymentPage($this->createCreatePaymentPageRecord());

        $this->assertTrue($data->hasFailureReason);
        $this->assertNotNull($data->failureReason);
        $this->assertSame(FailureCode::AUTHENTICATION_ERROR, $data->failureReason->failureCode);
    }
}
