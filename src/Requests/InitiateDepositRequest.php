<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Requests;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;
use AndyDefer\PhpPawapay\ValueObjects\MessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\PreAuthorisationCodeVO;
use AndyDefer\PhpPawapay\ValueObjects\ReferenceVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

final class InitiateDepositRequest extends BaseRequest
{
    private UuidVO $depositId;

    private PayerVO $payer;

    private AmountVO $amount;

    private Currency $currency;

    private ?PreAuthorisationCodeVO $preAuthorisationCode = null;

    private ?ReferenceVO $clientReferenceId = null;

    private ?MessageVO $customerMessage = null;

    private ?MetadataVO $metadata = null;

    public function setDepositId(UuidVO $depositId): self
    {
        $this->depositId = $depositId;

        return $this;
    }

    public function setPayer(PayerVO $payer): self
    {
        $this->payer = $payer;

        return $this;
    }

    public function setAmount(AmountVO $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function setPreAuthorisationCode(?PreAuthorisationCodeVO $preAuthorisationCode): self
    {
        $this->preAuthorisationCode = $preAuthorisationCode;

        return $this;
    }

    public function setClientReferenceId(?ReferenceVO $clientReferenceId): self
    {
        $this->clientReferenceId = $clientReferenceId;

        return $this;
    }

    public function setCustomerMessage(?MessageVO $customerMessage): self
    {
        $this->customerMessage = $customerMessage;

        return $this;
    }

    public function setMetadata(?MetadataVO $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    protected function setBody(): AbstractValueObject
    {
        return new InitiateDepositVO(
            $this->depositId,
            $this->payer,
            $this->amount,
            $this->currency,
            $this->preAuthorisationCode,
            $this->clientReferenceId,
            $this->customerMessage,
            $this->metadata
        );
    }
}
