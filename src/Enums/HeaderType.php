<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Enums;

enum HeaderType: string
{
    // Headers généraux
    case HOST = 'Host';
    case USER_AGENT = 'User-Agent';
    case ACCEPT = 'Accept';
    case ACCEPT_LANGUAGE = 'Accept-Language';
    case ACCEPT_ENCODING = 'Accept-Encoding';
    case CONNECTION = 'Connection';

    // Headers d'authentification
    case AUTHORIZATION = 'Authorization';
    case X_API_KEY = 'X-API-Key';
    case COOKIE = 'Cookie';

    // Headers de contenu
    case CONTENT_TYPE = 'Content-Type';
    case CONTENT_LENGTH = 'Content-Length';
    case CONTENT_ENCODING = 'Content-Encoding';
    case CONTENT_LANGUAGE = 'Content-Language';

    // Headers de cache
    case CACHE_CONTROL = 'Cache-Control';
    case IF_MODIFIED_SINCE = 'If-Modified-Since';
    case IF_NONE_MATCH = 'If-None-Match';

    // Headers de requête
    case REFERER = 'Referer';
    case ORIGIN = 'Origin';
    case X_REQUESTED_WITH = 'X-Requested-With';
    case X_FORWARDED_FOR = 'X-Forwarded-For';

    // Headers personnalisés
    case X_REQUEST_ID = 'X-Request-Id';
    case X_CORRELATION_ID = 'X-Correlation-Id';

    // Headers de sécurité
    case X_CSRF_TOKEN = 'X-CSRF-Token';
    case STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';

    public function isAuthentication(): bool
    {
        return in_array($this, [
            self::AUTHORIZATION,
            self::X_API_KEY,
            self::COOKIE,
        ]);
    }

    public function isContent(): bool
    {
        return in_array($this, [
            self::CONTENT_TYPE,
            self::CONTENT_LENGTH,
            self::CONTENT_ENCODING,
            self::CONTENT_LANGUAGE,
        ]);
    }

    public function isCache(): bool
    {
        return in_array($this, [
            self::CACHE_CONTROL,
            self::IF_MODIFIED_SINCE,
            self::IF_NONE_MATCH,
        ]);
    }

    public function isCustom(): bool
    {
        return str_starts_with($this->value, 'X-');
    }
}
