<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Enums;

enum OptionType: string
{
    // Options générales
    case TIMEOUT = 'timeout';
    case CONNECT_TIMEOUT = 'connect_timeout';
    case VERIFY = 'verify';
    case DEBUG = 'debug';
    case HTTP_ERRORS = 'http_errors';
    case ALLOW_REDIRECTS = 'allow_redirects';
    case MAX_REDIRECTS = 'max_redirects';
    case COOKIES = 'cookies';
    case IDN_CONVERSION = 'idn_conversion';

    // Options de transfert
    case BODY = 'body';
    case JSON = 'json';
    case MULTIPART = 'multipart';
    case FORM_PARAMS = 'form_params';
    case STREAM = 'stream';
    case SINK = 'sink';
    case READ_TIMEOUT = 'read_timeout';

    // Options de proxy
    case PROXY = 'proxy';
    case NO_PROXY = 'no_proxy';

    // Options d'authentification
    case AUTH = 'auth';
    case CERT = 'cert';
    case SSL_KEY = 'ssl_key';

    // Options de version HTTP
    case VERSION = 'version';

    // Options d'environnement
    case BASE_URI = 'base_uri';
    case HEADERS = 'headers';
    case QUERY = 'query';
    case DECODE_CONTENT = 'decode_content';
    case FORCE_IP_RESOLVE = 'force_ip_resolve';

    // Options de logging
    case ON_STATS = 'on_stats';

    public function isTransfer(): bool
    {
        return in_array($this, [
            self::BODY,
            self::JSON,
            self::MULTIPART,
            self::FORM_PARAMS,
            self::STREAM,
            self::SINK,
            self::READ_TIMEOUT,
        ]);
    }

    public function isProxy(): bool
    {
        return in_array($this, [
            self::PROXY,
            self::NO_PROXY,
        ]);
    }

    public function isAuth(): bool
    {
        return in_array($this, [
            self::AUTH,
            self::CERT,
            self::SSL_KEY,
        ]);
    }
}
