<?php

namespace AndyDefer\PhpPawapay\Enums;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case HEAD = 'HEAD';
    case OPTIONS = 'OPTIONS';

    public function isSafe(): bool
    {
        return in_array($this, [self::GET, self::HEAD, self::OPTIONS]);
    }

    public function isIdempotent(): bool
    {
        return in_array($this, [self::GET, self::HEAD, self::OPTIONS, self::PUT, self::DELETE]);
    }

    public function hasBody(): bool
    {
        return in_array($this, [self::POST, self::PUT, self::PATCH]);
    }
}
