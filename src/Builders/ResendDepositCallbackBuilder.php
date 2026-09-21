<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Builders;

use AndyDefer\PhpPawapay\Contracts\Responses\ResendDepositCallbackResponseInterface;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;

final class ResendDepositCallbackBuilder
{
    private string $apiToken;

    private PawaPayBaseUrl $baseUrl;

    private string $depositId;

    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;
        $this->baseUrl = PawaPayBaseUrl::SANDBOX;
    }

    public function withBaseUrl(PawaPayBaseUrl $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function withDepositId(string $depositId): self
    {
        $this->depositId = $depositId;

        return $this;
    }

    public function build(): PawapayClient
    {
        return new PawapayClient(
            apiToken: $this->apiToken,
            baseUrl: $this->baseUrl
        );
    }

    public function execute(): ResendDepositCallbackResponseInterface
    {
        $client = $this->build();

        return $client->resendDepositCallback($this->depositId);
    }

    public static function create(string $apiToken): self
    {
        return new self($apiToken);
    }
}
