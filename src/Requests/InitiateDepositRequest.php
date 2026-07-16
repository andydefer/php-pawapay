<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Requests;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Enums\Endpoint;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\Graphs\AccountDetailsGraph;
use AndyDefer\PhpPawapay\Graphs\PayerGraph;
use AndyDefer\PhpPawapay\Structures\InitiateDepositStruct;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;

final class InitiateDepositRequest extends Request
{
    private InitiateDepositVO $deposit;

    private PawaPayBaseUrl $baseUrl;

    public function __construct(InitiateDepositVO $deposit, PawaPayBaseUrl $baseUrl = PawaPayBaseUrl::SANDBOX)
    {
        $this->deposit = $deposit;
        $this->baseUrl = $baseUrl;
        parent::__construct();
    }

    /**
     * Set the base URL for the request.
     */
    public function setBaseUrl(PawaPayBaseUrl $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    protected function setUrl(): UrlVO
    {
        return new UrlVO($this->baseUrl->value.ltrim(Endpoint::DEPOSITS_INITIATE->value, '/'));
    }

    protected function setBody(): RequestBodyVO
    {
        $accountDetails = new AccountDetailsGraph(
            phoneNumber: $this->deposit->payer->accountDetails->phoneNumber->getValue(),
            provider: $this->deposit->payer->accountDetails->provider,
        );

        $payer = new PayerGraph(
            type: $this->deposit->payer->type,
            accountDetails: $accountDetails,
        );

        $metadata = null;
        if ($this->deposit->metadata !== null && $this->deposit->metadata->getValue() !== null) {
            $metadata = $this->deposit->metadata->getValue()->toArray();
        }

        $struct = new InitiateDepositStruct(
            depositId: $this->deposit->depositId->getValue(),
            payer: $payer,
            amount: (string) $this->deposit->amount->getValue(),
            currency: $this->deposit->currency,
            preAuthorisationCode: $this->deposit->preAuthorisationCode?->getValue(),
            clientReferenceId: $this->deposit->clientReferenceId?->getValue(),
            customerMessage: $this->deposit->customerMessage?->getValue(),
            metadata: $metadata,
        );

        return new RequestBodyVO($struct, ContentType::JSON);
    }
}
