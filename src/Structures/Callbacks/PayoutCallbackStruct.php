<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures\Callbacks;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\PayoutStatus;
use AndyDefer\PhpPawapay\Graphs\RecipientGraph;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\ClientReferenceIdVO;
use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

final class PayoutCallbackStruct extends Struct
{
    public function __construct(
        public readonly UuidVO $payoutId,
        public readonly PayoutStatus $status,
        public readonly AmountVO $amount,
        public readonly Currency $currency,
        public readonly RecipientGraph $recipient,
        public readonly ClientReferenceIdVO $clientReferenceId,
        public readonly CustomerMessageVO $customerMessage,
        public readonly string $providerTransactionId,
        public readonly DateTimeZuluVO $created,
    ) {}
}
