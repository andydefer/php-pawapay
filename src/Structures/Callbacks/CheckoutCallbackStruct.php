<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures\Callbacks;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpPawapay\Collections\DepositDataStructCollection;
use AndyDefer\PhpPawapay\Enums\CheckoutStatus;
use AndyDefer\PhpPawapay\Enums\DepositStatus;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

final class CheckoutCallbackStruct extends Struct
{
    public function __construct(
        public readonly UuidVO $checkoutId,
        public readonly CheckoutStatus $status,
        public readonly DepositStatus $depositStatus,
        public readonly DepositCallbackStruct $deposit,
        public readonly DepositDataStructCollection $depositsHistory,
        public readonly ?MetadataVO $metadata = null,
    ) {}
}
