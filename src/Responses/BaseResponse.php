<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Responses;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpPawapay\Contracts\ResponseInterface;
use AndyDefer\PhpPawapay\ValueObjects\HeadersVO;
use AndyDefer\PhpVo\Enums\HttpStatusCode;

abstract class BaseResponse implements ResponseInterface
{
    public function __construct(
        private readonly HttpStatusCode $statusCode,
        private readonly StrictDataObject $body,
        private readonly HeadersVO $headers
    ) {}

    final public function getStatusCode(): HttpStatusCode
    {
        return $this->statusCode;
    }

    final public function getBody(): StrictDataObject
    {
        return $this->body;
    }

    final public function getHeaders(): HeadersVO
    {
        return $this->headers;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode->isSuccess();
    }

    public function isError(): bool
    {
        return $this->statusCode->isError();
    }

    public function toArray(): array
    {
        return $this->body->toArray();
    }

    public function get(string $key): mixed
    {
        return $this->body->get($key);
    }

    public function has(string $key): bool
    {
        return $this->body->has($key);
    }
}
