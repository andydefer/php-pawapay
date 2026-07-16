<?php

namespace AndyDefer\PhpPawapay\Enums;

enum PawaPayBaseUrl: string
{
    case SANDBOX = 'https://api.sandbox.pawapay.io/';
    case PRODUCTION = 'https://api.pawapay.io/';

}
