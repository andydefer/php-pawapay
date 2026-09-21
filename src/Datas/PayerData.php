<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\PhpPawapay\Enums\PayerType;

final class PayerData extends AbstractData
{
    public function __construct(
        public readonly PayerType $type,
        public readonly AccountDetailsData $accountDetails,
    ) {}
}
