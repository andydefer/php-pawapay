<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\PawapayClient;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\PayerType;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\Tests\MockPawapayClient;
use AndyDefer\PhpPawapay\ValueObjects\AccountDetailsVO;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;
use AndyDefer\PhpPawapay\ValueObjects\MessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\ReferenceVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
use PHPUnit\Framework\TestCase;

final class InitiateDepositTest extends TestCase
{
    private MockPawapayClient $client;

    protected function setUp(): void
    {
        $this->client = new MockPawapayClient;
    }

    private function createDeposit(): InitiateDepositVO
    {
        $depositId = new UuidVO('f4401bd2-1568-4140-bf2d-eb77d2b2b639');
        $phoneNumber = new PhoneNumberVO('260763456789');
        $amount = new AmountVO(15.00);

        $accountDetails = new AccountDetailsVO(
            phoneNumber: $phoneNumber,
            provider: Provider::MTN_MOMO_ZMB
        );

        $payer = new PayerVO(
            type: PayerType::MMO,
            accountDetails: $accountDetails
        );

        $metadata = new MetadataVO(
            new StrictDataObject([
                'orderId' => 'ORD-123456789',
                'customerId' => 'customer@email.com',
                'isPII' => true,
            ])
        );

        return new InitiateDepositVO(
            depositId: $depositId,
            payer: $payer,
            amount: $amount,
            currency: Currency::ZMW,
            preAuthorisationCode: null,
            clientReferenceId: new ReferenceVO('INV-123456'),
            customerMessage: new MessageVO('Payment for order #123456'),
            metadata: $metadata,
        );
    }

    // ==================== 200 SUCCESS ====================

    public function test_initiate_deposit_returns_accepted(): void
    {
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'ACCEPTED',
            'created' => '2020-10-19T11:17:01Z',
        ]);

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->isAccepted());
        $this->assertSame('f4401bd2-1568-4140-bf2d-eb77d2b2b639', $response->getDepositId());
        $this->assertSame('ACCEPTED', $response->getStatus()->value);
        $this->assertSame('2020-10-19T11:17:01Z', $response->getCreated());
        $this->assertFalse($response->hasFailureReason());
    }

    public function test_initiate_deposit_returns_duplicate_ignored(): void
    {
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'DUPLICATE_IGNORED',
            'created' => '2020-10-19T11:17:01Z',
        ]);

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->isDuplicateIgnored());
        $this->assertSame('DUPLICATE_IGNORED', $response->getStatus()->value);
        $this->assertFalse($response->hasFailureReason());
    }

    // ==================== 200 REJECTED - PROVIDER ERRORS ====================

    public function test_initiate_deposit_rejected_provider_temporarily_unavailable(): void
    {
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'PROVIDER_TEMPORARILY_UNAVAILABLE',
                'failureMessage' => "The provider 'MTN_MOMO_ZMB' is currently not able to process payments.",
            ],
        ]);

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->isRejected());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('PROVIDER_TEMPORARILY_UNAVAILABLE', $failure->failureCode);
    }

    public function test_initiate_deposit_rejected_invalid_phone_number(): void
    {
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'INVALID_PHONE_NUMBER',
                'failureMessage' => "The phone number '2607634' seems to be invalid for the provider 'MTN_MOMO_ZMB'.",
            ],
        ]);

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->isRejected());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('INVALID_PHONE_NUMBER', $failure->failureCode);
    }

    public function test_initiate_deposit_rejected_invalid_currency(): void
    {
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'INVALID_CURRENCY',
                'failureMessage' => "The currency 'USD' is not supported with provider 'MTN_MOMO_ZMB'.",
            ],
        ]);

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->isRejected());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('INVALID_CURRENCY', $failure->failureCode);
    }

    public function test_initiate_deposit_rejected_invalid_amount(): void
    {
        $this->client->addSuccessResponse([
            'depositId' => 'f4401bd2-1568-4140-bf2d-eb77d2b2b639',
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'INVALID_AMOUNT',
                'failureMessage' => "The provider MTN_MOMO_ZMB only supports up to '2' decimal places in amount.",
            ],
        ]);

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->isRejected());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('INVALID_AMOUNT', $failure->failureCode);
    }

    public function test_initiate_deposit_rejected_amount_out_of_bounds(): void
    {
        $this->client->addSuccessResponse([
            'depositId' => null,
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'AMOUNT_OUT_OF_BOUNDS',
                'failureMessage' => "The amount needs to be more than '1' and less than '20000' for provider 'MTN_MOMO_ZMB'.",
            ],
        ]);

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->isRejected());
        $this->assertTrue($response->hasFailureReason());
        $this->assertNull($response->getDepositId());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('AMOUNT_OUT_OF_BOUNDS', $failure->failureCode);
    }

    // ==================== 400 BAD REQUEST ====================

    public function test_initiate_deposit_400_invalid_input(): void
    {
        $this->client->addErrorResponse(400, 'INVALID_INPUT', 'We are unable to parse the body of the request.');

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('INVALID_INPUT', $failure->failureCode);
    }

    public function test_initiate_deposit_400_missing_parameter(): void
    {
        $this->client->addErrorResponse(400, 'MISSING_PARAMETER', "Request does not include the required parameter 'amount'.");

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('MISSING_PARAMETER', $failure->failureCode);
    }

    public function test_initiate_deposit_400_invalid_parameter(): void
    {
        $this->client->addErrorResponse(400, 'INVALID_PARAMETER', "Value for parameter 'provider' is invalid.");

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('INVALID_PARAMETER', $failure->failureCode);
    }

    public function test_initiate_deposit_400_unsupported_parameter(): void
    {
        $this->client->addErrorResponse(400, 'UNSUPPORTED_PARAMETER', "Request includes an unsupported parameter 'amnt'.");

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('UNSUPPORTED_PARAMETER', $failure->failureCode);
    }

    // ==================== 401 UNAUTHORIZED ====================

    public function test_initiate_deposit_401_authentication_error(): void
    {
        $this->client->addErrorResponse(401, 'AUTHENTICATION_ERROR', 'The API token in the request is invalid.');

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('AUTHENTICATION_ERROR', $failure->failureCode);
    }

    // ==================== 403 FORBIDDEN ====================

    public function test_initiate_deposit_403_authorisation_error(): void
    {
        $this->client->addErrorResponse(403, 'AUTHORISATION_ERROR', 'The API token in the request is not authorised for this endpoint.');

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('AUTHORISATION_ERROR', $failure->failureCode);
    }

    // ==================== 500 INTERNAL SERVER ERROR ====================

    public function test_initiate_deposit_500_unknown_error(): void
    {
        $this->client->addErrorResponse(500, 'UNKNOWN_ERROR', 'Unable to process request due to an unknown problem.');

        $response = $this->client->initiateDeposit($this->createDeposit());

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('UNKNOWN_ERROR', $failure->failureCode);
    }
}
