<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Utils\StrictAssociative;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

final class PredictProviderRecord extends AbstractRecord
{
    public function __construct(
        public readonly PhoneNumberVO $phoneNumber,
        public readonly ?StrictAssociative $data = null,
    ) {}
}
