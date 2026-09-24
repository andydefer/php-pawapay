<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts\Callbacks;

use AndyDefer\PhpPawapay\Structures\Callbacks\CheckoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\PayoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\RefundCallbackStruct;

interface HandlesCallbacks
{
    public function handleDeposit(DepositCallbackStruct $struct): void;

    public function handlePayout(PayoutCallbackStruct $struct): void;

    public function handleRefund(RefundCallbackStruct $struct): void;

    public function handleCheckout(CheckoutCallbackStruct $struct): void;
}
