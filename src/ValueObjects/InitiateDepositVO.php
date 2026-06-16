<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Utils\DataObject;
use AndyDefer\PhpPawapay\Enums\Currency;

final class InitiateDepositVO extends AbstractValueObject
{
    public function __construct(
        public readonly UuidVO $depositId,
        public readonly PayerVO $payer,
        public readonly AmountVO $amount,
        public readonly Currency $currency,
        public readonly ?PreAuthorisationCodeVO $preAuthorisationCode = null,
        public readonly ?ReferenceVO $clientReferenceId = null,
        public readonly ?MessageVO $customerMessage = null,
        public readonly ?MetadataVO $metadata = null
    ) {}

    public function getValue(): DataObject
    {
        return new DataObject([
            'depositId' => $this->depositId,
            'payer' => $this->payer,
            'amount' => $this->amount,
            'currency' => $this->currency->value,
            'preAuthorisationCode' => $this->preAuthorisationCode,
            'clientReferenceId' => $this->clientReferenceId,
            'customerMessage' => $this->customerMessage,
            'metadata' => $this->metadata,
        ]);
    }
}
