<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests;

use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;

final class MockPawapayClient extends PawapayClient
{
    private MockHandler $mockHandler;

    public function __construct(string $apiToken = 'mock-token')
    {
        $this->mockHandler = new MockHandler;
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $clientService = new ClientService($guzzleClient);

        parent::__construct($apiToken, PawaPayBaseUrl::SANDBOX, $clientService);
    }

    public function addResponse(int $status, array $headers, string $body): void
    {
        $this->mockHandler->append(new Response($status, $headers, $body));
    }

    public function addSuccessResponse(array $data): void
    {
        $this->addResponse(200, ['Content-Type' => 'application/json'], json_encode($data));
    }

    public function addErrorResponse(int $status, string $failureCode, string $failureMessage): void
    {
        $this->addResponse($status, ['Content-Type' => 'application/json'], json_encode([
            'failureReason' => [
                'failureCode' => $failureCode,
                'failureMessage' => $failureMessage,
            ],
        ]));
    }

    public function addAuthenticationErrorResponse(): void
    {
        $this->addErrorResponse(401, 'AUTHENTICATION_ERROR', 'The API token in the request is invalid.');
    }

    public function addAuthorisationErrorResponse(): void
    {
        $this->addErrorResponse(403, 'AUTHORISATION_ERROR', 'The API token in the request is not authorised for this endpoint.');
    }

    public function addNotFoundResponse(): void
    {
        $this->addResponse(200, ['Content-Type' => 'application/json'], json_encode([
            'status' => 'NOT_FOUND',
        ]));
    }

    public function addDepositFoundResponse(array $data): void
    {
        $this->addSuccessResponse([
            'status' => 'FOUND',
            'data' => $data,
        ]);
    }

    public function addPredictProviderFoundResponse(string $country, string $provider, string $phoneNumber): void
    {
        $this->addSuccessResponse([
            'country' => $country,
            'provider' => $provider,
            'phoneNumber' => $phoneNumber,
        ]);
    }

    public function addPredictProviderNotFoundResponse(): void
    {
        $this->addSuccessResponse([]);
    }

    public function addPredictProviderErrorResponse(int $status, string $failureCode, string $failureMessage): void
    {
        $this->addErrorResponse($status, $failureCode, $failureMessage);
    }

    public function getMockHandler(): MockHandler
    {
        return $this->mockHandler;
    }

    public function assertRequestCount(int $expectedCount): void
    {
        $request = $this->mockHandler->getLastRequest();
        $count = $request === null ? 0 : 1;
        Assert::assertEquals($expectedCount, $count);
    }

    public function assertRequestUri(string $expectedUri): void
    {
        $request = $this->mockHandler->getLastRequest();
        if ($request === null) {
            Assert::fail('No request was made');
        }
        Assert::assertEquals($expectedUri, (string) $request->getUri());
    }
}
