<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Requests;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Enums\Endpoint;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;

final class ResendDepositCallbackRequest extends Request
{
    private string $depositId;

    private PawaPayBaseUrl $baseUrl;

    public function __construct(string $depositId, PawaPayBaseUrl $baseUrl = PawaPayBaseUrl::SANDBOX)
    {
        $this->depositId = $depositId;
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
        $path = str_replace('{depositId}', $this->depositId, Endpoint::DEPOSITS_RESEND_CALLBACK->value);

        return new UrlVO($this->baseUrl->value.ltrim($path, '/'));
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }
}
