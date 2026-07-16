<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay;

use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Enums\Endpoint;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Requests\InitiateDepositRequest;
use AndyDefer\PhpPawapay\Responses\InitiateDepositResponse;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;

final class PawapayClient
{
    private ClientService $client;

    private string $apiToken;

    private PawaPayBaseUrl $baseUrl;

    public function __construct(string $apiToken, PawaPayBaseUrl $baseUrl = PawaPayBaseUrl::SANDBOX, ?ClientService $client = null)
    {
        $this->apiToken = $apiToken;
        $this->baseUrl = $baseUrl;
        $this->client = $client ?? new ClientService;
    }

    /**
     * Build a full URL from an endpoint.
     */
    private function buildUrl(Endpoint $endpoint): UrlVO
    {
        return new UrlVO($this->baseUrl->value.ltrim($endpoint->value, '/'));
    }

    public function initiateDeposit(InitiateDepositVO $deposit): InitiateDepositResponse
    {
        $request = new InitiateDepositRequest($deposit);

        $request->getHeaders()
            ->setAuthorization($this->apiToken)
            ->setContentType(ContentType::JSON)
            ->setAccept(ContentType::JSON);

        $request->getOptions()
            ->setTimeout(30)
            ->setConnectTimeout(10)
            ->setHttpErrors(false);

        $url = $this->buildUrl(Endpoint::DEPOSITS_INITIATE);

        return $this->client->post(
            $url->getValue(),
            $request,
            InitiateDepositResponse::class
        );
    }
}
