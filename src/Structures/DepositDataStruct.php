<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\DepositStatus;
use AndyDefer\PhpPawapay\Graphs\PayerGraph;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;

final class DepositDataStruct extends Struct
{
    public function __construct(
        public readonly string $depositId,
        public readonly DepositStatus $status,
        public readonly string $amount,
        public readonly ?Currency $currency = null,
        public readonly ?Country $country = null,
        public readonly ?PayerGraph $payer = null,
        public readonly ?string $clientReferenceId = null,
        public readonly ?string $customerMessage = null,
        public readonly ?string $created = null,
        public readonly ?string $providerTransactionId = null,
        public readonly ?MetadataVO $metadata = null,
        public readonly ?FailureReasonStruct $failureReason = null,
    ) {}
}
