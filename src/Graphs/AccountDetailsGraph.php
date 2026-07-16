<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

final class AccountDetailsGraph extends Graph
{
    public function __construct(
        public readonly PhoneNumberVO $phoneNumber,
        public readonly Provider $provider,
    ) {}
}
