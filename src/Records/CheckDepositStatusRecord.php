<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

final class CheckDepositStatusRecord extends AbstractRecord
{
    public function __construct(
        public readonly UuidVO $depositId,
    ) {}
}
