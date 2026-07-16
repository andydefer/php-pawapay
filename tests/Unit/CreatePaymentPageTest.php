<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\Language;
use AndyDefer\PhpPawapay\Graphs\AmountDetailsGraph;
use AndyDefer\PhpPawapay\Structures\PaymentPageStruct;
use AndyDefer\PhpPawapay\Tests\MockPawapayClient;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\MessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
use PHPUnit\Framework\TestCase;

final class CreatePaymentPageTest extends TestCase
{
    private MockPawapayClient $client;

    protected function setUp(): void
    {
        $this->client = new MockPawapayClient;
    }

    private function createPaymentPageStruct(): PaymentPageStruct
    {
        $depositId = new UuidVO('9b724dbf-32a7-4e63-96bb-59a4747e43ca');
        $phoneNumber = new PhoneNumberVO('243812345678');
        $amount = new AmountVO(25.50);

        $amountDetails = new AmountDetailsGraph(
            amount: $amount,
            currency: Currency::USD
        );

        $metadata = new MetadataVO(
            new StrictDataObject([
                'orderId' => 'ORD-RDC-123456789',
                'customerId' => 'customer@email.com',
            ])
        );

        return new PaymentPageStruct(
            depositId: $depositId,
            returnUrl: new UrlVO('https://merchant.example.com/checkout-result'),
            amountDetails: $amountDetails,
            phoneNumber: $phoneNumber,
            language: Language::EN,
            country: Country::COD,
            customerMessage: new MessageVO('Payment for order RDC-123456'),
            reason: new MessageVO('Ticket to festival'),
            metadata: $metadata,
        );
    }

    public function test_create_payment_page_success(): void
    {
        $depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';

        $this->client->addSuccessResponse([
            'redirectUrl' => 'https://sandbox.paywith.pawapay.io/v2?token=xxx',
        ]);

        $paymentPage = $this->createPaymentPageStruct();
        $response = $this->client->createPaymentPage($paymentPage);

        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->hasFailureReason());
        $this->assertNull($response->getFailureReason());

        $redirectUrl = $response->getRedirectUrl();
        $this->assertNotNull($redirectUrl);
        $this->assertStringContainsString('sandbox.paywith.pawapay.io', $redirectUrl->getValue());
    }

    public function test_create_payment_page_rejected_provider_temporarily_unavailable(): void
    {
        $this->client->addSuccessResponse([
            'failureReason' => [
                'failureCode' => 'PROVIDER_TEMPORARILY_UNAVAILABLE',
                'failureMessage' => "The provider 'VODACOM_MPESA_COD' is currently not able to process payments.",
            ],
        ]);

        $paymentPage = $this->createPaymentPageStruct();
        $response = $this->client->createPaymentPage($paymentPage);

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('PROVIDER_TEMPORARILY_UNAVAILABLE', $failure->failureCode);
    }

    public function test_create_payment_page_rejected_invalid_phone_number(): void
    {
        $this->client->addSuccessResponse([
            'failureReason' => [
                'failureCode' => 'INVALID_PHONE_NUMBER',
                'failureMessage' => "The phone number '243812345678' seems to be invalid for the provider 'VODACOM_MPESA_COD'.",
            ],
        ]);

        $paymentPage = $this->createPaymentPageStruct();
        $response = $this->client->createPaymentPage($paymentPage);

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('INVALID_PHONE_NUMBER', $failure->failureCode);
    }

    public function test_create_payment_page_rejected_invalid_currency(): void
    {
        $this->client->addSuccessResponse([
            'failureReason' => [
                'failureCode' => 'INVALID_CURRENCY',
                'failureMessage' => "The currency 'EUR' is not supported with provider 'VODACOM_MPESA_COD'.",
            ],
        ]);

        $paymentPage = $this->createPaymentPageStruct();
        $response = $this->client->createPaymentPage($paymentPage);

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('INVALID_CURRENCY', $failure->failureCode);
    }

    public function test_create_payment_page_rejected_invalid_amount(): void
    {
        $this->client->addSuccessResponse([
            'failureReason' => [
                'failureCode' => 'INVALID_AMOUNT',
                'failureMessage' => "The provider VODACOM_MPESA_COD only supports up to '2' decimal places in amount.",
            ],
        ]);

        $paymentPage = $this->createPaymentPageStruct();
        $response = $this->client->createPaymentPage($paymentPage);

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('INVALID_AMOUNT', $failure->failureCode);
    }

    public function test_create_payment_page_authentication_error(): void
    {
        $this->client->addAuthenticationErrorResponse();

        $paymentPage = $this->createPaymentPageStruct();
        $response = $this->client->createPaymentPage($paymentPage);

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('AUTHENTICATION_ERROR', $failure->failureCode);
        $this->assertSame('The API token in the request is invalid.', $failure->failureMessage);
    }

    public function test_create_payment_page_authorisation_error(): void
    {
        $this->client->addAuthorisationErrorResponse();

        $paymentPage = $this->createPaymentPageStruct();
        $response = $this->client->createPaymentPage($paymentPage);

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('AUTHORISATION_ERROR', $failure->failureCode);
        $this->assertSame('The API token in the request is not authorised for this endpoint.', $failure->failureMessage);
    }

    public function test_create_payment_page_unknown_error(): void
    {
        $this->client->addErrorResponse(500, 'UNKNOWN_ERROR', 'Unable to process request due to an unknown problem.');

        $paymentPage = $this->createPaymentPageStruct();
        $response = $this->client->createPaymentPage($paymentPage);

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('UNKNOWN_ERROR', $failure->failureCode);
        $this->assertSame('Unable to process request due to an unknown problem.', $failure->failureMessage);
    }
}
