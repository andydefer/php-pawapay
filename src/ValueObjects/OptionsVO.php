<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpPawapay\Enums\OptionType;

final class OptionsVO extends AbstractValueObject
{
    private array $options = [];

    // Options générales
    public function setTimeout(int $seconds): self
    {
        $this->options[OptionType::TIMEOUT->value] = $seconds;

        return $this;
    }

    public function setConnectTimeout(int $seconds): self
    {
        $this->options[OptionType::CONNECT_TIMEOUT->value] = $seconds;

        return $this;
    }

    public function setVerify(bool|string $verify): self
    {
        $this->options[OptionType::VERIFY->value] = $verify;

        return $this;
    }

    public function setDebug(bool $debug): self
    {
        $this->options[OptionType::DEBUG->value] = $debug;

        return $this;
    }

    public function setHttpErrors(bool $httpErrors): self
    {
        $this->options[OptionType::HTTP_ERRORS->value] = $httpErrors;

        return $this;
    }

    public function setAllowRedirects(bool|array $allowRedirects): self
    {
        $this->options[OptionType::ALLOW_REDIRECTS->value] = $allowRedirects;

        return $this;
    }

    public function setMaxRedirects(int $maxRedirects): self
    {
        $this->options[OptionType::MAX_REDIRECTS->value] = $maxRedirects;

        return $this;
    }

    public function setCookies(bool|array $cookies): self
    {
        $this->options[OptionType::COOKIES->value] = $cookies;

        return $this;
    }

    public function setIdnConversion(bool $idnConversion): self
    {
        $this->options[OptionType::IDN_CONVERSION->value] = $idnConversion;

        return $this;
    }

    // Options de transfert
    public function setBody(string $body): self
    {
        $this->options[OptionType::BODY->value] = $body;

        return $this;
    }

    public function setJson(array $json): self
    {
        $this->options[OptionType::JSON->value] = $json;

        return $this;
    }

    public function setMultipart(array $multipart): self
    {
        $this->options[OptionType::MULTIPART->value] = $multipart;

        return $this;
    }

    public function setFormParams(array $formParams): self
    {
        $this->options[OptionType::FORM_PARAMS->value] = $formParams;

        return $this;
    }

    public function setStream(bool $stream): self
    {
        $this->options[OptionType::STREAM->value] = $stream;

        return $this;
    }

    public function setSink(string $sink): self
    {
        $this->options[OptionType::SINK->value] = $sink;

        return $this;
    }

    public function setReadTimeout(int $seconds): self
    {
        $this->options[OptionType::READ_TIMEOUT->value] = $seconds;

        return $this;
    }

    // Options de proxy
    public function setProxy(string|array $proxy): self
    {
        $this->options[OptionType::PROXY->value] = $proxy;

        return $this;
    }

    public function setNoProxy(array $noProxy): self
    {
        $this->options[OptionType::NO_PROXY->value] = $noProxy;

        return $this;
    }

    // Options d'authentification
    public function setAuth(array $auth): self
    {
        $this->options[OptionType::AUTH->value] = $auth;

        return $this;
    }

    public function setCert(string|array $cert): self
    {
        $this->options[OptionType::CERT->value] = $cert;

        return $this;
    }

    public function setSslKey(string|array $sslKey): self
    {
        $this->options[OptionType::SSL_KEY->value] = $sslKey;

        return $this;
    }

    // Options de version HTTP
    public function setVersion(string $version): self
    {
        $this->options[OptionType::VERSION->value] = $version;

        return $this;
    }

    // Options d'environnement
    public function setBaseUri(string $baseUri): self
    {
        $this->options[OptionType::BASE_URI->value] = $baseUri;

        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->options[OptionType::HEADERS->value] = $headers;

        return $this;
    }

    public function setQuery(array $query): self
    {
        $this->options[OptionType::QUERY->value] = $query;

        return $this;
    }

    public function setDecodeContent(bool $decodeContent): self
    {
        $this->options[OptionType::DECODE_CONTENT->value] = $decodeContent;

        return $this;
    }

    public function setForceIpResolve(string $forceIpResolve): self
    {
        $this->options[OptionType::FORCE_IP_RESOLVE->value] = $forceIpResolve;

        return $this;
    }

    // Options de logging
    public function setOnStats(callable $onStats): self
    {
        $this->options[OptionType::ON_STATS->value] = $onStats;

        return $this;
    }

    // Méthodes génériques
    public function setCustom(string $key, mixed $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }

    public function has(OptionType $option): bool
    {
        return isset($this->options[$option->value]);
    }

    public function get(OptionType $option): mixed
    {
        return $this->options[$option->value] ?? null;
    }

    public function getValue(): StrictDataObject
    {
        return new StrictDataObject($this->options);
    }

    public function toArray(): array
    {
        return $this->options;
    }
}
