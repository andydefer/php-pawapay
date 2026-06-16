<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpPawapay\ValueObjects\HeadersVO;
use AndyDefer\PhpVo\Enums\HttpStatusCode;

interface ResponseInterface
{
    public function getStatusCode(): HttpStatusCode;

    public function getBody(): StrictDataObject;

    public function getHeaders(): HeadersVO;
}
