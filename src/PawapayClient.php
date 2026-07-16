<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay;

use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Requests\CheckDepositStatusRequest;
use AndyDefer\PhpPawapay\Requests\CreatePaymentPageRequest;
use AndyDefer\PhpPawapay\Requests\InitiateDepositRequest;
use AndyDefer\PhpPawapay\Requests\ResendDepositCallbackRequest;
use AndyDefer\PhpPawapay\Responses\CheckDepositStatusResponse;
use AndyDefer\PhpPawapay\Responses\CreatePaymentPageResponse;
use AndyDefer\PhpPawapay\Responses\InitiateDepositResponse;
use AndyDefer\PhpPawapay\Responses\ResendDepositCallbackResponse;
use AndyDefer\PhpPawapay\Structures\PaymentPageStruct;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;

class PawapayClient
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

    public function setBaseUrl(PawaPayBaseUrl $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function initiateDeposit(InitiateDepositVO $deposit): InitiateDepositResponse
    {
        $request = new InitiateDepositRequest($deposit, $this->baseUrl);

        $request->getHeaders()
            ->setAuthorization($this->apiToken)
            ->setContentType(ContentType::JSON)
            ->setAccept(ContentType::JSON);

        $request->getOptions()
            ->setTimeout(30)
            ->setConnectTimeout(10)
            ->setHttpErrors(false);

        return $this->client->post(
            $request->getUrl()->getValue(),
            $request,
            InitiateDepositResponse::class
        );
    }

    public function checkDepositStatus(string $depositId): CheckDepositStatusResponse
    {
        $request = new CheckDepositStatusRequest($depositId, $this->baseUrl);

        $request->getHeaders()
            ->setAuthorization($this->apiToken)
            ->setAccept(ContentType::JSON);

        $request->getOptions()
            ->setTimeout(30)
            ->setConnectTimeout(10)
            ->setHttpErrors(false);

        return $this->client->get(
            $request->getUrl()->getValue(),
            $request,
            CheckDepositStatusResponse::class
        );
    }

    public function resendDepositCallback(string $depositId): ResendDepositCallbackResponse
    {
        $request = new ResendDepositCallbackRequest($depositId, $this->baseUrl);

        $request->getHeaders()
            ->setAuthorization($this->apiToken)
            ->setContentType(ContentType::JSON)
            ->setAccept(ContentType::JSON);

        $request->getOptions()
            ->setTimeout(30)
            ->setConnectTimeout(10)
            ->setHttpErrors(false);

        return $this->client->post(
            $request->getUrl()->getValue(),
            $request,
            ResendDepositCallbackResponse::class
        );
    }

    public function createPaymentPage(PaymentPageStruct $paymentPage): CreatePaymentPageResponse
    {
        $request = new CreatePaymentPageRequest($paymentPage, $this->baseUrl);

        $request->getHeaders()
            ->setAuthorization($this->apiToken)
            ->setContentType(ContentType::JSON)
            ->setAccept(ContentType::JSON);

        $request->getOptions()
            ->setTimeout(30)
            ->setConnectTimeout(10)
            ->setHttpErrors(false);

        return $this->client->post(
            $request->getUrl()->getValue(),
            $request,
            CreatePaymentPageResponse::class
        );
    }
}
