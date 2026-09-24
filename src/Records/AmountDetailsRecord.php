<?php

namespace AndyDefer\PhpPawapay\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;

class AmountDetailsRecord extends AbstractRecord
{
    public function __construct(
        public readonly AmountVO $amount,
        public readonly Currency $currency,
    ) {}
}
