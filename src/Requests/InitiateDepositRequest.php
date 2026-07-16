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
        $struct = InitiateDepositStruct::from([
            'depositId' => $this->deposit->depositId,
            'payer' => $this->deposit->payer,
            'amount' => $this->deposit->amount,
            'currency' => $this->deposit->currency,
            'preAuthorisationCode' => $this->deposit->preAuthorisationCode,
            'clientReferenceId' => $this->deposit->clientReferenceId,
            'customerMessage' => $this->deposit->customerMessage,
            'metadata' => $this->deposit->metadata,
        ]);

        return new RequestBodyVO($struct, ContentType::JSON);
    }
}
