<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpPawapay\ValueObjects\HeadersVO;
use AndyDefer\PhpPawapay\ValueObjects\OptionsVO;

interface RequestInterface
{
    public function getBody(): StrictDataObject;

    public function getHeaders(): HeadersVO;

    public function getOptions(): OptionsVO;
}
