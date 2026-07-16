<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\PawapayClient;

use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\DepositStatus;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\Tests\MockPawapayClient;
use PHPUnit\Framework\TestCase;

final class CheckDepositStatusTest extends TestCase
{
    private MockPawapayClient $client;

    protected function setUp(): void
    {
        $this->client = new MockPawapayClient;
    }

    // ==================== 200 FOUND ====================

    public function test_check_deposit_status_found_completed_rdc_usd(): void
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
            'customerMessage' => 'Payment for order RDC-123456',
            'created' => '2020-10-19T08:17:01Z',
            'providerTransactionId' => '12356789',
        ]);

        $response = $this->client->checkDepositStatus('8917c345-4791-4285-a416-62f24b6982db');

        $this->assertTrue($response->isFound());
        $this->assertFalse($response->isNotFound());

        $data = $response->getDepositData();
        $this->assertNotNull($data);
        $this->assertSame('8917c345-4791-4285-a416-62f24b6982db', $data->depositId);
        $this->assertSame(DepositStatus::COMPLETED, $data->status);
        $this->assertSame('25.50', $data->amount);
        $this->assertSame(Currency::USD, $data->currency);
        $this->assertSame(Country::COD, $data->country);
        $this->assertSame(Provider::VODACOM_MPESA_COD, $data->payer->accountDetails->provider);
        $this->assertSame('243812345678', $data->payer->accountDetails->phoneNumber->getValue());
        $this->assertSame('REF-RDC-123456', $data->clientReferenceId);
        $this->assertSame('Payment for order RDC-123456', $data->customerMessage);
        $this->assertSame('2020-10-19T08:17:01Z', $data->created);
        $this->assertSame('12356789', $data->providerTransactionId);
        $this->assertNull($data->failureReason);
    }

    public function test_check_deposit_status_found_accepted_rdc_usd(): void
    {
        $this->client->addDepositFoundResponse([
            'depositId' => '8917c345-4791-4285-a416-62f24b6982db',
            'status' => 'ACCEPTED',
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

        $response = $this->client->checkDepositStatus('8917c345-4791-4285-a416-62f24b6982db');

        $this->assertTrue($response->isFound());

        $data = $response->getDepositData();
        $this->assertNotNull($data);
        $this->assertSame(DepositStatus::ACCEPTED, $data->status);
        $this->assertSame(Currency::USD, $data->currency);
        $this->assertSame(Country::COD, $data->country);
        $this->assertSame(Provider::VODACOM_MPESA_COD, $data->payer->accountDetails->provider);
        $this->assertSame('243812345678', $data->payer->accountDetails->phoneNumber->getValue());
        $this->assertNull($data->failureReason);
    }

    public function test_check_deposit_status_found_processing_rdc_usd(): void
    {
        $this->client->addDepositFoundResponse([
            'depositId' => '8917c345-4791-4285-a416-62f24b6982db',
            'status' => 'PROCESSING',
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

        $response = $this->client->checkDepositStatus('8917c345-4791-4285-a416-62f24b6982db');

        $this->assertTrue($response->isFound());

        $data = $response->getDepositData();
        $this->assertNotNull($data);
        $this->assertSame(DepositStatus::PROCESSING, $data->status);
        $this->assertSame(Currency::USD, $data->currency);
        $this->assertSame(Country::COD, $data->country);
        $this->assertSame(Provider::VODACOM_MPESA_COD, $data->payer->accountDetails->provider);
        $this->assertSame('243812345678', $data->payer->accountDetails->phoneNumber->getValue());
        $this->assertNull($data->clientReferenceId);
        $this->assertNull($data->customerMessage);
        $this->assertNull($data->providerTransactionId);
        $this->assertNull($data->failureReason);
    }

    public function test_check_deposit_status_found_in_reconciliation_rdc_usd(): void
    {
        $this->client->addDepositFoundResponse([
            'depositId' => '8917c345-4791-4285-a416-62f24b6982db',
            'status' => 'IN_RECONCILIATION',
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

        $response = $this->client->checkDepositStatus('8917c345-4791-4285-a416-62f24b6982db');

        $this->assertTrue($response->isFound());

        $data = $response->getDepositData();
        $this->assertNotNull($data);
        $this->assertSame(DepositStatus::IN_RECONCILIATION, $data->status);
        $this->assertSame(Currency::USD, $data->currency);
        $this->assertSame(Country::COD, $data->country);
        $this->assertSame(Provider::VODACOM_MPESA_COD, $data->payer->accountDetails->provider);
        $this->assertSame('243812345678', $data->payer->accountDetails->phoneNumber->getValue());
        $this->assertNull($data->failureReason);
    }

    public function test_check_deposit_status_found_failed_with_failure_reason_rdc_usd(): void
    {
        $this->client->addDepositFoundResponse([
            'depositId' => '8917c345-4791-4285-a416-62f24b6982db',
            'status' => 'FAILED',
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
            'failureReason' => [
                'failureCode' => 'PAYMENT_NOT_APPROVED',
                'failureMessage' => 'The customer did not approve the authorisation for this payment',
            ],
        ]);

        $response = $this->client->checkDepositStatus('8917c345-4791-4285-a416-62f24b6982db');

        $this->assertTrue($response->isFound());

        $data = $response->getDepositData();
        $this->assertNotNull($data);
        $this->assertSame(DepositStatus::FAILED, $data->status);
        $this->assertSame(Currency::USD, $data->currency);
        $this->assertSame(Country::COD, $data->country);
        $this->assertSame(Provider::VODACOM_MPESA_COD, $data->payer->accountDetails->provider);
        $this->assertSame('243812345678', $data->payer->accountDetails->phoneNumber->getValue());
        $this->assertNotNull($data->failureReason);
        $this->assertSame('PAYMENT_NOT_APPROVED', $data->failureReason->failureCode);
        $this->assertSame('The customer did not approve the authorisation for this payment', $data->failureReason->failureMessage);
    }

    // ==================== 200 NOT_FOUND ====================

    public function test_check_deposit_status_not_found(): void
    {
        $this->client->addNotFoundResponse();

        $response = $this->client->checkDepositStatus('f4401bd2-1568-4140-bf2d-eb77d2b2b639');

        $this->assertFalse($response->isFound());
        $this->assertTrue($response->isNotFound());
        $this->assertNull($response->getDepositData());
        $this->assertFalse($response->hasFailureReason());
    }

    // ==================== 401 AUTHENTICATION_ERROR ====================

    public function test_check_deposit_status_authentication_error(): void
    {
        $this->client->addAuthenticationErrorResponse();

        $response = $this->client->checkDepositStatus('8917c345-4791-4285-a416-62f24b6982db');

        $this->assertFalse($response->isFound());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('AUTHENTICATION_ERROR', $failure->failureCode);
        $this->assertSame('The API token in the request is invalid.', $failure->failureMessage);
    }

    // ==================== 403 AUTHORISATION_ERROR ====================

    public function test_check_deposit_status_authorisation_error(): void
    {
        $this->client->addAuthorisationErrorResponse();

        $response = $this->client->checkDepositStatus('8917c345-4791-4285-a416-62f24b6982db');

        $this->assertFalse($response->isFound());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('AUTHORISATION_ERROR', $failure->failureCode);
        $this->assertSame('The API token in the request is not authorised for this endpoint.', $failure->failureMessage);
    }

    // ==================== 500 UNKNOWN_ERROR ====================

    public function test_check_deposit_status_unknown_error(): void
    {
        $this->client->addErrorResponse(500, 'UNKNOWN_ERROR', 'Unable to process request due to an unknown problem.');

        $response = $this->client->checkDepositStatus('8917c345-4791-4285-a416-62f24b6982db');

        $this->assertFalse($response->isFound());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('UNKNOWN_ERROR', $failure->failureCode);
        $this->assertSame('Unable to process request due to an unknown problem.', $failure->failureMessage);
    }
}
