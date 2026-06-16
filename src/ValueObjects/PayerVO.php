<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Utils\DataObject;
use AndyDefer\PhpPawapay\Enums\MMO;

final class PayerVO extends AbstractValueObject
{
    public function __construct(
        public readonly MMO $type,
        public readonly AccountDetailsVO $accountDetails
    ) {}

    public function getValue(): DataObject
    {
        return new DataObject([
            'type' => $this->type,
            'accountDetails' => $this->accountDetails,
        ]);
    }
}
