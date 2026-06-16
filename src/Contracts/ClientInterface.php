<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts;

interface ClientInterface
{
    public function get(string $uri, RequestInterface $request): ResponseInterface;

    public function post(string $uri, RequestInterface $request): ResponseInterface;

    public function put(string $uri, RequestInterface $request): ResponseInterface;

    public function patch(string $uri, RequestInterface $request): ResponseInterface;

    public function delete(string $uri, RequestInterface $request): ResponseInterface;
}
