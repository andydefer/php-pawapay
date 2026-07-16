<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Graphs\PayerGraph;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\MessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PreAuthorisationCodeVO;
use AndyDefer\PhpPawapay\ValueObjects\ReferenceVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

final class InitiateDepositStruct extends Struct
{
    public function __construct(
        public readonly UuidVO $depositId,
        public readonly PayerGraph $payer,
        public readonly AmountVO $amount,
        public readonly Currency $currency,
        public readonly ?PreAuthorisationCodeVO $preAuthorisationCode = null,
        public readonly ?ReferenceVO $clientReferenceId = null,
        public readonly ?MessageVO $customerMessage = null,
        public readonly ?MetadataVO $metadata = null,
    ) {}
}
