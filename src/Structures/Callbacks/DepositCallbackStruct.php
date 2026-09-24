<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures\Callbacks;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\DepositStatus;
use AndyDefer\PhpPawapay\Graphs\PayerGraph;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\ClientReferenceIdVO;
use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

final class DepositCallbackStruct extends Struct
{
    public function __construct(
        public readonly UuidVO $depositId,
        public readonly DepositStatus $status,
        public readonly AmountVO $amount,
        public readonly Currency $currency,
        public readonly Country $country,
        public readonly PayerGraph $payer,
        public readonly CustomerMessageVO $customerMessage,
        public readonly ClientReferenceIdVO $clientReferenceId,
        public readonly DateTimeZuluVO $created,
        public readonly string $providerTransactionId,
        public readonly ?MetadataVO $metadata = null,
        public readonly ?FailureReasonStruct $failureReason = null,
    ) {}
}
