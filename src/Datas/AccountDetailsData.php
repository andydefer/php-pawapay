<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

final class AccountDetailsData extends AbstractData
{
    public function __construct(
        public readonly PhoneNumberVO $phoneNumber,
        public readonly Provider $provider,
    ) {}
}
