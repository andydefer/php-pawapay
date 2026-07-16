<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit;

use AndyDefer\PhpPawapay\Enums\ResendCallbackStatus;
use AndyDefer\PhpPawapay\Tests\MockPawapayClient;
use PHPUnit\Framework\TestCase;

final class ResendDepositCallbackTest extends TestCase
{
    private MockPawapayClient $client;

    protected function setUp(): void
    {
        $this->client = new MockPawapayClient;
    }

    // ==================== 200 ACCEPTED ====================

    public function test_resend_deposit_callback_accepted(): void
    {
        $depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';

        $this->client->addSuccessResponse([
            'depositId' => $depositId,
            'status' => 'ACCEPTED',
        ]);

        $response = $this->client->resendDepositCallback($depositId);

        $this->assertTrue($response->isAccepted());
        $this->assertFalse($response->isRejected());
        $this->assertSame($depositId, $response->getDepositId());
        $this->assertSame(ResendCallbackStatus::ACCEPTED, $response->getStatus());
        $this->assertFalse($response->hasFailureReason());
        $this->assertNull($response->getFailureReason());
    }

    // ==================== 200 REJECTED - NOT_FOUND ====================

    public function test_resend_deposit_callback_rejected_not_found(): void
    {
        $depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';

        $this->client->addSuccessResponse([
            'depositId' => $depositId,
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'NOT_FOUND',
                'failureMessage' => "Payout with ID {$depositId} not found",
            ],
        ]);

        $response = $this->client->resendDepositCallback($depositId);

        $this->assertFalse($response->isAccepted());
        $this->assertTrue($response->isRejected());
        $this->assertSame($depositId, $response->getDepositId());
        $this->assertSame(ResendCallbackStatus::REJECTED, $response->getStatus());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('NOT_FOUND', $failure->failureCode);
        $this->assertSame("Payout with ID {$depositId} not found", $failure->failureMessage);
    }

    // ==================== 200 REJECTED - INVALID_STATE ====================

    public function test_resend_deposit_callback_rejected_invalid_state(): void
    {
        $depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';

        $this->client->addSuccessResponse([
            'depositId' => $depositId,
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'INVALID_STATE',
                'failureMessage' => "Payout with ID {$depositId} has not finished processing",
            ],
        ]);

        $response = $this->client->resendDepositCallback($depositId);

        $this->assertFalse($response->isAccepted());
        $this->assertTrue($response->isRejected());
        $this->assertSame($depositId, $response->getDepositId());
        $this->assertSame(ResendCallbackStatus::REJECTED, $response->getStatus());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('INVALID_STATE', $failure->failureCode);
        $this->assertSame("Payout with ID {$depositId} has not finished processing", $failure->failureMessage);
    }

    // ==================== 401 AUTHENTICATION_ERROR ====================

    public function test_resend_deposit_callback_authentication_error(): void
    {
        $depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';

        $this->client->addAuthenticationErrorResponse();

        $response = $this->client->resendDepositCallback($depositId);

        $this->assertFalse($response->isAccepted());
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('AUTHENTICATION_ERROR', $failure->failureCode);
        $this->assertSame('The API token in the request is invalid.', $failure->failureMessage);
    }

    // ==================== 403 AUTHORISATION_ERROR ====================

    public function test_resend_deposit_callback_authorisation_error(): void
    {
        $depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';

        $this->client->addAuthorisationErrorResponse();

        $response = $this->client->resendDepositCallback($depositId);

        $this->assertFalse($response->isAccepted());
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('AUTHORISATION_ERROR', $failure->failureCode);
        $this->assertSame('The API token in the request is not authorised for this endpoint.', $failure->failureMessage);
    }

    // ==================== 500 UNKNOWN_ERROR ====================

    public function test_resend_deposit_callback_unknown_error(): void
    {
        $depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';

        $this->client->addErrorResponse(500, 'UNKNOWN_ERROR', 'Unable to process request due to an unknown problem.');

        $response = $this->client->resendDepositCallback($depositId);

        $this->assertFalse($response->isAccepted());
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertTrue($response->hasFailureReason());

        $failure = $response->getFailureReason();
        $this->assertNotNull($failure);
        $this->assertSame('UNKNOWN_ERROR', $failure->failureCode);
        $this->assertSame('Unable to process request due to an unknown problem.', $failure->failureMessage);
    }
}
