<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Graphs\PayerGraph;

final class InitiateDepositStruct extends Struct
{
    public function __construct(
        public readonly string $depositId,
        public readonly PayerGraph $payer,
        public readonly string $amount,
        public readonly Currency $currency,
        public readonly ?string $preAuthorisationCode = null,
        public readonly ?string $clientReferenceId = null,
        public readonly ?string $customerMessage = null,
        public readonly ?array $metadata = null,
    ) {}
}
