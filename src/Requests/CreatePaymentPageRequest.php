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
use AndyDefer\PhpPawapay\Structures\PaymentPageStruct;

final class CreatePaymentPageRequest extends Request
{
    private PaymentPageStruct $paymentPage;

    private PawaPayBaseUrl $baseUrl;

    public function __construct(PaymentPageStruct $paymentPage, PawaPayBaseUrl $baseUrl = PawaPayBaseUrl::SANDBOX)
    {
        $this->paymentPage = $paymentPage;
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
        return new UrlVO($this->baseUrl->value.ltrim(Endpoint::PAYMENT_PAGE->value, '/'));
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO($this->paymentPage, ContentType::JSON);
    }
}
