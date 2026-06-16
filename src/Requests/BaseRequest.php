<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Requests;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Normalizers\NormalizerChain;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpPawapay\Contracts\RequestInterface;
use AndyDefer\PhpPawapay\ValueObjects\HeadersVO;
use AndyDefer\PhpPawapay\ValueObjects\OptionsVO;

abstract class BaseRequest implements RequestInterface
{
    protected HeadersVO $headers;

    protected OptionsVO $options;

    public function __construct()
    {
        $this->headers = new HeadersVO;
        $this->options = new OptionsVO;
    }

    abstract protected function setBody(): AbstractValueObject;

    final public function getBody(): StrictDataObject
    {
        $normalized = NormalizerChain::get()->normalize($this->setBody());

        return new StrictDataObject($normalized);
    }

    final public function getHeaders(): HeadersVO
    {
        return $this->headers;
    }

    final public function getOptions(): OptionsVO
    {
        return $this->options;
    }
}
