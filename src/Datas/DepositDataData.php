<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Utils\StrictAssociative;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\DepositStatus;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\ClientReferenceIdVO;
use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

final class DepositDataData extends AbstractData
{
    public function __construct(
        public readonly UuidVO $depositId,
        public readonly DepositStatus $status,
        public readonly AmountVO $amount,
        public readonly ?Currency $currency = null,
        public readonly ?Country $country = null,
        public readonly ?PayerData $payer = null,
        public readonly ?ClientReferenceIdVO $clientReferenceId = null,
        public readonly ?CustomerMessageVO $customerMessage = null,
        public readonly ?DateTimeZuluVO $created = null,
        public readonly ?string $providerTransactionId = null,
        public readonly ?StrictAssociative $metadata = null,
        public readonly ?FailureReasonData $failureReason = null,
    ) {}
}
