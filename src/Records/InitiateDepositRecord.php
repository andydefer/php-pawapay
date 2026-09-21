<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\ClientReferenceIdVO;
use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

final class InitiateDepositRecord extends AbstractRecord
{
    public function __construct(
        public readonly UuidVO $depositId,
        public readonly PayerVO $payer,
        public readonly AmountVO $amount,
        public readonly Currency $currency,
        public readonly ?string $preAuthorisationCode = null,
        public readonly ?ClientReferenceIdVO $clientReferenceId = null,
        public readonly ?CustomerMessageVO $customerMessage = null,
        public readonly ?MetadataVO $metadata = null,
    ) {}
}
