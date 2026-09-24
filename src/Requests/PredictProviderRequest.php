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
use AndyDefer\PhpPawapay\Structures\PredictProviderStruct;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

final class PredictProviderRequest extends Request
{
    private PhoneNumberVO $phoneNumber;

    private PawaPayBaseUrl $baseUrl;

    public function __construct(PhoneNumberVO $phoneNumber, PawaPayBaseUrl $baseUrl = PawaPayBaseUrl::SANDBOX)
    {
        $this->phoneNumber = $phoneNumber;
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
        return new UrlVO(
            $this->baseUrl->value.ltrim(Endpoint::PREDICT_PROVIDER->value, '/')
        );
    }

    protected function setBody(): RequestBodyVO
    {
        $struct = new PredictProviderStruct(
            phoneNumber: $this->phoneNumber,
        );

        return new RequestBodyVO($struct, ContentType::JSON);
    }
}
