<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpPawapay\Enums\HeaderType;

final class HeadersVO extends AbstractValueObject
{
    private array $headers = [];

    // Headers généraux
    public function setHost(string $host): self
    {
        $this->headers[HeaderType::HOST->value] = $host;

        return $this;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->headers[HeaderType::USER_AGENT->value] = $userAgent;

        return $this;
    }

    public function setAccept(string $accept): self
    {
        $this->headers[HeaderType::ACCEPT->value] = $accept;

        return $this;
    }

    public function setAcceptLanguage(string $acceptLanguage): self
    {
        $this->headers[HeaderType::ACCEPT_LANGUAGE->value] = $acceptLanguage;

        return $this;
    }

    public function setAcceptEncoding(string $acceptEncoding): self
    {
        $this->headers[HeaderType::ACCEPT_ENCODING->value] = $acceptEncoding;

        return $this;
    }

    public function setConnection(string $connection): self
    {
        $this->headers[HeaderType::CONNECTION->value] = $connection;

        return $this;
    }

    // Headers d'authentification
    public function setAuthorization(string $token): self
    {
        $this->headers[HeaderType::AUTHORIZATION->value] = 'Bearer '.$token;

        return $this;
    }

    public function setBasicAuth(string $username, string $password): self
    {
        $this->headers[HeaderType::AUTHORIZATION->value] = 'Basic '.base64_encode($username.':'.$password);

        return $this;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->headers[HeaderType::X_API_KEY->value] = $apiKey;

        return $this;
    }

    public function setCookie(string $cookie): self
    {
        $this->headers[HeaderType::COOKIE->value] = $cookie;

        return $this;
    }

    // Headers de contenu
    public function setContentType(string $contentType): self
    {
        $this->headers[HeaderType::CONTENT_TYPE->value] = $contentType;

        return $this;
    }

    public function setContentLength(int $contentLength): self
    {
        $this->headers[HeaderType::CONTENT_LENGTH->value] = (string) $contentLength;

        return $this;
    }

    public function setContentEncoding(string $contentEncoding): self
    {
        $this->headers[HeaderType::CONTENT_ENCODING->value] = $contentEncoding;

        return $this;
    }

    public function setContentLanguage(string $contentLanguage): self
    {
        $this->headers[HeaderType::CONTENT_LANGUAGE->value] = $contentLanguage;

        return $this;
    }

    // Headers de cache
    public function setCacheControl(string $cacheControl): self
    {
        $this->headers[HeaderType::CACHE_CONTROL->value] = $cacheControl;

        return $this;
    }

    public function setIfModifiedSince(string $ifModifiedSince): self
    {
        $this->headers[HeaderType::IF_MODIFIED_SINCE->value] = $ifModifiedSince;

        return $this;
    }

    public function setIfNoneMatch(string $ifNoneMatch): self
    {
        $this->headers[HeaderType::IF_NONE_MATCH->value] = $ifNoneMatch;

        return $this;
    }

    // Headers de requête
    public function setReferer(string $referer): self
    {
        $this->headers[HeaderType::REFERER->value] = $referer;

        return $this;
    }

    public function setOrigin(string $origin): self
    {
        $this->headers[HeaderType::ORIGIN->value] = $origin;

        return $this;
    }

    public function setXRequestedWith(string $xRequestedWith): self
    {
        $this->headers[HeaderType::X_REQUESTED_WITH->value] = $xRequestedWith;

        return $this;
    }

    public function setXForwardedFor(string $xForwardedFor): self
    {
        $this->headers[HeaderType::X_FORWARDED_FOR->value] = $xForwardedFor;

        return $this;
    }

    // Headers personnalisés
    public function setXRequestId(string $xRequestId): self
    {
        $this->headers[HeaderType::X_REQUEST_ID->value] = $xRequestId;

        return $this;
    }

    public function setXCorrelationId(string $xCorrelationId): self
    {
        $this->headers[HeaderType::X_CORRELATION_ID->value] = $xCorrelationId;

        return $this;
    }

    // Headers de sécurité
    public function setXsrfToken(string $xsrfToken): self
    {
        $this->headers[HeaderType::X_CSRF_TOKEN->value] = $xsrfToken;

        return $this;
    }

    public function setStrictTransportSecurity(string $sts): self
    {
        $this->headers[HeaderType::STRICT_TRANSPORT_SECURITY->value] = $sts;

        return $this;
    }

    // Méthode générique pour ajouter un header personnalisé
    public function setCustom(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function getValue(): StrictDataObject
    {
        return new StrictDataObject($this->headers);
    }

    public function has(HeaderType $header): bool
    {
        return isset($this->headers[$header->value]);
    }

    public function get(HeaderType $header): ?string
    {
        return $this->headers[$header->value] ?? null;
    }
}
