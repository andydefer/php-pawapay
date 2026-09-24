<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Fixtures\Service;

use AndyDefer\PhpPawapay\Contracts\PawapayClientInterface;
use AndyDefer\PhpPawapay\Enums\CallbackOperationType;
use AndyDefer\PhpPawapay\Services\PawapayService;
use AndyDefer\PhpPawapay\Structures\Callbacks\CheckoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\DepositCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\PayoutCallbackStruct;
use AndyDefer\PhpPawapay\Structures\Callbacks\RefundCallbackStruct;

final class RecordingPawapayService extends PawapayService
{
    /** @var list<string> */
    public array $calls = [];

    public ?CallbackOperationType $beforeOperation = null;

    public ?CallbackOperationType $afterOperation = null;

    public function __construct(PawapayClientInterface $client)
    {
        parent::__construct($client);
    }

    protected function beforeHandleCallback(
        DepositCallbackStruct|PayoutCallbackStruct|RefundCallbackStruct|CheckoutCallbackStruct $struct,
        CallbackOperationType $operation,
    ): void {
        $this->calls[] = 'before';
        $this->beforeOperation = $operation;
    }

    protected function afterHandleCallback(
        DepositCallbackStruct|PayoutCallbackStruct|RefundCallbackStruct|CheckoutCallbackStruct $struct,
        CallbackOperationType $operation,
    ): void {
        $this->calls[] = 'after';
        $this->afterOperation = $operation;
    }
}
